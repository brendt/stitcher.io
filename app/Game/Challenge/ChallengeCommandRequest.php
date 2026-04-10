<?php

declare(strict_types=1);

namespace App\Game\Challenge;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class ChallengeCommandRequest implements Request
{
    use IsRequest;

    public string $playerId;
    public string $stationId;
    public ?string $effectiveAt = null;
}
