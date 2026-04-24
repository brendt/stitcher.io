<?php

namespace App\Dungeon\Http;

use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Cards\Clarity;
use App\Dungeon\Cards\LocateLake;
use App\Dungeon\Cards\ManaIncreaseMajor;
use App\Dungeon\Cards\Reset;
use App\Dungeon\Cards\StabilityMajor;
use App\Dungeon\Cards\StabilityMinor;
use App\Dungeon\Cards\SupportMajor;
use App\Dungeon\Cards\WaterWalkerMajor;
use App\Dungeon\Cards\WaterWalkerMinor;
use App\Dungeon\Dungeon;
use App\Dungeon\Point;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\DungeonRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Dungeon\Tile;
use App\Support\Authentication\User;
use Tempest\Core\Environment;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\View\view;

final readonly class DemoController
{
    #[Get('/dungeon/demo')]
    public function demo(
        DungeonRepository $repository,
        DeckRepository $deckRepository,
        StatsRepository $statsRepository,
        User $user,
        Environment $environment,
    ): Redirect|View
    {
        if (! $environment->isLocal()) {
            return view('dungeon-aidan.view.php');
        }

        $dungeon = Dungeon::new($user, $deckRepository, $statsRepository, deck: [
            new LocateLake(),
            new LocateLake(),
            new LocateLake(),
            new LocateLake(),
            new LocateLake(),
            new LocateLake(),
            new LocateLake(),
            new Reset(),
        ]);

        $repository->persist($dungeon);

        $dungeon->cheat = true;
        $dungeon->mana = 100000;
        $dungeon->health = 1000;
        $dungeon->stability = 100;

        $dungeon->spawnLake(new Point(2, 0));
        $dungeon->spawnLake(new Point(4, 4));

        $dungeon->addTile(new Tile(new Point(1, 1), isTrapped: true));

//        $directions = arr(Direction::cases());

        for ($i = 0; $i < 100; $i++) {
//            $dungeon->move($directions->random());
        }

        $dungeon->spawnDweller(new Point(2,2));
//        $dungeon->spawnDweller();
//        $dungeon->spawnDweller();
//        $dungeon->spawnVictoryPoint(new Point(5, 5));
//        $dungeon->spawnShard(new Point(5, 6));
            $dungeon->spawnHealthAltar(new Point(10, 8));
            $dungeon->spawnStabilityAltar(new Point(10, 10));
            $dungeon->spawnManaAltar(new Point(10, 12));

        $dungeon->spawnShard(new Point(0,1));

        $repository->persist($dungeon);

        return new Redirect(uri([DungeonGameController::class, 'dungeon']) . '?debug');
    }
}