<?php

namespace App\Chat;

use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use function Tempest\env;

final class YouTubeChatCommand
{
    use HasConsole;

    private const API_BASE = 'https://www.googleapis.com/youtube/v3';

    private ?string $accessToken = null;
    private ?string $liveChatId = null;
    private ?string $nextPageToken = null;
    private array $seenMessageIds = [];

    public function __construct(
        private ChatStorage $chatStorage,
    ) {}

    #[ConsoleCommand('chat:youtube')]
    public function __invoke(): void
    {
        $this->accessToken = env('YOUTUBE_ACCESS_TOKEN');
        $channelId = env('YOUTUBE_CHANNEL_ID');

        $this->info("Watching channel {$channelId} for live streams...");

        while (true) {
            // Wait for a live stream
            $videoId = $this->waitForLiveStream($channelId);

            $this->liveChatId = $this->fetchLiveChatId($videoId);

            if ($this->liveChatId === null) {
                $this->error('Could not find live chat ID. Retrying...');
                sleep(10);
                continue;
            }

            $this->success("Connected to live chat: {$this->liveChatId}");

            // Poll messages until stream ends
            $this->pollUntilStreamEnds();

            $this->info('Stream ended. Waiting for new stream...');
            $this->liveChatId = null;
            $this->nextPageToken = null;
            $this->seenMessageIds = [];
        }
    }

    private function waitForLiveStream(string $channelId): string
    {
        while (true) {
            $videoId = $this->findLiveVideoId($channelId);

            if ($videoId !== null) {
                $this->success("Found live video: {$videoId}");
                return $videoId;
            }

            sleep(120);
        }
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

    private function findLiveVideoId(string $channelId): ?string
    {
        $url = self::API_BASE . '/search?' . http_build_query([
            'part' => 'id',
            'channelId' => $channelId,
            'eventType' => 'live',
            'type' => 'video',
            'maxResults' => 1,
        ]);

        $response = $this->request($url);

        return $response['items'][0]['id']['videoId'] ?? null;
    }

    private function fetchLiveChatId(string $videoId): ?string
    {
        $url = self::API_BASE . '/videos?' . http_build_query([
            'part' => 'liveStreamingDetails',
            'id' => $videoId,
        ]);

        $response = $this->request($url);

        return $response['items'][0]['liveStreamingDetails']['activeLiveChatId'] ?? null;
    }

    private function pollMessages(): bool
    {
        $params = [
            'part' => 'snippet,authorDetails',
            'liveChatId' => $this->liveChatId,
            'maxResults' => 200,
        ];

        if ($this->nextPageToken !== null) {
            $params['pageToken'] = $this->nextPageToken;
        }

        $url = self::API_BASE . '/liveChat/messages?' . http_build_query($params);
        $response = $this->request($url);

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

            $message = new Message(
                user: $user,
                content: $snippet['displayMessage'] ?? '',
                platform: 'youtube',
                timestamp: new DateTimeImmutable($snippet['publishedAt'] ?? 'now'),
                color: $this->chatStorage->getUserColor($user),
            );

            $this->chatStorage->appendMessage($message);
            $this->writeln("[{$message->user}] {$message->content}");
        }

        return true;
    }

    private function request(string $url): ?array
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

        // Handle token refresh if needed
        if (isset($data['error']['code']) && $data['error']['code'] === 401) {
            $this->info('Access token expired, refreshing...');
            if ($this->refreshAccessToken()) {
                $this->info('Retrying request...');
                return $this->request($url);
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
