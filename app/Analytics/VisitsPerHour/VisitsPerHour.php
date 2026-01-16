<?php

namespace App\Analytics\VisitsPerHour;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('visits_per_hour')]
final class VisitsPerHour implements Chartable
{
    public DateTime $hour;
    public int $count;

    public string $label {
        get {
            return $this->hour->format('H:i');
        }
    }

    public int $value {
        get {
            return $this->count;
        }
    }
}