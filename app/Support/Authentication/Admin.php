<?php

namespace App\Support\Authentication;

use Attribute;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

#[Attribute]
final class Admin implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        $route->middleware[] = AdminMiddleware::class;

        return $route;
    }
}