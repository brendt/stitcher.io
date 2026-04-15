<?php

namespace App\Dungeon\Support;

use Attribute;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_CLASS)]
final class DungeonEndpoint implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleware[] = DungeonActionMiddleware::class;

        return $route;
    }
}