<?php

namespace App\Analytics\VisitsPerPostPerDay;

use DateTime;
use Tempest\Database\Table;

#[Table('visits_per_post_per_day')]
final class VisitsPerPostPerDay
{
    public string $uri;
    public DateTime $date;
    public int $count;
}