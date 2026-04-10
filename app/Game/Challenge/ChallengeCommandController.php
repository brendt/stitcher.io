<?php

declare(strict_types=1);

namespace App\Game\Challenge;

use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Router\Post;

final readonly class ChallengeCommandController
{
    public function __construct(
        private ChallengeCommandResolver $resolver,
    ) {}

    #[Post('/games/{gameId}/commands/complete-challenge')]
    public function __invoke(string $gameId, ChallengeCommandRequest $request): Response
    {
        return new Json($this->resolver->handle(gameId: $gameId, request: $request));
    }
}
