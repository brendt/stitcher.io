<?php

namespace App\Todo;

use App\Support\Authentication\Profile;
use Tempest\Database\IsDatabaseModel;
use Tempest\Router\Bindable;

final class Workspace implements Bindable
{
    use IsDatabaseModel;

    public function __construct(
        public Profile $profile,
        public string $name,
    ) {}

    /** @var \App\Todo\Project[] */
    public array $projects = [];
}
