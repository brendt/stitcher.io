<?php

namespace App\Support\Authentication;

use Tempest\Database\IsDatabaseModel;

/**
 * The person behind one or more users, so that they share the same data, like todos, no matter which login they use.
 */
final class Profile
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
    ) {}

    // The todo workspace that was opened last, so that it's opened again next time
    public ?int $latestWorkspaceId = null;

    /** @var \App\Support\Authentication\User[] */
    public array $users = [];
}
