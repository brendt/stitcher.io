<?php

namespace App\Chat;

use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\DateTime\DateTime;
use Tempest\HttpClient\HttpClient;
use function Tempest\env;
use function Tempest\Support\str;

final class YouTubeChatCommand
{
    use HasConsole;

    private const string API_BASE = 'https://www.googleapis.com/youtube/v3';
    private const string FEED_BASE = 'https://www.youtube.com/feeds/videos.xml';
    private const int LATEST_RECHECK_SECONDS = 180;

    private ?string $accessToken = null;
    private ?string $liveChatId = null;
    private ?string $nextPageToken = null;
    private ?string $currentVideoId = null;
    private ?DateTimeImmutable $connectedAt = null;
    private bool $isFirstPollAfterConnect = false;
    private array $seenMessageIds = [];
    private array $checkedVideoIds = [];

    public function __construct(
        private ChatStorage $chatStorage,
        private HttpClient $http,
    ) {}

    #[ConsoleCommand('chat:youtube')]
    public function __invoke(): void
    {
        $this->accessToken = env('YOUTUBE_ACCESS_TOKEN');
        $channelId = env('YOUTUBE_CHANNEL_ID');

        $this->info("Watching channel {$channelId} feed for livestream starts...");

        while (true) {
            ['videoId' => $videoId, 'liveChatId' => $liveChatId] = $this->waitForLiveStreamFromFeed($channelId);
            $this->currentVideoId = $videoId;
            $this->liveChatId = $liveChatId;
            $this->connectedAt = new DateTimeImmutable();
            $this->isFirstPollAfterConnect = true;

            $this->success("Connected to live chat: {$this->liveChatId} {$this->currentVideoId}");
            $this->chatStorage->appendMessage(new Message(
                user: 'PHPAnnotated',
                content: "Connected",
                platform: 'youtube',
                timestamp: new DateTimeImmutable(),
                color: $this->chatStorage->getUserColor('PHPAnnotated'),
            ));

            $this->pollUntilStreamEnds();

            $this->info('Stream ended. Waiting for new stream...');
            if ($this->currentVideoId !== null) {
                $this->info("Marked livestream {$this->currentVideoId} as completed; waiting for feed updates...");
            }
            $this->currentVideoId = null;
            $this->liveChatId = null;
            $this->connectedAt = null;
            $this->isFirstPollAfterConnect = false;
            $this->nextPageToken = null;
            $this->seenMessageIds = [];
        }
    }

    private function waitForLiveStreamFromFeed(string $channelId): array
    {
        while (true) {
            $entries = $this->fetchFeedEntries($channelId);

            if ($entries === null) {
                $this->error('Failed to fetch YouTube feed. Retrying in 60s...');
                sleep(60);
                continue;
            }

            if ($entries === []) {
                $this->info('Feed has no entries yet. Retrying in 60s...');
                sleep(60);
                continue;
            }

            $candidate = $this->findLatestCandidateEntry($entries);

            if ($candidate === null) {
                $latestVideoId = $entries[0]['videoId'] ?? 'n/a';
                $this->info("Latest feed video {$latestVideoId} already checked recently, waiting 1 minute for updates...");
                sleep(60);
                continue;
            }

            $videoId = $candidate['videoId'];
            $liveChatId = $this->fetchLiveChatId($videoId);
            $this->checkedVideoIds[$videoId] = true;

            if ($liveChatId === null) {
                $this->info("Latest feed video {$videoId} is not currently live. Waiting 1 minute for newer feed updates...");
                sleep(60);
                continue;
            }

            return [
                'videoId' => $videoId,
                'liveChatId' => $liveChatId,
            ];
        }
    }

    private function findLatestCandidateEntry(array $entries): ?array
    {
        $latest = array_first($entries);

        if ($latest === null) {
            return null;
        }

        $videoId = $latest['videoId'];
        $isChecked = isset($this->checkedVideoIds[$videoId]);

        if (! $isChecked) {
            return $latest;
        }

        return null;
    }

    private function fetchFeedEntries(string $channelId): ?array
    {
        $url = self::FEED_BASE . '?' . http_build_query([
            'channel_id' => $channelId,
        ]);

        $input = $this->http->get($url, [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'
        ])->body;

        $xml = simplexml_load_string($input, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $data = json_decode($json, true);

        foreach ($data['entry'] as $entry) {
            $entries[] = [
                'videoId' => str($entry['id'])->afterLast(':')->toString(),
                'published' => DateTime::parse($entry['published']),
                'title' => $entry['title'],
            ];
        }

        return $entries;
    }

    private function pollUntilStreamEnds(): void
    {
        $failureCount = 0;

        while ($failureCount < 3) {
            if ($this->pollMessages()) {
                $failureCount = 0;
            } else {
                $failureCount++;
                $this->info("Poll failed ({$failureCount}/3)...");
            }

            sleep(5);
        }
    }

    private function fetchLiveChatId(string $videoId): ?string
    {
        $url = self::API_BASE . '/videos?' . http_build_query([
            'part' => 'liveStreamingDetails',
            'id' => $videoId,
        ]);

        $response = $this->requestJson($url);

        return $response['items'][0]['liveStreamingDetails']['activeLiveChatId'] ?? null;
    }

    private function pollMessages(): bool
    {
        $skipBacklog = $this->isFirstPollAfterConnect;
        $this->isFirstPollAfterConnect = false;

        $params = [
            'part' => 'snippet,authorDetails',
            'liveChatId' => $this->liveChatId,
            'maxResults' => 200,
        ];

        if ($this->nextPageToken !== null) {
            $params['pageToken'] = $this->nextPageToken;
        }

        $url = self::API_BASE . '/liveChat/messages?' . http_build_query($params);
        $response = $this->requestJson($url);

        if ($response === null || isset($response['error'])) {
            $this->error('Poll response error: ' . json_encode($response));
            return false;
        }

        $this->nextPageToken = $response['nextPageToken'] ?? null;

        foreach ($response['items'] ?? [] as $item) {
            $messageId = $item['id'] ?? null;

            if ($messageId === null || isset($this->seenMessageIds[$messageId])) {
                continue;
            }

            $this->seenMessageIds[$messageId] = true;

            $snippet = $item['snippet'] ?? [];
            $author = $item['authorDetails'] ?? [];
            $user = $author['displayName'] ?? 'Unknown';
            $publishedAt = new DateTimeImmutable($snippet['publishedAt'] ?? 'now');

            if (
                $skipBacklog
                && $this->connectedAt !== null
                && $publishedAt <= $this->connectedAt
            ) {
                continue;
            }

            $message = new Message(
                user: $user,
                content: $snippet['displayMessage'] ?? '',
                platform: 'youtube',
                timestamp: $publishedAt,
                color: $this->chatStorage->getUserColor($user),
            );

            $this->chatStorage->appendMessage($message);
            $this->writeln("[{$message->user}] {$message->content}");
        }

        return true;
    }

    private function requestJson(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", [
                    'Authorization: Bearer ' . $this->accessToken,
                    'Accept: application/json',
                ]),
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            return null;
        }

        $data = json_decode($result, true);

        if (isset($data['error']['code']) && $data['error']['code'] === 401) {
            $this->info('Access token expired, refreshing...');
            if ($this->refreshAccessToken()) {
                $this->info('Retrying request...');
                return $this->requestJson($url);
            }
            $this->error('Token refresh failed, returning null');
            return null;
        }

        if (isset($data['error'])) {
            $this->error('API error: ' . json_encode($data['error']));
        }

        return $data;
    }

    private function refreshAccessToken(): bool
    {
        $response = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'client_id' => env('YOUTUBE_CLIENT_ID'),
                    'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
                    'refresh_token' => env('YOUTUBE_REFRESH_TOKEN'),
                    'grant_type' => 'refresh_token',
                ]),
            ],
        ]));

        if ($response === false) {
            $this->error('Failed to refresh token');
            return false;
        }

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            $this->accessToken = $data['access_token'];
            $this->success('Token refreshed');
            return true;
        }

        $this->error('Token refresh failed: ' . ($data['error_description'] ?? 'Unknown error'));
        return false;
    }
}
