<?php

declare(strict_types=1);

namespace App\Game\Move;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class DebugSpawnBonusesCommandRequest implements Request
{
    use IsRequest;

    public string $playerId;
}

