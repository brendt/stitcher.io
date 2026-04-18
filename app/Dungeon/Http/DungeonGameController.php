<?php

namespace App\Dungeon\Http;

use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Cards\StabilityMajor;
use App\Dungeon\Direction;
use App\Dungeon\Dungeon;
use App\Dungeon\Point;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\DungeonRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Support\Authentication\User;
use Tempest\Core\Environment;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotAcceptable;
use Tempest\Http\Responses\NotFound;
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
    public function new(
        DungeonRepository $repository,
        DeckRepository $deckRepository,
        StatsRepository $statsRepository,
        User $user
    ): Redirect {
        if ($statsRepository->forUser($user)->tokens < 1) {
            return new Redirect(uri([DungeonHomeController::class, 'index']));
        }

        $statsRepository->decreaseTokens($user, 1);
        $statsRepository->increaseStats($user, games: 1);

        $dungeon = Dungeon::new($user, $deckRepository, $statsRepository);

        $repository->persist($dungeon);

        return new Redirect(uri([self::class, 'dungeon']));
    }

    #[Get('/dungeon/demo')]
    public function demo(
        DungeonRepository $repository,
        DeckRepository $deckRepository,
        StatsRepository $statsRepository,
        User $user,
        Environment $environment,
    ): Redirect|NotFound
    {
        if (! $environment->isLocal()) {
            return new NotFound();
        }

        $dungeon = Dungeon::new($user, $deckRepository, $statsRepository, deck: [
            new StabilityMajor(),
            new StabilityMajor(),
            new StabilityMajor(),
            new StabilityMajor(),
            new BreakthroughMajor(),
        ]);

        $repository->persist($dungeon);

        $dungeon->cheat = true;
        $dungeon->mana = 1000;
        $dungeon->health = 1000;
        $dungeon->stability = 10;

//        $directions = arr(Direction::cases());

        for ($i = 0; $i < 100; $i++) {
//            $dungeon->move($directions->random());
        }

        $dungeon->spawnDweller();
        $dungeon->spawnDweller();
        $dungeon->spawnDweller();
        $dungeon->spawnVictoryPoint(new Point(5, 5));
        $dungeon->spawnShard(new Point(5, 6));
//            $dungeon->spawnHealthAltar(new Point(10, 8));
//            $dungeon->spawnStabilityAltar(new Point(10, 10));
//            $dungeon->spawnManaAltar(new Point(10, 12));

        $dungeon->spawnShard(new Point(0,1));

        $repository->persist($dungeon);


        return new Redirect(uri([self::class, 'dungeon']) . '?debug');
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

    #[DungeonActionEndpoint, Post('/dungeon/resign')]
    public function resign(Dungeon $dungeon): Response
    {
        $dungeon->resign();

        return new Ok();
    }
}
