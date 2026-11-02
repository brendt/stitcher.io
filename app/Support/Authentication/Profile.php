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

    /** @var \App\Support\Authentication\User[] */
    public array $users = [];
}
