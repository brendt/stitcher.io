<?php

namespace App\Dungeon\Http;

use Attribute;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_CLASS)]
final class DungeonActionEndpoint implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleware[] = DungeonActionEndpointMiddleware::class;

        return $route;
    }
}