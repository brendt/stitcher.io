<?php

namespace App\Todo;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Virtual;
use Tempest\DateTime\DateTime;
use Tempest\Router\Bindable;

final class Task implements Bindable
{
    use IsDatabaseModel;

    public function __construct(
        public Project $project,
        public string $name,
        public ?DateTime $completedAt = null,
        public TaskKind $kind = TaskKind::TASK,
        // Open tasks and text are shown in this order, which is the order they were saved in
        public int $position = 0,
    ) {}

    #[Virtual]
    public bool $isText {
        get => $this->kind === TaskKind::TEXT;
    }
}
