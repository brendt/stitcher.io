<?php

declare(strict_types=1);

namespace App\Game\Score;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class FinalizeMatchRequest implements Request
{
    use IsRequest;

    public ?bool $force = null;
    public ?int $durationSeconds = null;
    public ?int $hubBonus = null;
    public ?string $effectiveAt = null;
}
