<?php

use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\DatabaseSessionConfig;

return new DatabaseSessionConfig(
    expiration: Duration::days(30),
);
