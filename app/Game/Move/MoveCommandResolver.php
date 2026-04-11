<?php

declare(strict_types=1);

namespace App\Game\Move;

use App\Game\Domain\ClaimRule;
use App\Game\Domain\Game;
use App\Game\Persistence\GameRepository;
use DomainException;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;

final readonly class MoveCommandResolver
{
    private const FIRST_DOUBLE_COIN_SPAWN_STEPS_PER_PLAYER = 20;
    private const NEXT_DOUBLE_COIN_SPAWN_STEPS_PER_PLAYER = 30;
    private const FIRST_STEAL_SPAWN_STEPS_PER_PLAYER = 30;
    private const NEXT_STEAL_SPAWN_STEPS_PER_PLAYER = 40;
    private const FIRST_SPEED_BOOST_SPAWN_STEPS_PER_PLAYER = 30;
    private const NEXT_SPEED_BOOST_SPAWN_STEPS_PER_PLAYER = 40;
    private const SPEED_BOOST_MULTIPLIER = 2.0;
    private const SPEED_BOOST_MOVE_DURATION = 20;

    public function __construct(
        private GameRepository $games,
        private ClaimRule $claimRule = new ClaimRule(cap: 5),
    ) {}

    /**
     * @return array{accepted: bool, reason: string, requestEventId: int, arrivalAt?: string, travelTimeSeconds?: int}
     */
    public function handle(string $gameId, MoveCommandRequest $request): array
    {
        $meta = $this->games->loadMeta($gameId);
        if (($meta['status'] ?? 'pending') !== 'active') {
            return [
                'accepted' => false,
                'reason' => 'game_not_active',
                'requestEventId' => 0,
            ];
        }

        $departureAt = $request->effectiveAt
            ?? DateTime::now()->format(FormatPattern::SQL_DATE_TIME);
        $game = $this->games->load($gameId);

        if ($this->games->hasPendingMoveForPlayer($gameId, $request->playerId)) {
            return [
                'accepted' => false,
                'reason' => 'already_in_transit',
                'requestEventId' => 0,
            ];
        }

        $travelTimeSeconds = $this->travelTimeBetween(
            game: $game,
            fromStationId: $request->fromStationId,
            toStationId: $request->toStationId,
        );

        if ($travelTimeSeconds === null) {
            return [
                'accepted' => false,
                'reason' => 'not_adjacent',
                'requestEventId' => 0,
            ];
        }

        if ($this->games->hasActiveSpeedBoostForPlayer($gameId, $request->playerId)) {
            $travelTimeSeconds = max(1, (int) ceil($travelTimeSeconds / self::SPEED_BOOST_MULTIPLIER));
        }

        $validation = $this->validateMove(
            game: $game,
            request: [
                'id' => 0,
                'playerId' => $request->playerId,
                'fromStationId' => $request->fromStationId,
                'toStationId' => $request->toStationId,
                'deposit' => $request->deposit,
            ],
        );

        if ($validation['accepted'] === false) {
            return [
                'accepted' => false,
                'reason' => $validation['reason'],
                'requestEventId' => 0,
            ];
        }

        $arrivalAt = DateTime::parse($departureAt)->plusSeconds($travelTimeSeconds)->format(FormatPattern::SQL_DATE_TIME);

        $requestEventId = $this->games->appendEvent(
            gameId: $gameId,
            type: 'move_requested',
            playerId: $request->playerId,
            payload: [
                'playerId' => $request->playerId,
                'fromStationId' => $request->fromStationId,
                'toStationId' => $request->toStationId,
                'deposit' => $request->deposit,
                'departureAt' => $departureAt,
                'travelTimeSeconds' => $travelTimeSeconds,
            ],
            effectiveAt: $arrivalAt,
        );

        $this->processDueMoves($gameId);

        $resolution = $this->games->findMoveResolution(
            gameId: $gameId,
            effectiveAt: $arrivalAt,
            requestEventId: $requestEventId,
        );

        if ($resolution !== null) {
            return [
                'accepted' => (bool) ($resolution['accepted'] ?? false),
                'reason' => (string) ($resolution['reason'] ?? 'pending'),
                'requestEventId' => $requestEventId,
                'arrivalAt' => $arrivalAt,
                'travelTimeSeconds' => $travelTimeSeconds,
            ];
        }

        return [
            'accepted' => true,
            'reason' => 'in_transit',
            'requestEventId' => $requestEventId,
            'arrivalAt' => $arrivalAt,
            'travelTimeSeconds' => $travelTimeSeconds,
        ];
    }

    public function processDueMoves(string $gameId, ?string $upToEffectiveAt = null): void
    {
        $upToEffectiveAt ??= DateTime::now()->format(FormatPattern::SQL_DATE_TIME);
        $slots = $this->games->pendingMoveArrivalSlots(
            gameId: $gameId,
            upToEffectiveAt: $upToEffectiveAt,
        );

        foreach ($slots as $slot) {
            $this->resolveTargetConflicts(
                gameId: $gameId,
                effectiveAt: $slot['effectiveAt'],
                toStationId: $slot['toStationId'],
            );
        }
    }

    /**
     * Demo-only debug helper to spawn bonuses near a specific player.
     *
     * @return array{accepted: bool, reason: string, spawned2x: bool, spawnedSteal: bool, spawnedSpeed: bool}
     */
    public function debugSpawnBonusesNearPlayer(string $gameId, string $playerId): array
    {
        if (!str_starts_with($gameId, 'demo-')) {
            return [
                'accepted' => false,
                'reason' => 'debug_only_for_demo',
                'spawned2x' => false,
                'spawnedSteal' => false,
                'spawnedSpeed' => false,
            ];
        }

        $game = $this->games->load($gameId);
        $player = $game->player($playerId);
        if ($player->stationId === null) {
            return [
                'accepted' => false,
                'reason' => 'player_has_no_station',
                'spawned2x' => false,
                'spawnedSteal' => false,
                'spawnedSpeed' => false,
            ];
        }

        $effectiveAt = DateTime::now()->format(FormatPattern::SQL_DATE_TIME);
        $blockedStations = array_fill_keys($this->games->activeBonusStations($gameId), true);
        foreach ($game->players as $candidate) {
            if ($candidate->stationId !== null) {
                $blockedStations[$candidate->stationId] = true;
            }
        }

        $spawned2x = false;
        $spawnedSteal = false;
        $spawnedSpeed = false;

        $doubleStation = $this->pickNearestStationToPlayer(
            game: $game,
            originStationId: $player->stationId,
            blockedStations: array_keys($blockedStations),
        );

        if ($doubleStation !== null) {
            $this->games->appendEvent(
                gameId: $gameId,
                type: 'double_coin_spawned',
                playerId: null,
                payload: [
                    'stationId' => $doubleStation,
                    'type' => '2x',
                ],
                effectiveAt: $effectiveAt,
            );
            $blockedStations[$doubleStation] = true;
            $spawned2x = true;
        }

        $stealStation = $this->pickNearestStationToPlayer(
            game: $game,
            originStationId: $player->stationId,
            blockedStations: array_keys($blockedStations),
        );

        if ($stealStation !== null) {
            $this->games->appendEvent(
                gameId: $gameId,
                type: 'steal_spawned',
                playerId: null,
                payload: [
                    'stationId' => $stealStation,
                    'type' => 'steal',
                ],
                effectiveAt: $effectiveAt,
            );
            $spawnedSteal = true;
            $blockedStations[$stealStation] = true;
        }

        $speedStation = $this->pickNearestStationToPlayer(
            game: $game,
            originStationId: $player->stationId,
            blockedStations: array_keys($blockedStations),
        );

        if ($speedStation !== null) {
            $this->games->appendEvent(
                gameId: $gameId,
                type: 'speed_boost_spawned',
                playerId: null,
                payload: [
                    'stationId' => $speedStation,
                    'type' => 'speed',
                ],
                effectiveAt: $effectiveAt,
            );
            $spawnedSpeed = true;
        }

        return [
            'accepted' => $spawned2x || $spawnedSteal || $spawnedSpeed,
            'reason' => ($spawned2x || $spawnedSteal || $spawnedSpeed) ? 'accepted' : 'no_available_station',
            'spawned2x' => $spawned2x,
            'spawnedSteal' => $spawnedSteal,
            'spawnedSpeed' => $spawnedSpeed,
        ];
    }

    private function resolveTargetConflicts(string $gameId, string $effectiveAt, string $toStationId): void
    {
        $requests = $this->games->moveRequestEvents(gameId: $gameId, effectiveAt: $effectiveAt);
        $resolutions = $this->games->moveResolutionEvents(gameId: $gameId, effectiveAt: $effectiveAt);

        $resolvedRequestIds = [];
        $acceptedTargetAlreadyReserved = false;

        foreach ($resolutions as $resolution) {
            $resolvedRequestIds[(int) $resolution['requestEventId']] = true;

            if (($resolution['accepted'] ?? false) && ($resolution['toStationId'] ?? null) === $toStationId) {
                $acceptedTargetAlreadyReserved = true;
            }
        }

        $pendingTargetRequests = array_values(array_filter(
            $requests,
            static fn (array $request): bool => ! isset($resolvedRequestIds[$request['id']])
                && ($request['toStationId'] ?? null) === $toStationId,
        ));

        if ($pendingTargetRequests === []) {
            return;
        }

        usort($pendingTargetRequests, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);

        if ($acceptedTargetAlreadyReserved) {
            foreach ($pendingTargetRequests as $request) {
                $this->resolveRejected(
                    gameId: $gameId,
                    requestId: $request['id'],
                    effectiveAt: $effectiveAt,
                    reason: 'station_conflict',
                    requestPayload: $request,
                );
            }

            return;
        }

        $accepted = false;

        foreach ($pendingTargetRequests as $request) {
            if ($accepted) {
                $this->resolveRejected(
                    gameId: $gameId,
                    requestId: $request['id'],
                    effectiveAt: $effectiveAt,
                    reason: 'station_conflict',
                    requestPayload: $request,
                );

                continue;
            }

            $validation = $this->validateMove(
                game: $this->games->load($gameId),
                request: $request,
            );

            if ($validation['accepted'] === false) {
                $this->resolveRejected(
                    gameId: $gameId,
                    requestId: $request['id'],
                    effectiveAt: $effectiveAt,
                    reason: $validation['reason'],
                    requestPayload: $request,
                );

                continue;
            }

            $player = $validation['player'];
            $station = $validation['station'];
            $deposit = $validation['deposit'];

            $this->games->updatePlayerState(
                gameId: $gameId,
                playerId: $player->id,
                coins: $player->coins,
                stationId: $station->id,
            );

            if ($deposit > 0) {
                $this->games->updateStationClaim(
                    gameId: $gameId,
                    stationId: $station->id,
                    ownerId: $station->ownerId,
                    topValue: $station->topValue,
                );
            }

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'move_resolved',
                playerId: $player->id,
                payload: [
                    'requestEventId' => $request['id'],
                    'accepted' => true,
                    'reason' => 'accepted',
                    'toStationId' => $request['toStationId'],
                ],
                effectiveAt: $effectiveAt,
                orderKey: $request['id'],
            );

            $this->applyStepBonuses(
                gameId: $gameId,
                playerId: $player->id,
                stationId: $station->id,
                effectiveAt: $effectiveAt,
            );

            $accepted = true;
        }
    }

    /**
     * @param array{id: int, playerId: string, fromStationId: string, toStationId: string, deposit: ?int} $request
     * @return array{accepted: bool, reason: string, player?: \App\Game\Domain\Player, station?: \App\Game\Domain\Station, deposit?: int}
     */
    private function validateMove(Game $game, array $request): array
    {
        $player = $game->player($request['playerId']);

        if ($player->stationId !== $request['fromStationId']) {
            return ['accepted' => false, 'reason' => 'invalid_origin'];
        }

        $isAdjacent = false;

        foreach ($game->edges as $edge) {
            $matchesForward = $edge->fromStationId === $request['fromStationId'] && $edge->toStationId === $request['toStationId'];
            $matchesReverse = $edge->toStationId === $request['fromStationId'] && $edge->fromStationId === $request['toStationId'];

            if ($matchesForward || $matchesReverse) {
                $isAdjacent = true;
                break;
            }
        }

        if (! $isAdjacent) {
            return ['accepted' => false, 'reason' => 'not_adjacent'];
        }

        $target = $game->station($request['toStationId']);

        if ($target->isOwnedBy($player->id)) {
            return [
                'accepted' => true,
                'reason' => 'accepted',
                'player' => $player,
                'station' => $target,
                'deposit' => 0,
            ];
        }

        $deposit = $request['deposit'];

        if ($deposit === null) {
            return ['accepted' => false, 'reason' => 'missing_deposit'];
        }

        try {
            $claimedStation = $this->claimRule->apply(
                station: $target,
                playerId: $player->id,
                deposit: $deposit,
            );
        } catch (DomainException) {
            return ['accepted' => false, 'reason' => 'invalid_deposit'];
        }

        if (! $player->canAfford($deposit)) {
            return ['accepted' => false, 'reason' => 'insufficient_coins'];
        }

        return [
            'accepted' => true,
            'reason' => 'accepted',
            'player' => $player->spend($deposit),
            'station' => $claimedStation,
            'deposit' => $deposit,
        ];
    }

    /**
     * @param array{playerId: string, toStationId: string} $requestPayload
     */
    private function resolveRejected(
        string $gameId,
        int $requestId,
        string $effectiveAt,
        string $reason,
        array $requestPayload,
    ): void {
        $this->games->appendEvent(
            gameId: $gameId,
            type: 'move_resolved',
            playerId: $requestPayload['playerId'],
            payload: [
                'requestEventId' => $requestId,
                'accepted' => false,
                'reason' => $reason,
                'toStationId' => $requestPayload['toStationId'],
            ],
            effectiveAt: $effectiveAt,
            orderKey: $requestId,
        );
    }

    private function travelTimeBetween(Game $game, string $fromStationId, string $toStationId): ?int
    {
        foreach ($game->edges as $edge) {
            $matchesForward = $edge->fromStationId === $fromStationId && $edge->toStationId === $toStationId;
            $matchesReverse = $edge->toStationId === $fromStationId && $edge->fromStationId === $toStationId;

            if ($matchesForward || $matchesReverse) {
                return $edge->travelTimeSeconds;
            }
        }

        return null;
    }

    private function applyStepBonuses(
        string $gameId,
        string $playerId,
        string $stationId,
        string $effectiveAt,
    ): void {
        if ($this->games->hasActiveDoubleCoinAtStation($gameId, $stationId)) {
            $game = $this->games->load($gameId);
            $player = $game->player($playerId);
            $doubledCoins = $player->coins * 2;

            $this->games->updatePlayerState(
                gameId: $gameId,
                playerId: $playerId,
                coins: $doubledCoins,
                stationId: $stationId,
            );

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'double_coin_collected',
                playerId: $playerId,
                payload: [
                    'stationId' => $stationId,
                    'multiplier' => 2,
                    'coinsAfter' => $doubledCoins,
                ],
                effectiveAt: $effectiveAt,
            );
        }

        if ($this->games->hasActiveStealAtStation($gameId, $stationId)) {
            $game = $this->games->load($gameId);
            $collector = $game->player($playerId);
            [$victimId, $stolenCoins] = $this->resolveStealTarget($game, $collector->id);

            $collectorCoinsAfterSteal = $collector->coins + $stolenCoins;
            $this->games->updatePlayerState(
                gameId: $gameId,
                playerId: $collector->id,
                coins: $collectorCoinsAfterSteal,
                stationId: $stationId,
            );

            if ($victimId !== null) {
                $victim = $game->player($victimId);
                if ($victim->stationId !== null) {
                    $this->games->updatePlayerState(
                        gameId: $gameId,
                        playerId: $victim->id,
                        coins: max(0, $victim->coins - $stolenCoins),
                        stationId: $victim->stationId,
                    );
                }
            }

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'steal_collected',
                playerId: $playerId,
                payload: [
                    'stationId' => $stationId,
                    'victimPlayerId' => $victimId,
                    'stolenCoins' => $stolenCoins,
                    'coinsAfter' => $collectorCoinsAfterSteal,
                ],
                effectiveAt: $effectiveAt,
            );
        }

        if ($this->games->hasActiveSpeedBoostAtStation($gameId, $stationId)) {
            $this->games->appendEvent(
                gameId: $gameId,
                type: 'speed_boost_collected',
                playerId: $playerId,
                payload: [
                    'stationId' => $stationId,
                    'multiplier' => self::SPEED_BOOST_MULTIPLIER,
                    'moves' => self::SPEED_BOOST_MOVE_DURATION,
                ],
                effectiveAt: $effectiveAt,
            );
        }

        $this->spawnDueDoubleCoinBonuses(
            gameId: $gameId,
            effectiveAt: $effectiveAt,
        );
        $this->spawnDueStealBonuses(
            gameId: $gameId,
            effectiveAt: $effectiveAt,
        );
        $this->spawnDueSpeedBoostBonuses(
            gameId: $gameId,
            effectiveAt: $effectiveAt,
        );
    }

    private function spawnDueDoubleCoinBonuses(string $gameId, string $effectiveAt): void
    {
        $game = $this->games->load($gameId);
        $playerCount = count($game->players);
        if ($playerCount === 0) {
            return;
        }

        $stepsTaken = $this->games->acceptedMoveCount($gameId);
        $spawnedCount = $this->games->doubleCoinSpawnCount($gameId);

        $firstThreshold = self::FIRST_DOUBLE_COIN_SPAWN_STEPS_PER_PLAYER * $playerCount;
        $nextThresholdSize = self::NEXT_DOUBLE_COIN_SPAWN_STEPS_PER_PLAYER * $playerCount;
        $nextThreshold = $firstThreshold + ($spawnedCount * $nextThresholdSize);

        while ($stepsTaken >= $nextThreshold) {
            $stationId = $this->pickFarthestStationFromPlayers($gameId, $game);
            if ($stationId === null) {
                return;
            }

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'double_coin_spawned',
                playerId: null,
                payload: [
                    'stationId' => $stationId,
                    'type' => '2x',
                ],
                effectiveAt: $effectiveAt,
            );

            $spawnedCount++;
            $nextThreshold = $firstThreshold + ($spawnedCount * $nextThresholdSize);
        }
    }

    private function spawnDueStealBonuses(string $gameId, string $effectiveAt): void
    {
        $game = $this->games->load($gameId);
        $playerCount = count($game->players);
        if ($playerCount < 2) {
            return;
        }

        $movesTaken = $this->games->acceptedMoveCount($gameId);
        $spawnedCount = $this->games->stealSpawnCount($gameId);

        $firstThreshold = self::FIRST_STEAL_SPAWN_STEPS_PER_PLAYER * $playerCount;
        $nextThresholdSize = self::NEXT_STEAL_SPAWN_STEPS_PER_PLAYER * $playerCount;
        $nextThreshold = $firstThreshold + ($spawnedCount * $nextThresholdSize);

        while ($movesTaken >= $nextThreshold) {
            $stationId = $this->pickFarthestStationFromPlayers($gameId, $game);
            if ($stationId === null) {
                return;
            }

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'steal_spawned',
                playerId: null,
                payload: [
                    'stationId' => $stationId,
                    'type' => 'steal',
                ],
                effectiveAt: $effectiveAt,
            );

            $spawnedCount++;
            $nextThreshold = $firstThreshold + ($spawnedCount * $nextThresholdSize);
        }
    }

    private function spawnDueSpeedBoostBonuses(string $gameId, string $effectiveAt): void
    {
        $game = $this->games->load($gameId);
        $playerCount = count($game->players);
        if ($playerCount < 2) {
            return;
        }

        $movesTaken = $this->games->acceptedMoveCount($gameId);
        $spawnedCount = $this->games->speedBoostSpawnCount($gameId);

        $firstThreshold = self::FIRST_SPEED_BOOST_SPAWN_STEPS_PER_PLAYER * $playerCount;
        $nextThresholdSize = self::NEXT_SPEED_BOOST_SPAWN_STEPS_PER_PLAYER * $playerCount;
        $nextThreshold = $firstThreshold + ($spawnedCount * $nextThresholdSize);

        while ($movesTaken >= $nextThreshold) {
            $stationId = $this->pickFarthestStationFromPlayers($gameId, $game);
            if ($stationId === null) {
                return;
            }

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'speed_boost_spawned',
                playerId: null,
                payload: [
                    'stationId' => $stationId,
                    'type' => 'speed',
                ],
                effectiveAt: $effectiveAt,
            );

            $spawnedCount++;
            $nextThreshold = $firstThreshold + ($spawnedCount * $nextThresholdSize);
        }
    }

    /**
     * @return array{0: ?string, 1: int}
     */
    private function resolveStealTarget(Game $game, string $collectorPlayerId): array
    {
        $bestVictim = null;

        foreach ($game->players as $candidate) {
            if ($candidate->id === $collectorPlayerId) {
                continue;
            }

            if ($candidate->coins <= 0) {
                continue;
            }

            if ($bestVictim === null) {
                $bestVictim = $candidate;
                continue;
            }

            if ($candidate->coins > $bestVictim->coins) {
                $bestVictim = $candidate;
                continue;
            }

            if ($candidate->coins === $bestVictim->coins && strcmp($candidate->id, $bestVictim->id) < 0) {
                $bestVictim = $candidate;
            }
        }

        if ($bestVictim === null) {
            return [null, 0];
        }

        $stolenCoins = max(1, (int) floor($bestVictim->coins * 0.25));
        return [$bestVictim->id, min($bestVictim->coins, $stolenCoins)];
    }

    private function pickFarthestStationFromPlayers(string $gameId, Game $game): ?string
    {
        $activeBonusStations = array_fill_keys($this->games->activeBonusStations($gameId), true);
        $occupiedStations = [];

        foreach ($game->players as $player) {
            if ($player->stationId !== null) {
                $occupiedStations[$player->stationId] = true;
            }
        }

        $adjacency = [];
        foreach ($game->edges as $edge) {
            $adjacency[$edge->fromStationId] ??= [];
            $adjacency[$edge->toStationId] ??= [];
            $adjacency[$edge->fromStationId][] = $edge->toStationId;
            $adjacency[$edge->toStationId][] = $edge->fromStationId;
        }

        $distances = [];
        $queue = [];
        $cursor = 0;
        foreach ($game->players as $player) {
            if ($player->stationId === null || isset($distances[$player->stationId])) {
                continue;
            }

            $distances[$player->stationId] = 0;
            $queue[] = $player->stationId;
        }

        while ($cursor < count($queue)) {
            $stationId = $queue[$cursor++];
            $currentDistance = $distances[$stationId];

            foreach ($adjacency[$stationId] ?? [] as $neighbor) {
                if (isset($distances[$neighbor])) {
                    continue;
                }

                $distances[$neighbor] = $currentDistance + 1;
                $queue[] = $neighbor;
            }
        }

        $bestStationId = null;
        $bestDistance = -1;

        foreach ($game->stations as $station) {
            if (isset($activeBonusStations[$station->id])) {
                continue;
            }

            if (isset($occupiedStations[$station->id])) {
                continue;
            }

            $distance = $distances[$station->id] ?? PHP_INT_MAX;
            if ($distance > $bestDistance) {
                $bestDistance = $distance;
                $bestStationId = $station->id;
                continue;
            }

            if ($distance === $bestDistance && $bestStationId !== null && strcmp($station->id, $bestStationId) < 0) {
                $bestStationId = $station->id;
            }
        }

        return $bestStationId;
    }

    /**
     * @param list<string> $blockedStations
     */
    private function pickNearestStationToPlayer(Game $game, string $originStationId, array $blockedStations): ?string
    {
        $blocked = array_fill_keys($blockedStations, true);

        $adjacency = [];
        foreach ($game->edges as $edge) {
            $adjacency[$edge->fromStationId] ??= [];
            $adjacency[$edge->toStationId] ??= [];
            $adjacency[$edge->fromStationId][] = $edge->toStationId;
            $adjacency[$edge->toStationId][] = $edge->fromStationId;
        }

        $visited = [$originStationId => true];
        $queue = [$originStationId];
        $cursor = 0;

        while ($cursor < count($queue)) {
            $stationId = $queue[$cursor++];

            if ($stationId !== $originStationId && !isset($blocked[$stationId])) {
                return $stationId;
            }

            $neighbors = $adjacency[$stationId] ?? [];
            sort($neighbors);
            foreach ($neighbors as $neighbor) {
                if (isset($visited[$neighbor])) {
                    continue;
                }

                $visited[$neighbor] = true;
                $queue[] = $neighbor;
            }
        }

        return null;
    }
}
