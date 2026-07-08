<?php

namespace App\Mail\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;

final class OutboxMail
{
    use IsDatabaseModel;

    public OutboxCampaign $campaign;
    public string $receiver;
    public string $subject;
    public string $content;
    public ?DateTime $sendingAt = null;
    public ?DateTime $sentAt = null;
    public ?DateTime $failedAt = null;
    public ?string $log = null;
}
