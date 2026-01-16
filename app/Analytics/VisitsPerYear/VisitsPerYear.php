<?php

namespace App\Analytics\VisitsPerYear;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('visits_per_year')]
final class VisitsPerYear implements Chartable
{
    public DateTime $date;
    public int $count;

    public string $label {
        get {
            return $this->date->format('Y');
        }
    }

    public int $value {
        get {
            return $this->count;
        }
    }
}