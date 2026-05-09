<?php

namespace App\Analytics\VisitsPerMinute;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('visits_per_minute')]
final class VisitsPerMinute implements Chartable
{
    public DateTime $time;
    public int $count;

    public string $label {
        get {
            return $this->time->format('H:i');
        }
    }

    public int $value {
        get {
            return $this->count;
        }
    }
}