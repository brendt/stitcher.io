<?php

namespace App\Nws;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;

final class Nws
{
    use IsDatabaseModel;

    public string $uri;
    public string $title;
    public DateTime $publishedAt;
    public string $summary;
    public ?array $sentiment = null;
    public ?array $keywords = null;
}