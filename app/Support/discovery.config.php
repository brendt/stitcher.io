<?php

use Tempest\Core\DiscoveryConfig;
use Tempest\Http\Session\VerifyCsrfMiddleware;

return new DiscoveryConfig()
    ->skipClasses(VerifyCsrfMiddleware::class);
