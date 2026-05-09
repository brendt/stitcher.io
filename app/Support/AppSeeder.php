<?php

namespace App\Support;

use App\Blog\BlogPostRepository;
use App\Blog\Comment;
use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonUserStats;
use App\Support\Authentication\Role;
use App\Support\Authentication\User;
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

        foreach (range(0, 100) as $i) {
            $user = User::create(
                name: 'Brent',
                email: "test+{$i}@example.com",
                role: Role::USER,
            );

            $games = random_int(0, 1000);
            $wins = random_int(0, $games);
            $losses = $games - $wins;

            DungeonUserStats::create(
                userId: $user->id->value,
                campaignId: Dungeon::CURRENT_CAMPAIGN,
                coins: 0,
                tokens: 10,
                victoryPoints: random_int(0, 1000),
                experience: random_int(0, 100_000),
                wins: $wins,
                losses: $losses,
                games: $games,
                shards: random_int(0, 1000),
                runPrice: 2500,
                extra: [Dungeon::HAS_SEEN_SHARD_SHOP => false, Dungeon::NICKNAME => "Player {$i}"],
            );
        }

//        foreach ($this->repository->all() as $post) {
//            foreach (range(1, 5) as $i) {
//                Comment::create(
//                    user: $user,
//                    for: $post->slug,
//                    content: "Hello {$i}",
//                    createdAt: DateTime::now()->minusDays(random_int(0, 10)),
//                );
//            }
//        }
    }
}