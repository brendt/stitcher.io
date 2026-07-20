<?php

namespace Tests\Mail;

use Exception;
use Tempest\Mail\Email;
use Tempest\Mail\Mailer;

final readonly class ExceptionMailer implements Mailer
{
    public function send(Email $email): void
    {
        throw new Exception('Nope');
    }
}
