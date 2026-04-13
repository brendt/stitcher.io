<?php

namespace App\Dungeon;

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
use function Tempest\View\view;

#[Stateless]
final class DungeonController
{
    #[Get('/dungeon/new')]
    public function new(DungeonRepository $repository): Redirect
    {
        $dungeon = new Dungeon();

        $repository->persist($dungeon);

        $repository->persist($dungeon);

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