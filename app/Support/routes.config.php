<?php

use Tempest\Core\Environment;
use Tempest\Router\RouteConfig;
use function Tempest\env;

return new RouteConfig(
    throwHttpExceptions: ! Environment::tryFrom(env('ENVIRONMENT'))?->isProduction(),
);