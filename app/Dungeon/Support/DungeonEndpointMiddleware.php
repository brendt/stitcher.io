<?php

namespace App\Dungeon\Support;

use App\Dungeon\Dungeon;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[SkipDiscovery]
final readonly class DungeonEndpointMiddleware implements HttpMiddleware
{
    public function __construct(
        private Dungeon $dungeon,
        private DungeonRepository $dungeonRepository,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $next($request);

        $payload = [
            'version' => $this->dungeon->version,
            'changes' => $this->dungeon->consumeChanges(),
        ];

        $this->dungeonRepository->persist($this->dungeon);

        return new Ok($payload);
    }
}