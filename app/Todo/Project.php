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
        // Projects are shown in this order, which is the order they were saved in
        public int $position = 0,
    ) {}

    /** @var \App\Todo\Task[] */
    public array $tasks = [];
}
