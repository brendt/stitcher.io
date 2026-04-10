<?php

declare(strict_types=1);

namespace App\Game\Ui;

use App\Game\Challenge\ChallengeCommandResolver;
use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Map\MapGenerator;
use App\Game\Persistence\GameRepository;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\View\view;

final readonly class GameUiController
{
    public function __construct(
        private GameRepository $games,
        private MapGenerator $maps,
        private ChallengeCommandResolver $challenges,
    ) {}

    #[Get('/game/{gameId}')]
    public function show(string $gameId): View
    {
        return view(__DIR__ . '/game-page.view.php', gameId: $gameId);
    }

    #[Get('/game/demo')]
    public function demo(): Redirect
    {
        $gameId = 'demo-' . random_int(100000, 999999);
        $seed = random_int(1, 2_147_483_647);
        // Higher station density keeps corridor curvature smoother (~20 stations per cardinal segment).
        $map = $this->maps->generate(stationCount: 100, seed: $seed);

        $stationIds = array_values(array_keys($map->stations));
        $playerAStation = $stationIds[0];
        $playerBStation = $stationIds[(int) floor(count($stationIds) / 2)];

        $stations = $map->stations;
        $stations[$playerAStation] = $stations[$playerAStation]->withClaim(ownerId: 'p1', topValue: 1);
        $stations[$playerBStation] = $stations[$playerBStation]->withClaim(ownerId: 'p2', topValue: 1);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: $playerAStation),
                'p2' => new Player(id: 'p2', coins: 40, stationId: $playerBStation),
            ],
            stations: $stations,
            edges: $map->edges,
        );

        $this->games->save(
            game: $game,
            seed: $seed,
            status: 'active',
            stationCoordinates: $map->stationCoordinates,
        );
        $this->challenges->fillChallengePool(gameId: $gameId);

        return new Redirect(uri([self::class, 'show'], gameId: $gameId));
    }
}
