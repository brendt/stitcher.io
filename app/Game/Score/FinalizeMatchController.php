<?php

declare(strict_types=1);

namespace App\Game\Score;

use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Router\Post;
use Tempest\Router\Stateless;

#[Stateless]
final readonly class FinalizeMatchController
{
    public function __construct(
        private FinalizeMatchResolver $resolver,
    ) {}

    #[Post('/games/{gameId}/commands/finalize-match')]
    public function __invoke(string $gameId, FinalizeMatchRequest $request): Response
    {
        return new Json($this->resolver->handle(gameId: $gameId, request: $request));
    }
}
