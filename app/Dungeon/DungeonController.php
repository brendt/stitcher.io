<?php

namespace App\Dungeon;

use App\Dungeon\Cards\BeaconMajor;
use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Cards\Clarity;
use App\Dungeon\Cards\EmergencyExitMinor;
use App\Dungeon\Cards\HealMajor;
use App\Dungeon\Cards\KillDwellerMajor;
use App\Dungeon\Cards\TrapDisarmMajor;
use App\Dungeon\Cards\TrapDisarmMinor;
use App\Dungeon\Cards\UpperHandMajor;
use App\Dungeon\Support\DungeonEndpoint;
use App\Dungeon\Support\DungeonRepository;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\NotAcceptable;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Stateless;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\View\view;

#[Stateless]
final class DungeonController
{
    #[Get('/dungeon/new')]
    public function new(DungeonRepository $repository, Request $request): Redirect
    {
        $dungeon = Dungeon::new(deck: [
            new TrapDisarmMajor(),
            new TrapDisarmMinor(),
        ]);

        $repository->persist($dungeon);

        if ($request->has('demo')) {
            $dungeon->cheat = true;
            $dungeon->mana = 1000;

            $directions = arr(Direction::cases());

            for ($i = 0; $i < 1000; $i++) {
                $dungeon->move($directions->random());
            }

            $dungeon->addTile(new Tile(new Point(5, 5), isTrapped: true));
            $dungeon->spawnDweller(new Point(10, 10));
            $dungeon->spawnDweller();
            $dungeon->spawnDweller();
            $dungeon->spawnDweller();
//            $dungeon->spawnArtifact(new Point(0,0));

            $repository->persist($dungeon);
        }

        return new Redirect(uri([self::class, 'dungeon']));
    }

    #[Get('/dungeon')]
    public function dungeon(Dungeon $dungeon): View
    {
        return view('dungeon.view.php', dungeon: $dungeon);
    }

    #[Get('/dungeon/state')]
    public function state(Dungeon $dungeon): Ok
    {
        return new Ok($dungeon->toArray());
    }

    #[DungeonEndpoint, Post('/dungeon/move')]
    public function move(Dungeon $dungeon, Request $request): Response
    {
        $direction = Direction::tryFrom($request->get('direction'));

        if (! $direction) {
            return new NotAcceptable();
        }

        $dungeon->move($direction);

        return new Ok();
    }

    #[DungeonEndpoint, Post('/dungeon/play-card')]
    public function playCard(Dungeon $dungeon, Request $request): Response
    {
        $card = $request->get('card');

        if (! $card) {
            return new NotAcceptable();
        }

        $dungeon->playCard($card);

        return new Ok();
    }

    #[DungeonEndpoint, Post('/dungeon/interact-with-tile')]
    public function interactWithTile(Dungeon $dungeon, Request $request): Response
    {
        $x = $request->get('x');
        $y = $request->get('y');

        if ($x === null || $y === null) {
            return new NotAcceptable();
        }

        $dungeon->interactWithTile(new Point($x, $y));

        return new Ok();
    }
}
