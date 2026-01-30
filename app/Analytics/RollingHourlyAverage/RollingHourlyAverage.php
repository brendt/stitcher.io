<?php

namespace App\Analytics\RollingHourlyAverage;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('rolling_hourly_average')]
final class RollingHourlyAverage implements Chartable
{
    public DateTime $date;
    public int $count;

    public string $label {
        get {
            return $this->date->format('Y-m-d H:i');
        }
    }

    public int $value {
        get {
            return $this->count;
        }
    }
}