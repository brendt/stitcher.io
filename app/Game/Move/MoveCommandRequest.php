<?php

declare(strict_types=1);

namespace App\Game\Move;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class MoveCommandRequest implements Request
{
    use IsRequest;

    public string $playerId;
    public string $fromStationId;
    public string $toStationId;
    public ?int $deposit = null;
    public ?string $effectiveAt = null;
}
