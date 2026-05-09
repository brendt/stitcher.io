<?php

namespace App\Analytics\VisitsPerPostPerWeek;

use App\Analytics\AnalyticsController;
use DateTime;
use Tempest\Database\Table;
use function Tempest\Router\uri;

#[Table('visits_per_post_per_week')]
final class VisitsPerPostPerWeek
{
    public string $uri;
    public DateTime $date;
    public int $count;

    public string $detailUri {
        get {
            return uri([AnalyticsController::class, 'forPost'], uri: ltrim($this->uri, '/'));
        }
    }
}