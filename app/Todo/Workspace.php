<?php

namespace App\Todo;

use App\Support\Authentication\User;
use Tempest\Database\IsDatabaseModel;
use Tempest\Router\Bindable;

final class Workspace implements Bindable
{
    use IsDatabaseModel;

    public function __construct(
        public User $user,
        public string $name,
    ) {}

    /** @var \App\Todo\Project[] */
    public array $projects = [];
}
