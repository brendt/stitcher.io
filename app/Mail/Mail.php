<?php

namespace App\Mail;

use Tempest\DateTime\DateTime;
use function Tempest\Router\uri;

final class Mail
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $content,
        public DateTime $date,
        public ?string $pretext = null,
    ) {}

    public string $cleanContent {
        get => str_replace('::subscriber.first_name::', '', $this->content);
    }

    public string $uri {
        get => uri([MailController::class, 'show'], slug: $this->slug);
    }
}