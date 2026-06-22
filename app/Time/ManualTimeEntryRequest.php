<?php

namespace App\Time;

use Tempest\DateTime\DateTime;
use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class ManualTimeEntryRequest implements Request
{
    use IsRequest;

    public DateTime $start;
    public ?DateTime $end;
    public bool $isVacation = false;
}