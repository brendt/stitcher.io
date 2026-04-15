<?php

namespace App\Dungeon;

use App\Dungeon\Cards\BeaconMajor;
use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Cards\EmergencyExitMajor;
use App\Dungeon\Cards\EmergencyExitMinor;
use App\Dungeon\Cards\HealMajor;
use App\Dungeon\Cards\KillDwellerMajor;
use App\Dungeon\Cards\TestCard;
use App\Dungeon\Support\DungeonAction;
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
        $dungeon = new Dungeon(deck: [
            new BeaconMajor(),
            new EmergencyExitMinor(),
            new HealMajor(),
        ]);

        $repository->persist($dungeon);

        if ($request->has('demo')) {
            $directions = arr(Direction::cases());

            for ($i = 0; $i < 1000; $i++) {
                $dungeon->move($directions->random());
            }

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

    #[DungeonAction, Post('/dungeon/move')]
    public function move(Dungeon $dungeon, Request $request): Response
    {
        $direction = Direction::tryFrom($request->get('direction'));

        if (! $direction) {
            return new NotAcceptable();
        }

        $dungeon->move($direction);

        return new Ok();
    }
}