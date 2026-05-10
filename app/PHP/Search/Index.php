<?php

namespace App\Php\Search;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('index')]
final class Index
{
    use IsDatabaseModel;

    public string $title;
    public string $uri;
}