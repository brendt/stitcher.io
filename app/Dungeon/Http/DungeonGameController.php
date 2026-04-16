<?php

namespace App\Dungeon\Http;

use App\Dungeon\Direction;
use App\Dungeon\Dungeon;
use App\Dungeon\Point;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\DungeonRepository;
use App\Support\Authentication\User;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotAcceptable;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\View\view;

#[DungeonAuth]
final class DungeonGameController
{
    #[Get('/dungeon/new')]
    public function new(DungeonRepository $repository, DeckRepository $deckRepository, User $user, Request $request): Redirect
    {
        $dungeon = Dungeon::new($user, $deckRepository);

        $repository->persist($dungeon);

        if ($request->has('demo')) {
            $dungeon->cheat = true;
            $dungeon->mana = 1000;
            $dungeon->health = 1000;

            $directions = arr(Direction::cases());

            for ($i = 0; $i < 100; $i++) {
                $dungeon->move($directions->random());
            }

            $dungeon->spawnDweller(new Point(10, 10));
            $dungeon->spawnDweller();
            $dungeon->spawnDweller();
            $dungeon->spawnDweller();
            $dungeon->spawnVictoryPoint(new Point(5, 5));
            $dungeon->spawnShard(new Point(5, 6));
//            $dungeon->spawnHealthAltar(new Point(10, 8));
//            $dungeon->spawnStabilityAltar(new Point(10, 10));
//            $dungeon->spawnManaAltar(new Point(10, 12));

//            $dungeon->spawnArtifact(new Point(0,0));

            $repository->persist($dungeon);
        }

        return new Redirect(uri([self::class, 'dungeon']));
    }

    #[Get('/dungeon/game')]
    public function dungeon(Dungeon $dungeon): View
    {
        return view('dungeon-game.view.php', dungeon: $dungeon);
    }

    #[Get('/dungeon/state')]
    public function state(Dungeon $dungeon): Ok
    {
        return new Ok($dungeon->toArray());
    }

    #[DungeonActionEndpoint, Post('/dungeon/move')]
    public function move(Dungeon $dungeon, Request $request): Response
    {
        $direction = Direction::tryFrom($request->get('direction'));

        if (! $direction) {
            return new NotAcceptable();
        }

        $dungeon->move($direction);

        return new Ok();
    }

    #[DungeonActionEndpoint, Post('/dungeon/play-card')]
    public function playCard(Dungeon $dungeon, Request $request): Response
    {
        $card = $request->get('card');

        if (! $card) {
            return new NotAcceptable();
        }

        $dungeon->playCard($card);

        return new Ok();
    }

    #[DungeonActionEndpoint, Post('/dungeon/interact-with-tile')]
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

    #[DungeonActionEndpoint, Post('/dungeon/exit')]
    public function exit(Dungeon $dungeon): Response
    {
        $dungeon->exit();

        return new Ok();
    }
}
