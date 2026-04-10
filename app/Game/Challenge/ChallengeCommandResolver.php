<?php

declare(strict_types=1);

namespace App\Game\Challenge;

use App\Game\Persistence\GameRepository;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;

final readonly class ChallengeCommandResolver
{
    private const PLAYER_SPECIFIC_CHALLENGE_INTERVAL_SECONDS = 300;
    private const PLAYER_SPECIFIC_MIN_STATION_DISTANCE = 20;

    public function __construct(
        private GameRepository $games,
        private ChallengeSpawnSelector $selector = new ChallengeSpawnSelector(),
    ) {}

    /**
     * @return array{accepted: bool, reason: string, reward: int}
     */
    public function handle(string $gameId, ChallengeCommandRequest $request): array
    {
        $meta = $this->games->loadMeta($gameId);
        if (($meta['status'] ?? 'pending') !== 'active') {
            return ['accepted' => false, 'reason' => 'game_not_active', 'reward' => 0];
        }

        $game = $this->games->load($gameId);
        $player = $game->player($request->playerId);

        if ($player->stationId !== $request->stationId) {
            return ['accepted' => false, 'reason' => 'not_at_station', 'reward' => 0];
        }

        $challenge = $this->games->activeChallengeAtStation(
            gameId: $gameId,
            stationId: $request->stationId,
            playerId: $player->id,
        );

        if ($challenge === null) {
            return ['accepted' => false, 'reason' => 'no_active_challenge', 'reward' => 0];
        }

        $this->games->completeChallenge($challenge['id']);

        $updatedPlayer = $player->earn($challenge['reward']);
        $this->games->updatePlayerState(
            gameId: $gameId,
            playerId: $updatedPlayer->id,
            coins: $updatedPlayer->coins,
            stationId: $updatedPlayer->stationId ?? $request->stationId,
        );

        $effectiveAt = $request->effectiveAt
            ?? DateTime::now()->format(FormatPattern::SQL_DATE_TIME);

        $this->games->appendEvent(
            gameId: $gameId,
            type: 'challenge_completed',
            playerId: $player->id,
            payload: [
                'stationId' => $request->stationId,
                'reward' => $challenge['reward'],
            ],
            effectiveAt: $effectiveAt,
        );

        $this->fillChallengePool($gameId, $game, $effectiveAt);
        $this->fillPlayerSpecificChallengePool($gameId, $game, $effectiveAt);

        return ['accepted' => true, 'reason' => 'accepted', 'reward' => $challenge['reward']];
    }

    public function fillChallengePool(
        string $gameId,
        ?\App\Game\Domain\Game $game = null,
        ?string $effectiveAt = null,
        float $capMultiplier = 3.0,
    ): void
    {
        $game ??= $this->games->load($gameId);
        $effectiveAt ??= DateTime::now()->format(FormatPattern::SQL_DATE_TIME);

        $cap = max(1, (int) ceil(count($game->players) * $capMultiplier));
        $activeCount = $this->games->activeChallengeCount($gameId);

        if ($activeCount >= $cap) {
            return;
        }

        $seed = $this->seedFrom($gameId, $effectiveAt);
        $randomizer = new Randomizer(new Mt19937($seed));

        while ($activeCount < $cap) {
            $excluded = $this->games->activeChallengeStations($gameId);
            $stationId = $this->selector->pickStation($game, $excluded, $randomizer);

            if ($stationId === null) {
                return;
            }

            $reward = $randomizer->getInt(10, 25);

            $this->games->spawnChallenge(
                gameId: $gameId,
                stationId: $stationId,
                reward: $reward,
                challengeType: 'global',
                spawnedAt: $effectiveAt,
            );

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'challenge_spawned',
                playerId: null,
                payload: [
                    'stationId' => $stationId,
                    'reward' => $reward,
                ],
                effectiveAt: $effectiveAt,
            );

            $activeCount++;
        }
    }

    public function fillPlayerSpecificChallengePool(
        string $gameId,
        ?\App\Game\Domain\Game $game = null,
        ?string $effectiveAt = null,
    ): void {
        $game ??= $this->games->load($gameId);
        $effectiveAt ??= DateTime::now()->format(FormatPattern::SQL_DATE_TIME);
        $seed = $this->seedFrom($gameId, $effectiveAt . '|player');
        $randomizer = new Randomizer(new Mt19937($seed));

        foreach ($game->players as $player) {
            if ($player->stationId === null) {
                continue;
            }

            $activeChallenge = $this->games->activePlayerSpecificChallenge($gameId, $player->id);
            if ($activeChallenge !== null) {
                $activeAge = $this->elapsedSeconds(
                    from: $activeChallenge['spawned_at'],
                    to: $effectiveAt,
                );
                if ($activeAge !== null && $activeAge < self::PLAYER_SPECIFIC_CHALLENGE_INTERVAL_SECONDS) {
                    continue;
                }

                // Rotate stale, unclaimed player-specific challenges at interval boundaries.
                $this->games->deactivateChallenge($activeChallenge['id']);
            }

            $lastSpawnedAt = $this->games->latestPlayerSpecificChallengeSpawnedAt($gameId, $player->id);
            if ($lastSpawnedAt !== null) {
                $elapsed = $this->elapsedSeconds(
                    from: $lastSpawnedAt,
                    to: $effectiveAt,
                );
                if ($elapsed !== null && $elapsed < self::PLAYER_SPECIFIC_CHALLENGE_INTERVAL_SECONDS) {
                    continue;
                }
            }

            $candidateStationIds = $this->playerSpecificCandidateStations($game, $player->id);
            if ($candidateStationIds === []) {
                continue;
            }

            $stationId = $candidateStationIds[$randomizer->getInt(0, count($candidateStationIds) - 1)];
            $reward = $randomizer->getInt(30, 55);

            $this->games->spawnChallenge(
                gameId: $gameId,
                stationId: $stationId,
                reward: $reward,
                challengeType: 'player',
                playerId: $player->id,
                spawnedAt: $effectiveAt,
            );

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'challenge_spawned',
                playerId: $player->id,
                payload: [
                    'stationId' => $stationId,
                    'reward' => $reward,
                    'challengeType' => 'player',
                    'targetPlayerId' => $player->id,
                ],
                effectiveAt: $effectiveAt,
            );
        }
    }

    /**
     * @return list<string>
     */
    private function playerSpecificCandidateStations(\App\Game\Domain\Game $game, string $playerId): array
    {
        $player = $game->player($playerId);
        if ($player->stationId === null) {
            return [];
        }

        $excluded = array_fill_keys($this->games->activeChallengeStations($game->id), true);
        $distances = $this->stationDistancesFrom($game, $player->stationId, includeExpress: false);
        $candidates = [];

        foreach ($distances as $stationId => $distance) {
            if ($distance < self::PLAYER_SPECIFIC_MIN_STATION_DISTANCE) {
                continue;
            }

            if (isset($excluded[$stationId])) {
                continue;
            }

            $candidates[] = $stationId;
        }

        return $candidates;
    }

    /**
     * @return array<string, int>
     */
    private function stationDistancesFrom(
        \App\Game\Domain\Game $game,
        string $originStationId,
        bool $includeExpress = true,
    ): array
    {
        $adjacency = [];
        foreach ($game->edges as $edge) {
            if (! $includeExpress && $edge->isExpress) {
                continue;
            }

            $adjacency[$edge->fromStationId] ??= [];
            $adjacency[$edge->toStationId] ??= [];
            $adjacency[$edge->fromStationId][] = $edge->toStationId;
            $adjacency[$edge->toStationId][] = $edge->fromStationId;
        }

        $distances = [$originStationId => 0];
        $queue = [$originStationId];
        $cursor = 0;

        while ($cursor < count($queue)) {
            $current = $queue[$cursor++];
            $currentDistance = $distances[$current];

            foreach ($adjacency[$current] ?? [] as $neighbor) {
                if (isset($distances[$neighbor])) {
                    continue;
                }

                $distances[$neighbor] = $currentDistance + 1;
                $queue[] = $neighbor;
            }
        }

        return $distances;
    }

    private function seedFrom(string $gameId, string $effectiveAt): int
    {
        return abs(crc32($gameId . '|' . $effectiveAt));
    }

    private function elapsedSeconds(string $from, string $to): ?int
    {
        $fromTimestamp = strtotime($from);
        $toTimestamp = strtotime($to);
        if ($fromTimestamp === false || $toTimestamp === false) {
            return null;
        }

        $elapsed = $toTimestamp - $fromTimestamp;
        if ($elapsed < 0) {
            // Defensive fallback for timestamp source/timezone skew.
            return self::PLAYER_SPECIFIC_CHALLENGE_INTERVAL_SECONDS + 1;
        }

        return $elapsed;
    }
}
