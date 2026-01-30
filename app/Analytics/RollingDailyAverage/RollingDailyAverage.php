<?php

namespace App\Analytics\RollingDailyAverage;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('rolling_daily_average')]
final class RollingDailyAverage implements Chartable
{
    public DateTime $date;
    public int $count;

    public string $label {
        get {
            return $this->date->format('Y-m-d');
        }
    }

    public int $value {
        get {
            return $this->count;
        }
    }
}