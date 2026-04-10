<?php

declare(strict_types=1);

namespace App\Game\Move;

use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Router\Post;
use Tempest\Router\Stateless;

#[Stateless]
final readonly class MoveCommandController
{
    public function __construct(
        private MoveCommandResolver $resolver,
    ) {}

    #[Post('/games/{gameId}/commands/move')]
    public function __invoke(string $gameId, MoveCommandRequest $request): Response
    {
        return new Json($this->resolver->handle(gameId: $gameId, request: $request));
    }
}
