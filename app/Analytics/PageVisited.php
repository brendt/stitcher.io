<?php

declare(strict_types=1);

namespace App\Analytics;

use App\Support\StoredEvents\HasCreatedAtDate;
use App\Support\StoredEvents\ShouldBeStored;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

final class PageVisited implements ShouldBeStored, HasCreatedAtDate
{
    public string $uuid;

    public function __construct(
        public string $url,
        public DateTimeImmutable $visitedAt,
        public string $ip,
        public string $userAgent,
        public string $raw,
    ) {
        $this->uuid = Uuid::v4()->toString();
    }

    public DateTimeImmutable $createdAt {
        get => $this->visitedAt;
    }

    public function serialize(): string
    {
        return json_encode([
            'uuid' => $this->uuid,
            'url' => $this->url,
            'visitedAt' => $this->visitedAt->format('c'),
            'ip' => $this->ip,
            'userAgent' => $this->userAgent,
            'raw' => $this->raw,
            'uri' => $this->url,
        ]);
    }

    public static function unserialize(string $payload): self
    {
        $data = json_decode($payload, true);

        $self = new self(
            url: $data['url'],
            visitedAt: new DateTimeImmutable($data['visitedAt']),
            ip: $data['ip'],
            userAgent: $data['userAgent'],
            raw: $data['raw'],
        );

        $self->uuid = $data['uuid'];

        return $self;
    }
}
