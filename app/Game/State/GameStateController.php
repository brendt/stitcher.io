<?php

declare(strict_types=1);

namespace App\Game\State;

use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Router\Get;

final readonly class GameStateController
{
    public function __construct(
        private GameStateResolver $resolver,
    ) {}

    #[Get('/games/{gameId}/state')]
    public function __invoke(string $gameId, GameStateRequest $request): Response
    {
        return new Json($this->resolver->resolve(
            gameId: $gameId,
            includeTimeline: (bool) ($request->timeline ?? false),
        ));
    }
}
