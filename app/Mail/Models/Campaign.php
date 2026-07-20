<?php

namespace App\Mail\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;

use function Tempest\Database\query;

final class Campaign
{
    use IsDatabaseModel;

    public string $path;
    public DateTime $startedAt;
    public ?DateTime $processedAt = null;
    public ?DateTime $failedAt = null;
    public ?string $log = null;

    public int $totalCount {
        get => query(OutboxMail::class)
            ->count()
            ->where('campaign_id', $this->id)
            ->execute();
    }

    public int $sentCount {
        get => query(OutboxMail::class)
            ->count()
            ->where('campaign_id', $this->id)
            ->whereNotNull('sentAt')
            ->execute();
    }

    public bool $isSending {
        get => query(OutboxMail::class)
            ->count()
            ->where('campaign_id', $this->id)
            ->whereNull('sentAt')
            ->whereNull('failedAt')
            ->execute() > 0;
    }
}
