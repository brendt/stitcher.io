<?php

namespace App\Chat;

use DateTimeImmutable;
use Tempest\Container\Singleton;
use Tempest\Container\Tag;
use Tempest\Storage\Storage;

#[Singleton]
final class ChatStorage
{
    private const MESSAGES_FILE = 'chat/messages.jsonl';
    private const COLORS_FILE = 'chat/user_colors.json';

    private const PALETTE = [
        '#FF6B6B', // Red
        '#4ECDC4', // Teal
        '#45B7D1', // Blue
        '#96CEB4', // Sage
        '#FFEAA7', // Yellow
        '#DDA0DD', // Plum
        '#98D8C8', // Mint
        '#F7DC6F', // Gold
        '#BB8FCE', // Purple
        '#85C1E9', // Sky
        '#F8B500', // Amber
        '#00CED1', // Cyan
        '#FF7F50', // Coral
        '#40E0D0', // Turquoise
        '#FFB6C1', // Pink
        '#7FFF00', // Chartreuse
    ];

    private ?array $colors = null;

    public function __construct(
        #[Tag('local')] private Storage $storage,
    ) {}

    public function appendMessage(Message $message): void
    {
        $data = json_encode([
            'user' => $message->user,
            'content' => $message->content,
            'platform' => $message->platform,
            'timestamp' => $message->timestamp->format('c'),
            'color' => $message->color,
        ]) . "\n";

        $existing = '';

        if ($this->storage->fileExists(self::MESSAGES_FILE)) {
            $existing = $this->storage->read(self::MESSAGES_FILE);
        }

        $this->storage->write(self::MESSAGES_FILE, $existing . $data);
    }

    /** @return Message[] */
    public function getMessages(): array
    {
        if (!$this->storage->fileExists(self::MESSAGES_FILE)) {
            return [];
        }

        $content = $this->storage->read(self::MESSAGES_FILE);
        $lines = explode("\n", trim($content));

        $messages = [];

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $data = json_decode($line, true);

            if ($data === null) {
                continue;
            }

            $messages[] = new Message(
                user: $data['user'],
                content: $data['content'],
                platform: $data['platform'],
                timestamp: new DateTimeImmutable($data['timestamp']),
                color: $data['color'] ?? $this->getUserColor($data['user']),
            );
        }

        return $messages;
    }

    public function getUserColor(string $user): string
    {
        $colors = $this->loadColors();

        if (!isset($colors[$user])) {
            $colors[$user] = self::PALETTE[count($colors) % count(self::PALETTE)];
            $this->saveColors($colors);
        }

        return $colors[$user];
    }

    private function loadColors(): array
    {
        if ($this->colors !== null) {
            return $this->colors;
        }

        if (!$this->storage->fileExists(self::COLORS_FILE)) {
            $this->colors = [];
            return $this->colors;
        }

        $this->colors = json_decode($this->storage->read(self::COLORS_FILE), true) ?? [];

        return $this->colors;
    }

    private function saveColors(array $colors): void
    {
        $this->colors = $colors;
        $this->storage->write(self::COLORS_FILE, json_encode($colors, JSON_PRETTY_PRINT));
    }
}
