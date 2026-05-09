<?php

namespace App\Dungeon\Http;

use Attribute;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_CLASS)]
final class DungeonAuth implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleware[] = DungeonAuthMiddleware::class;

        return $route;
    }
}