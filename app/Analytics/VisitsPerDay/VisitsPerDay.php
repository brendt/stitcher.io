<?php

namespace App\Analytics\VisitsPerDay;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('visits_per_day')]
final class VisitsPerDay implements Chartable
{
    public DateTime $date;
    public int $count;

    public string $label {
        get {
            return $this->date->format('Y-m-d');
        }
    }

    public mixed $value {
        get {
            return $this->count;
        }
    }
}