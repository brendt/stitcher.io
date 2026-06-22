<?php

namespace App\Time;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\DateTime\Timezone;
use Tempest\Router\Bindable;
use DateTime as PHPDateTime;

final class TimeEntry implements Bindable
{
    use IsDatabaseModel;

    public DateTime $start;

    public ?DateTime $end;

    public float $totalHours {
        get {
            $end = $this->end ?? DateTime::now(Timezone::EUROPE_BRUSSELS);

            $diff = new PHPDateTime($this->start->format(FormatPattern::SQL_DATE_TIME))
                ->diff(new PHPDateTime($end->format(FormatPattern::SQL_DATE_TIME)));

            return ($diff->days * 24) + $diff->h + round($diff->i / 60, 2);
        }
    }
}