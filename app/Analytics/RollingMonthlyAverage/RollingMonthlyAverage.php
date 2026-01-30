<?php

namespace App\Analytics\RollingMonthlyAverage;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('rolling_monthly_average')]
final class RollingMonthlyAverage implements Chartable
{
    public DateTime $date;
    public int $count;

    public string $label {
        get {
            return $this->date->format('Y-m');
        }
    }

    public int $value {
        get {
            return $this->count;
        }
    }
}