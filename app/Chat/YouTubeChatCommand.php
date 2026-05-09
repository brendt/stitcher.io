<?php

namespace App\Chat;

use RuntimeException;
use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\HttpClient\HttpClient;
use Throwable;
use function Tempest\env;

final class YouTubeChatCommand
{
    use HasConsole;

    private const string API_BASE = 'https://www.googleapis.com/youtube/v3';
    private const int MAIN_LOOP_SLEEP_SECONDS = 60;
    private const int CHAT_STATUS_CHECK_SECONDS = 60;
    private const int FALLBACK_CHAT_SLEEP_SECONDS = 5;
    private const int MAX_CHAT_POLL_FAILURES = 3;
    private const int MAX_BACKFILL_ROUNDS = 3;
    private const int LIVE_BROADCAST_MAX_RESULTS = 5;
    private const string USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1';

    private ?string $accessToken = null;
    private ?string $currentVideoId = null;
    private ?string $liveChatId = null;
    private ?string $nextPageToken = null;
    private array $seenMessageIds = [];

    public function __construct(
        private readonly ChatStorage $chatStorage,
        private readonly HttpClient $http,
    ) {}

    #[ConsoleCommand('chat:youtube')]
    public function __invoke(): void
    {
        $this->accessToken = env('YOUTUBE_ACCESS_TOKEN');
        $channelId = env('YOUTUBE_CHANNEL_ID');

        $this->logInfo("YouTube chat watcher started for channel {$channelId}");
        $this->logInfo('Using API detector for livestream status (swappable via detectLivestreamStatus).');

        while (true) {
            $status = $this->detectLivestreamStatus();

            if ($status['state'] !== 'live') {
                $label = $status['state'] === 'unknown'
                    ? 'livestream status unknown (API issue)'
                    : 'no active livestream';
                $this->logInfo("Main loop: {$label}, sleeping 60 seconds.");
                sleep(self::MAIN_LOOP_SLEEP_SECONDS);
                continue;
            }

            $stream = $status['stream'];
            $this->startChatSession($stream['videoId'], $stream['liveChatId']);
            $this->chatMessageLoop();
            $this->resetChatSession();

            $this->logInfo('Main loop: chat loop ended, sleeping 60 seconds before checking livestream status again.');
            sleep(self::MAIN_LOOP_SLEEP_SECONDS);
        }
    }

    private function startChatSession(string $videoId, string $liveChatId): void
    {
        $this->currentVideoId = $videoId;
        $this->liveChatId = $liveChatId;
        $this->nextPageToken = null;
        $this->seenMessageIds = [];

        $this->logInfo("Connected to livestream {$videoId} with chat {$liveChatId}");
        $this->chatStorage->appendMessage(new Message(
            user: 'PHPAnnotated',
            content: 'Connected',
            platform: 'youtube',
            timestamp: new DateTimeImmutable(),
            color: $this->chatStorage->getUserColor('PHPAnnotated'),
        ));
    }

    private function resetChatSession(): void
    {
        $this->logInfo('Resetting chat session state.');
        $this->currentVideoId = null;
        $this->liveChatId = null;
        $this->nextPageToken = null;
        $this->seenMessageIds = [];
    }

    private function chatMessageLoop(): void
    {
        $this->logInfo('Chat loop started; attempting to backfill missed messages.');
        $this->backfillMessages();

        $pollFailureCount = 0;
        $nextStatusCheckAt = time();

        while (true) {
            if (time() >= $nextStatusCheckAt) {
                $nextStatusCheckAt = time() + self::CHAT_STATUS_CHECK_SECONDS;
                $status = $this->detectLivestreamStatus();

                if ($status['state'] !== 'live') {
                    $this->logInfo('Chat loop: livestream is no longer live, returning to main loop.');
                    return;
                }

                $liveVideoId = $status['stream']['videoId'] ?? null;
                if ($liveVideoId !== $this->currentVideoId) {
                    $this->logInfo("Chat loop: detected different live stream {$liveVideoId}, returning to main loop.");
                    return;
                }
            }

            $pollResult = $this->pollChatMessages();

            if (! $pollResult['ok']) {
                $pollFailureCount++;
                $this->logWarning("Chat loop: poll failed ({$pollFailureCount}/" . self::MAX_CHAT_POLL_FAILURES . ').');

                if ($pollFailureCount >= self::MAX_CHAT_POLL_FAILURES) {
                    $this->logError('Chat loop: too many poll failures, returning to main loop.');
                    return;
                }

                sleep(self::FALLBACK_CHAT_SLEEP_SECONDS);
                continue;
            }

            $pollFailureCount = 0;
            $storedCount = $pollResult['storedCount'];
            $sleepSeconds = $pollResult['sleepSeconds'];
            $this->logInfo("Chat loop: stored {$storedCount} message(s), sleeping {$sleepSeconds}s.");
            sleep($sleepSeconds);
        }
    }

    private function backfillMessages(): void
    {
        $total = 0;

        for ($i = 1; $i <= self::MAX_BACKFILL_ROUNDS; $i++) {
            $result = $this->pollChatMessages();

            if (! $result['ok']) {
                $this->logWarning("Backfill stopped at round {$i} due to poll failure.");
                break;
            }

            $total += $result['storedCount'];

            if ($result['storedCount'] === 0) {
                break;
            }
        }

        $this->logInfo("Backfill complete: stored {$total} message(s).");
    }

    private function detectLivestreamStatus(): array
    {
        return $this->detectLivestreamStatusViaApi();
    }

    private function detectLivestreamStatusViaApi(): array
    {
        $url = self::API_BASE . '/liveBroadcasts?' . http_build_query([
            'part' => 'id,snippet,status',
            'mine' => 'true',
            'broadcastType' => 'all',
            'maxResults' => self::LIVE_BROADCAST_MAX_RESULTS,
        ]);

        $response = $this->requestAuthorizedJson($url);

        if ($response === null) {
            return ['state' => 'unknown'];
        }

        foreach ($response['items'] ?? [] as $item) {
            $videoId = $item['id'] ?? null;
            $lifeCycleStatus = $item['status']['lifeCycleStatus'] ?? null;

            if ($lifeCycleStatus !== 'live' || ! is_string($videoId) || $videoId === '') {
                continue;
            }

            $liveChatId = $item['snippet']['liveChatId'] ?? $this->fetchLiveChatId($videoId);

            if (! is_string($liveChatId) || $liveChatId === '') {
                continue;
            }

            return [
                'state' => 'live',
                'stream' => [
                    'videoId' => $videoId,
                    'liveChatId' => $liveChatId,
                ],
            ];
        }

        return ['state' => 'offline'];
    }

    private function fetchLiveChatId(string $videoId): ?string
    {
        $url = self::API_BASE . '/videos?' . http_build_query([
            'part' => 'liveStreamingDetails',
            'id' => $videoId,
        ]);

        $response = $this->requestAuthorizedJson($url);

        if ($response === null) {
            return null;
        }

        $liveChatId = $response['items'][0]['liveStreamingDetails']['activeLiveChatId'] ?? null;

        return is_string($liveChatId) && $liveChatId !== '' ? $liveChatId : null;
    }

    /**
     * @return array{ok:bool,storedCount:int,sleepSeconds:int}
     */
    private function pollChatMessages(): array
    {
        if ($this->liveChatId === null) {
            return ['ok' => false, 'storedCount' => 0, 'sleepSeconds' => self::FALLBACK_CHAT_SLEEP_SECONDS];
        }

        $params = [
            'part' => 'snippet,authorDetails',
            'liveChatId' => $this->liveChatId,
            'maxResults' => 200,
        ];

        if ($this->nextPageToken !== null) {
            $params['pageToken'] = $this->nextPageToken;
        }

        $url = self::API_BASE . '/liveChat/messages?' . http_build_query($params);
        $response = $this->requestAuthorizedJson($url);

        if ($response === null) {
            return ['ok' => false, 'storedCount' => 0, 'sleepSeconds' => self::FALLBACK_CHAT_SLEEP_SECONDS];
        }

        $this->nextPageToken = $response['nextPageToken'] ?? $this->nextPageToken;

        $storedCount = 0;
        foreach ($response['items'] ?? [] as $item) {
            $messageId = $item['id'] ?? null;

            if (! is_string($messageId) || isset($this->seenMessageIds[$messageId])) {
                continue;
            }

            $this->seenMessageIds[$messageId] = true;

            $snippet = $item['snippet'] ?? [];
            $author = $item['authorDetails'] ?? [];
            $user = $author['displayName'] ?? 'Unknown';
            $content = $snippet['displayMessage'] ?? '';
            $publishedAt = new DateTimeImmutable($snippet['publishedAt'] ?? 'now');

            $message = new Message(
                user: $user,
                content: $content,
                platform: 'youtube',
                timestamp: $publishedAt,
                color: $this->chatStorage->getUserColor($user),
            );

            $this->chatStorage->appendMessage($message);
            $this->info(sprintf('[%s] [%s] %s', $this->timestamp(), $message->user, $message->content));
            $storedCount++;
        }

        $intervalMillis = (int) ($response['pollingIntervalMillis'] ?? (self::FALLBACK_CHAT_SLEEP_SECONDS * 1000));
        $sleepSeconds = max(self::FALLBACK_CHAT_SLEEP_SECONDS, (int) ceil($intervalMillis / 1000));

        return ['ok' => true, 'storedCount' => $storedCount, 'sleepSeconds' => $sleepSeconds];
    }

    private function requestAuthorizedJson(string $url, bool $allowRefresh = true): ?array
    {
        $response = $this->requestJson(
            method: 'GET',
            url: $url,
            headers: [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
                'User-Agent' => self::USER_AGENT,
            ],
        );

        if ($response === null) {
            return null;
        }

        if ($response['status'] === 401 && $allowRefresh) {
            $this->logWarning('Access token expired, refreshing token.');

            if ($this->refreshAccessToken()) {
                return $this->requestAuthorizedJson($url, false);
            }

            $this->logError('Token refresh failed.');
            return null;
        }

        if ($response['status'] >= 400) {
            $this->logError('API HTTP error ' . $response['status'] . ': ' . json_encode($response['data'] ?? []));
            return null;
        }

        if (! is_array($response['data'])) {
            $this->logError('API response is not valid JSON.');
            return null;
        }

        if (isset($response['data']['error'])) {
            $this->logError('API error: ' . json_encode($response['data']['error']));
            return null;
        }

        return $response['data'];
    }

    /**
     * @return array{status:int,data:mixed}|null
     */
    private function requestJson(string $method, string $url, array $headers = []): ?array
    {
        try {
            $response = match ($method) {
                'GET' => $this->http->get($url, $headers),
                'POST' => $this->http->post($url, $headers),
                default => throw new RuntimeException("Unsupported method {$method}"),
            };
        } catch (Throwable $throwable) {
            $this->logError("HTTP request failed: {$throwable->getMessage()}");
            return null;
        }

        $rawBody = $response->body;
        $data = is_string($rawBody) ? json_decode($rawBody, true) : $rawBody;

        return [
            'status' => $response->status->value,
            'data' => $data,
        ];
    }

    private function refreshAccessToken(): bool
    {
        $url = 'https://oauth2.googleapis.com/token?' . http_build_query([
            'client_id' => env('YOUTUBE_CLIENT_ID'),
            'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
            'refresh_token' => env('YOUTUBE_REFRESH_TOKEN'),
            'grant_type' => 'refresh_token',
        ]);

        $response = $this->requestJson('POST', $url, [
            'Accept' => 'application/json',
            'User-Agent' => self::USER_AGENT,
        ]);

        if ($response === null || $response['status'] >= 400 || ! is_array($response['data'])) {
            return false;
        }

        $newAccessToken = $response['data']['access_token'] ?? null;

        if (! is_string($newAccessToken) || $newAccessToken === '') {
            return false;
        }

        $this->accessToken = $newAccessToken;
        $this->logInfo('Access token refreshed.');

        return true;
    }

    private function logInfo(string $message): void
    {
        $this->info(sprintf('[%s] %s', $this->timestamp(), $message));
    }

    private function logWarning(string $message): void
    {
        $this->warning(sprintf('[%s] %s', $this->timestamp(), $message));
    }

    private function logError(string $message): void
    {
        $this->error(sprintf('[%s] %s', $this->timestamp(), $message));
    }

    private function timestamp(): string
    {
        return new DateTimeImmutable()->format('Y-m-d H:i:s');
    }
}
