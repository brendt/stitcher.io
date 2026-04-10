<?php

declare(strict_types=1);

namespace App\Game\State;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class GameStateRequest implements Request
{
    use IsRequest;

    public ?bool $timeline = null;
}
