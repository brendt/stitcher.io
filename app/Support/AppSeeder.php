<?php

namespace App\Support;

use App\Authentication\Role;
use App\Authentication\User;
use App\Blog\BlogPostRepository;
use App\Blog\Comment;
use Tempest\Database\DatabaseSeeder;
use Tempest\DateTime\DateTime;
use UnitEnum;
use function Tempest\env;

final class AppSeeder implements DatabaseSeeder
{
    public function __construct(
        private BlogPostRepository $repository,
    ) {}

    public function run(UnitEnum|string|null $database): void
    {
        $user = User::create(
            name: 'Brent',
            email: env('SEEDER_EMAIL', 'test@example.com'),
            role: Role::ADMIN,
        );

        foreach ($this->repository->all() as $post) {
            foreach (range(1, 5) as $i) {
                Comment::create(
                    user: $user,
                    for: $post->slug,
                    content: "Hello {$i}",
                    createdAt: DateTime::now()->minusDays(random_int(0, 10)),
                );
            }
        }
    }
}