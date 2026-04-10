<?php

declare(strict_types=1);

namespace App\Game\Ui;

use App\Game\Challenge\ChallengeCommandResolver;
use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Map\MapGenerator;
use App\Game\Persistence\GameRepository;
use Tempest\Http\Request;
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
    public function demo(Request $request): Redirect
    {
        $gameId = 'demo-' . random_int(100000, 999999);
        $seed = random_int(1, 2_147_483_647);
        $mode = strtolower((string) ($request->get('mode') ?? ''));
        $aiFlag = (string) ($request->get('ai') ?? '');
        $singlePlayer = in_array($mode, ['single', 'singleplayer', 'ai'], true) || in_array(strtolower($aiFlag), ['1', 'true', 'yes'], true);
        $requestedPlayers = $request->get('players');
        $playerCount = $requestedPlayers !== null ? (int) $requestedPlayers : ($singlePlayer ? 2 : 2);
        $playerCount = max(2, min(6, $playerCount));
        $stationCount = max(100, $playerCount * 30);
        $map = $this->maps->generate(stationCount: $stationCount, seed: $seed, playerCount: $playerCount);

        $stations = $map->stations;
        $stationsByLine = [];
        foreach ($map->stationCoordinates as $stationId => $coordinate) {
            $lineId = (string) ($coordinate['line_id'] ?? 'L1');
            $stationsByLine[$lineId] ??= [];
            $stationsByLine[$lineId][] = $stationId;
        }

        $players = [];
        $usedStartStations = [];

        for ($index = 1; $index <= $playerCount; $index++) {
            $playerId = ($singlePlayer && $index > 1)
                ? sprintf('ai%d', $index - 1)
                : sprintf('p%d', $index);
            $lineId = sprintf('L%d', $index);
            $candidates = $stationsByLine[$lineId] ?? array_keys($stations);
            $availableCandidates = array_values(array_filter(
                $candidates,
                static fn (string $stationId): bool => ! isset($usedStartStations[$stationId]),
            ));

            if ($availableCandidates === []) {
                $availableCandidates = array_values(array_filter(
                    array_keys($stations),
                    static fn (string $stationId): bool => ! isset($usedStartStations[$stationId]),
                ));
            }

            if ($availableCandidates === []) {
                throw new \RuntimeException('Could not assign unique start stations for all players.');
            }

            $startStation = $availableCandidates[random_int(0, count($availableCandidates) - 1)];
            $usedStartStations[$startStation] = true;
            $stations[$startStation] = $stations[$startStation]->withClaim(ownerId: $playerId, topValue: 1);
            $players[$playerId] = new Player(id: $playerId, coins: 40, stationId: $startStation);
        }

        $game = new Game(
            id: $gameId,
            players: $players,
            stations: $stations,
            edges: $map->edges,
        );

        $this->games->save(
            game: $game,
            seed: $seed,
            status: 'active',
            stationCoordinates: $map->stationCoordinates,
        );
        $this->challenges->fillChallengePool(gameId: $gameId, capMultiplier: 1.5);

        return new Redirect(uri([self::class, 'show'], gameId: $gameId));
    }
}
