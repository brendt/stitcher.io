<?php

namespace Tests\Mail;

use Tempest\Mail\Email;
use Tempest\Mail\Mailer;

final readonly class NullMailer implements Mailer
{
    public function send(Email $email): void
    {
        return;
    }
}
