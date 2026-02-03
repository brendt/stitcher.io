<?php

namespace App\Analytics\VisitsPerPostPerDay;

use App\Analytics\Chartable;
use DateTime;
use Tempest\Database\Table;

#[Table('visits_per_post_per_day')]
final class VisitsPerPostPerDay implements Chartable
{
    public string $uri;
    public DateTime $date;
    public int $count;

    public string $label {
        get => $this->date->format('Y-m-d');
    }

    public int $value {
        get => $this->count;
    }
}