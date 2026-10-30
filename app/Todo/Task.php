<?php

namespace App\Todo;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;
use Tempest\Router\Bindable;

final class Task implements Bindable
{
    use IsDatabaseModel;

    public function __construct(
        public Project $project,
        public string $name,
        public ?DateTime $completedAt = null,
    ) {}
}
