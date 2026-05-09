<?php

namespace App\Analytics\VisitsPerMonth;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('visits_per_month')]
final class VisitsPerMonth implements Chartable
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