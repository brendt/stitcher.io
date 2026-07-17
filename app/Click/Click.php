<?php

namespace App\Click;

use Tempest\Database\IsDatabaseModel;

final class Click
{
    use IsDatabaseModel;

    public string $uri;
    public int $clicks = 0;
}