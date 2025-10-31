<?php

namespace App\Map;

use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\view;

final readonly class MapController
{
    #[Get('/map')]
    public function index(): View
    {
        $game = MapGame::init(rand(1, 1000000000));
        $board = $game->baseLayer->generate()->getBoard();

        return view('map.view.php',
            game: $game,
            board: $board,
        );
    }
}