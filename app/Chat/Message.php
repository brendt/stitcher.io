<?php

namespace App\Chat;

use DateTimeImmutable;

final class Message
{
    public function __construct(
        public string $user,
        public string $content,
        public string $platform,
        public DateTimeImmutable $timestamp,
        public string $color,
    ) {}
}