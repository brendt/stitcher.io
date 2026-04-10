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
use Tempest\Router\Post;
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

    #[Get('/game/new')]
    public function newGamePage(): View
    {
        return view(__DIR__ . '/game-new.view.php');
    }

    #[Post('/game/new')]
    public function createNewGame(Request $request): Redirect
    {
        $mode = (string) ($request->get('mode') ?? 'players');
        if ($mode === 'bot') {
            return $this->createSinglePlayerBotGame();
        }

        $requestedPlayers = (int) ($request->get('players') ?? 2);
        $playerCount = max(2, min(6, $requestedPlayers));

        return $this->createHumanLobbyGame($playerCount);
    }

    #[Get('/game/{gameId}/join')]
    public function join(string $gameId): Redirect
    {
        $game = $this->games->load($gameId);
        $meta = $this->games->loadMeta($gameId);

        if (($meta['status'] ?? 'pending') !== 'pending') {
            $firstHuman = $this->firstHumanPlayerId($game);
            if ($firstHuman !== null) {
                return new Redirect(uri([self::class, 'show'], gameId: $gameId) . '?playerId=' . $firstHuman);
            }

            return new Redirect(uri([self::class, 'show'], gameId: $gameId));
        }

        $slot = $this->nextOpenHumanSlot($game);
        if ($slot === null) {
            $firstHuman = $this->firstHumanPlayerId($game);
            if ($firstHuman !== null) {
                return new Redirect(uri([self::class, 'show'], gameId: $gameId) . '?playerId=' . $firstHuman);
            }

            return new Redirect(uri([self::class, 'show'], gameId: $gameId));
        }

        $stationId = $this->pickStartStationForPlayer($game, $slot['playerId']);
        if ($stationId !== null) {
            $this->games->updatePlayerState(
                gameId: $gameId,
                playerId: $slot['playerId'],
                coins: $slot['coins'],
                stationId: $stationId,
            );
            $this->games->updateStationClaim(
                gameId: $gameId,
                stationId: $stationId,
                ownerId: $slot['playerId'],
                topValue: 1,
            );
        }

        $updatedGame = $this->games->load($gameId);
        if ($this->nextOpenHumanSlot($updatedGame) === null) {
            $this->games->setStatus($gameId, 'active');
            $this->challenges->fillChallengePool(gameId: $gameId, capMultiplier: 1.5);
        }

        return new Redirect(uri([self::class, 'show'], gameId: $gameId) . '?playerId=' . $slot['playerId']);
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
        $game = $this->createGameWithAssignedStarts(
            gameId: $gameId,
            seed: $seed,
            playerCount: $playerCount,
            botCount: $singlePlayer ? ($playerCount - 1) : 0,
            assignOnlyPlayerOne: false,
            status: 'active',
        );
        $this->challenges->fillChallengePool(gameId: $gameId, capMultiplier: 1.5);

        $firstHuman = $this->firstHumanPlayerId($game);
        $target = uri([self::class, 'show'], gameId: $gameId);
        if ($firstHuman !== null) {
            $target .= '?playerId=' . $firstHuman;
        }

        return new Redirect($target);
    }

    private function createSinglePlayerBotGame(): Redirect
    {
        $gameId = 'game-' . random_int(100000, 999999);
        $seed = random_int(1, 2_147_483_647);
        $game = $this->createGameWithAssignedStarts(
            gameId: $gameId,
            seed: $seed,
            playerCount: 2,
            botCount: 1,
            assignOnlyPlayerOne: false,
            status: 'active',
        );
        $this->challenges->fillChallengePool(gameId: $gameId, capMultiplier: 1.5);

        $firstHuman = $this->firstHumanPlayerId($game) ?? 'p1';
        return new Redirect(uri([self::class, 'show'], gameId: $gameId) . '?playerId=' . $firstHuman);
    }

    private function createHumanLobbyGame(int $playerCount): Redirect
    {
        $gameId = 'game-' . random_int(100000, 999999);
        $seed = random_int(1, 2_147_483_647);

        $this->createGameWithAssignedStarts(
            gameId: $gameId,
            seed: $seed,
            playerCount: $playerCount,
            botCount: 0,
            assignOnlyPlayerOne: true,
            status: 'pending',
        );

        return new Redirect(uri([self::class, 'show'], gameId: $gameId) . '?playerId=p1');
    }

    private function createGameWithAssignedStarts(
        string $gameId,
        int $seed,
        int $playerCount,
        int $botCount,
        bool $assignOnlyPlayerOne,
        string $status,
    ): Game {
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
            $humanId = sprintf('p%d', $index);
            $isBot = $index > ($playerCount - $botCount);
            $playerId = $isBot ? sprintf('ai%d', $index - ($playerCount - $botCount)) : $humanId;
            $coins = 40;
            $stationId = null;
            $shouldAssignStart = ! $assignOnlyPlayerOne || $index === 1 || $isBot;

            if ($shouldAssignStart) {
                $lineId = sprintf('L%d', $index);
                $candidates = $stationsByLine[$lineId] ?? array_keys($stations);
                $availableCandidates = array_values(array_filter(
                    $candidates,
                    static fn (string $candidate): bool => ! isset($usedStartStations[$candidate]),
                ));

                if ($availableCandidates === []) {
                    $availableCandidates = array_values(array_filter(
                        array_keys($stations),
                        static fn (string $candidate): bool => ! isset($usedStartStations[$candidate]),
                    ));
                }

                if ($availableCandidates === []) {
                    throw new \RuntimeException('Could not assign unique start stations for all players.');
                }

                $stationId = $availableCandidates[random_int(0, count($availableCandidates) - 1)];
                $usedStartStations[$stationId] = true;
                $stations[$stationId] = $stations[$stationId]->withClaim(ownerId: $playerId, topValue: 1);
            }

            $players[$playerId] = new Player(id: $playerId, coins: $coins, stationId: $stationId);
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
            status: $status,
            stationCoordinates: $map->stationCoordinates,
        );

        return $game;
    }

    /**
     * @return array{playerId: string, coins: int}|null
     */
    private function nextOpenHumanSlot(Game $game): ?array
    {
        $humanPlayers = array_values(array_filter(
            $game->players,
            static fn (Player $player): bool => str_starts_with($player->id, 'p'),
        ));

        usort(
            $humanPlayers,
            static fn (Player $left, Player $right): int => $left->id <=> $right->id,
        );

        foreach ($humanPlayers as $player) {
            if ($player->stationId === null) {
                return ['playerId' => $player->id, 'coins' => $player->coins];
            }
        }

        return null;
    }

    private function firstHumanPlayerId(Game $game): ?string
    {
        foreach ($game->players as $player) {
            if (str_starts_with($player->id, 'p')) {
                return $player->id;
            }
        }

        return null;
    }

    private function pickStartStationForPlayer(Game $game, string $playerId): ?string
    {
        $coordinates = $this->games->stationCoordinates($game->id);
        $lineIndex = (int) preg_replace('/\D+/', '', $playerId);
        $lineId = $lineIndex > 0 ? sprintf('L%d', $lineIndex) : 'L1';

        $occupied = [];
        foreach ($game->stations as $station) {
            if ($station->ownerId !== null) {
                $occupied[$station->id] = true;
            }
        }

        $lineCandidates = [];
        foreach ($coordinates as $stationId => $coordinate) {
            if (($coordinate['line_id'] ?? null) !== $lineId) {
                continue;
            }

            if (! isset($occupied[$stationId])) {
                $lineCandidates[] = $stationId;
            }
        }

        if ($lineCandidates !== []) {
            return $lineCandidates[random_int(0, count($lineCandidates) - 1)];
        }

        $fallback = array_values(array_filter(
            array_keys($game->stations),
            static fn (string $stationId): bool => ! isset($occupied[$stationId]),
        ));

        if ($fallback === []) {
            return null;
        }

        return $fallback[random_int(0, count($fallback) - 1)];
    }
}
