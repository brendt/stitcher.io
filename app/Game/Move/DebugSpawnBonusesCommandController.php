<?php

declare(strict_types=1);

namespace App\Game\Move;

use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Router\Post;
use Tempest\Router\Stateless;

#[Stateless]
final readonly class DebugSpawnBonusesCommandController
{
    public function __construct(
        private MoveCommandResolver $resolver,
    ) {}

    #[Post('/games/{gameId}/commands/debug-spawn-bonuses')]
    public function __invoke(string $gameId, DebugSpawnBonusesCommandRequest $request): Response
    {
        return new Json($this->resolver->debugSpawnBonusesNearPlayer(
            gameId: $gameId,
            playerId: $request->playerId,
        ));
    }
}

