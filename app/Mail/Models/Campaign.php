<?php

namespace App\Mail\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;

final class Campaign
{
    use IsDatabaseModel;

    public string $path;
    public DateTime $startedAt;
    public ?DateTime $endedAt = null;
    public ?DateTime $failedAt = null;
    public ?string $log = null;
}
