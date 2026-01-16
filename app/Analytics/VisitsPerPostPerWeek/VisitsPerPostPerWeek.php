<?php

namespace App\Analytics\VisitsPerPostPerWeek;

use DateTime;
use Tempest\Database\Table;

#[Table('visits_per_post_per_week')]
final class VisitsPerPostPerWeek
{
    public string $uri;
    public DateTime $date;
    public int $count;
}