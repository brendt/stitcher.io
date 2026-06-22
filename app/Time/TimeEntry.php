<?php

namespace App\Time;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;

final class TimeEntry
{
    use IsDatabaseModel;

    public DateTime $start;

    public ?DateTime $end;

    public float $totalHours {
        get {
            $end = $this->end ?? DateTime::now();

            $diff = $end->getTimestamp()->getSeconds() - $this->start->getTimestamp()->getSeconds();

            return round($diff / 3600, 2);
        }
    }
}