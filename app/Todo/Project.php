<?php

namespace App\Todo;

use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;
use Tempest\Router\Bindable;

final class Project implements Bindable
{
    use IsDatabaseModel;

    public function __construct(
        public Workspace $workspace,
        public string $name,
        public ?DateTime $completedAt = null,
        public ?DateTime $archivedAt = null,
        public bool $collapsed = false,
    ) {}

    /** @var \App\Todo\Task[] */
    public array $tasks = [];
}
