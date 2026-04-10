<?php

declare(strict_types=1);

namespace App\Game\Persistence;

use App\Game\Domain\Edge;
use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Domain\Station;
use InvalidArgumentException;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use function Tempest\Database\query;

final class GameRepository
{
    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    public function save(Game $game, int $seed, string $status = 'pending', array $stationCoordinates = []): void
    {
        query('games')
            ->insert(
                id: $game->id,
                seed: $seed,
                status: $status,
            )
            ->execute();

        foreach ($game->players as $player) {
            query('game_players')
                ->insert(
                    game_id: $game->id,
                    player_id: $player->id,
                    coins: $player->coins,
                    station_id: $player->stationId,
                )
                ->execute();
        }

        foreach ($game->stations as $station) {
            $coordinate = $stationCoordinates[$station->id] ?? null;

            query('game_stations')
                ->insert(
                    game_id: $game->id,
                    station_id: $station->id,
                    is_hub: $station->isHub,
                    x: $coordinate['x'] ?? null,
                    y: $coordinate['y'] ?? null,
                    line_id: $coordinate['line_id'] ?? null,
                )
                ->execute();

            query('game_station_claims')
                ->insert(
                    game_id: $game->id,
                    station_id: $station->id,
                    owner_id: $station->ownerId,
                    top_value: $station->topValue,
                )
                ->execute();
        }

        foreach ($game->edges as $edge) {
            query('game_edges')
                ->insert(
                    game_id: $game->id,
                    from_station_id: $edge->fromStationId,
                    to_station_id: $edge->toStationId,
                    travel_time_seconds: $edge->travelTimeSeconds,
                    is_express: $edge->isExpress,
                )
                ->execute();
        }
    }

    public function load(string $gameId): Game
    {
        $game = query('games')
            ->select()
            ->where('id = ?', $gameId)
            ->first();

        if ($game === null) {
            throw new InvalidArgumentException(sprintf('Game %s not found.', $gameId));
        }

        $players = [];

        foreach (query('game_players')->select()->where('game_id = ?', $gameId)->all() as $row) {
            $player = new Player(
                id: $row['player_id'],
                coins: (int) $row['coins'],
                stationId: $row['station_id'],
            );

            $players[$player->id] = $player;
        }

        $stations = [];

        foreach (
            query('game_stations')
                ->select(
                    'game_stations.station_id',
                    'game_stations.is_hub',
                    'game_station_claims.owner_id',
                    'game_station_claims.top_value',
                )
                ->join('LEFT JOIN game_station_claims ON game_station_claims.game_id = game_stations.game_id AND game_station_claims.station_id = game_stations.station_id')
                ->where('game_stations.game_id = ?', $gameId)
                ->all() as $row
        ) {
            $station = new Station(
                id: $row['station_id'],
                ownerId: $row['owner_id'],
                topValue: (int) $row['top_value'],
                isHub: (bool) $row['is_hub'],
            );

            $stations[$station->id] = $station;
        }

        $edges = array_map(
            static fn (array $row): Edge => new Edge(
                fromStationId: $row['from_station_id'],
                toStationId: $row['to_station_id'],
                travelTimeSeconds: (int) $row['travel_time_seconds'],
                isExpress: (bool) $row['is_express'],
            ),
            query('game_edges')
                ->select()
                ->where('game_id = ?', $gameId)
                ->all(),
        );

        return new Game(
            id: $game['id'],
            players: $players,
            stations: $stations,
            edges: $edges,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function appendEvent(
        string $gameId,
        string $type,
        ?string $playerId,
        array $payload,
        ?string $effectiveAt = null,
        ?int $orderKey = null,
    ): int {
        $id = query('game_events')
            ->insert(
                game_id: $gameId,
                player_id: $playerId,
                type: $type,
                payload: json_encode($payload, JSON_THROW_ON_ERROR),
                effective_at: $effectiveAt,
                order_key: $orderKey,
            )
            ->execute()
            ?->value;

        if ($id !== null) {
            return (int) $id;
        }

        $fallback = query('game_events')
            ->select('id')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', $type)
            ->where('player_id = ?', $playerId)
            ->where('effective_at = ?', $effectiveAt)
            ->orderBy('id DESC')
            ->first()['id'] ?? null;

        return (int) $fallback;
    }

    /**
     * @return list<array{id: int, playerId: string, fromStationId: string, toStationId: string, deposit: ?int}>
     */
    public function moveRequestEvents(string $gameId, string $effectiveAt): array
    {
        $rows = query('game_events')
            ->select('id', 'payload')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'move_requested')
            ->where('effective_at = ?', $effectiveAt)
            ->all();

        return array_map(function (array $row): array {
            $payload = json_decode($row['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);

            return [
                'id' => (int) $row['id'],
                'playerId' => (string) $payload['playerId'],
                'fromStationId' => (string) $payload['fromStationId'],
                'toStationId' => (string) $payload['toStationId'],
                'deposit' => isset($payload['deposit']) ? (int) $payload['deposit'] : null,
            ];
        }, $rows);
    }

    /**
     * @return list<array{requestEventId: int, accepted: bool, reason: string, toStationId: string}>
     */
    public function moveResolutionEvents(string $gameId, string $effectiveAt): array
    {
        $rows = query('game_events')
            ->select('payload')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'move_resolved')
            ->where('effective_at = ?', $effectiveAt)
            ->all();

        return array_map(function (array $row): array {
            $payload = json_decode($row['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);

            return [
                'requestEventId' => (int) $payload['requestEventId'],
                'accepted' => (bool) $payload['accepted'],
                'reason' => (string) $payload['reason'],
                'toStationId' => (string) $payload['toStationId'],
            ];
        }, $rows);
    }

    /**
     * @return array{requestEventId: int, accepted: bool, reason: string, toStationId: string}|null
     */
    public function findMoveResolution(string $gameId, string $effectiveAt, int $requestEventId): ?array
    {
        foreach ($this->moveResolutionEvents(gameId: $gameId, effectiveAt: $effectiveAt) as $resolution) {
            if ($resolution['requestEventId'] === $requestEventId) {
                return $resolution;
            }
        }

        return null;
    }

    /**
     * @return list<array{effectiveAt: string, toStationId: string}>
     */
    public function pendingMoveArrivalSlots(string $gameId, string $upToEffectiveAt): array
    {
        $requests = query('game_events')
            ->select('id', 'effective_at', 'payload')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'move_requested')
            ->where('effective_at IS NOT NULL')
            ->where('effective_at <= ?', $upToEffectiveAt)
            ->orderBy('effective_at ASC')
            ->orderBy('id ASC')
            ->all();

        $resolutions = query('game_events')
            ->select('payload')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'move_resolved')
            ->all();

        $resolvedRequestIds = [];
        foreach ($resolutions as $row) {
            $payload = json_decode($row['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);
            if (isset($payload['requestEventId'])) {
                $resolvedRequestIds[(int) $payload['requestEventId']] = true;
            }
        }

        $slots = [];
        foreach ($requests as $row) {
            $requestId = (int) $row['id'];
            if (isset($resolvedRequestIds[$requestId])) {
                continue;
            }

            $payload = json_decode($row['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);
            $effectiveAt = (string) ($row['effective_at'] ?? '');
            $toStationId = (string) ($payload['toStationId'] ?? '');
            if ($effectiveAt === '' || $toStationId === '') {
                continue;
            }

            $slotKey = $effectiveAt . '|' . $toStationId;
            $slots[$slotKey] = [
                'effectiveAt' => $effectiveAt,
                'toStationId' => $toStationId,
            ];
        }

        return array_values($slots);
    }

    public function hasPendingMoveForPlayer(string $gameId, string $playerId): bool
    {
        $requests = query('game_events')
            ->select('id')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'move_requested')
            ->where('player_id = ?', $playerId)
            ->all();

        if ($requests === []) {
            return false;
        }

        $requestIds = array_map(static fn (array $row): int => (int) $row['id'], $requests);
        $resolutions = query('game_events')
            ->select('payload')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'move_resolved')
            ->all();

        $resolvedRequestIds = [];
        foreach ($resolutions as $row) {
            $payload = json_decode($row['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);
            if (isset($payload['requestEventId'])) {
                $resolvedRequestIds[(int) $payload['requestEventId']] = true;
            }
        }

        foreach ($requestIds as $requestId) {
            if (! isset($resolvedRequestIds[$requestId])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{toStationId: string, arrivalAt: string, travelTimeSeconds: int}|null
     */
    public function pendingMoveForPlayer(string $gameId, string $playerId): ?array
    {
        $requests = query('game_events')
            ->select('id', 'payload', 'effective_at')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'move_requested')
            ->where('player_id = ?', $playerId)
            ->orderBy('id ASC')
            ->all();

        if ($requests === []) {
            return null;
        }

        $resolutions = query('game_events')
            ->select('payload')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'move_resolved')
            ->where('player_id = ?', $playerId)
            ->all();

        $resolvedRequestIds = [];
        foreach ($resolutions as $row) {
            $payload = json_decode($row['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);
            if (isset($payload['requestEventId'])) {
                $resolvedRequestIds[(int) $payload['requestEventId']] = true;
            }
        }

        foreach ($requests as $request) {
            $requestId = (int) $request['id'];
            if (isset($resolvedRequestIds[$requestId])) {
                continue;
            }

            $payload = json_decode($request['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);
            $toStationId = (string) ($payload['toStationId'] ?? '');
            $arrivalAt = (string) ($request['effective_at'] ?? '');
            if ($toStationId === '' || $arrivalAt === '') {
                continue;
            }

            return [
                'toStationId' => $toStationId,
                'arrivalAt' => $arrivalAt,
                'travelTimeSeconds' => (int) ($payload['travelTimeSeconds'] ?? 0),
            ];
        }

        return null;
    }

    public function updatePlayerState(string $gameId, string $playerId, int $coins, string $stationId): void
    {
        query('game_players')
            ->update(
                coins: $coins,
                station_id: $stationId,
            )
            ->where('game_id = ?', $gameId)
            ->where('player_id = ?', $playerId)
            ->execute();
    }

    public function updateStationClaim(string $gameId, string $stationId, string $ownerId, int $topValue): void
    {
        query('game_station_claims')
            ->update(
                owner_id: $ownerId,
                top_value: $topValue,
                updated_at: DateTime::now()->format(FormatPattern::SQL_DATE_TIME),
            )
            ->where('game_id = ?', $gameId)
            ->where('station_id = ?', $stationId)
            ->execute();
    }

    /**
     * @return array{id: string, status: string, created_at: string}
     */
    public function loadMeta(string $gameId): array
    {
        $row = query('games')
            ->select('id', 'status', 'created_at')
            ->where('id = ?', $gameId)
            ->first();

        if ($row === null) {
            throw new InvalidArgumentException(sprintf('Game %s not found.', $gameId));
        }

        return [
            'id' => (string) $row['id'],
            'status' => (string) $row['status'],
            'created_at' => (string) $row['created_at'],
        ];
    }

    public function setStatus(string $gameId, string $status): void
    {
        query('games')
            ->update(status: $status)
            ->where('id = ?', $gameId)
            ->execute();
    }

    public function activeChallengeCount(string $gameId): int
    {
        return (int) (query('game_challenges')
            ->count()
            ->where('game_id = ?', $gameId)
            ->where('active = ?', true)
            ->where('(challenge_type IS NULL OR challenge_type = ?)', 'global')
            ->execute() ?? 0);
    }

    /**
     * @return list<array{id: int, station_id: string, reward: int, challenge_type: string, player_id: ?string}>
     */
    public function activeChallenges(string $gameId): array
    {
        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'station_id' => (string) $row['station_id'],
                'reward' => (int) $row['reward'],
                'challenge_type' => (string) ($row['challenge_type'] ?? 'global'),
                'player_id' => isset($row['player_id']) ? (string) $row['player_id'] : null,
            ],
            query('game_challenges')
                ->select('id', 'station_id', 'reward', 'challenge_type', 'player_id')
                ->where('game_id = ?', $gameId)
                ->where('active = ?', true)
                ->all(),
        );
    }

    /**
     * @return list<string>
     */
    public function activeChallengeStations(string $gameId): array
    {
        return array_map(
            static fn (array $row): string => (string) $row['station_id'],
            query('game_challenges')
                ->select('station_id')
                ->where('game_id = ?', $gameId)
                ->where('active = ?', true)
                ->all(),
        );
    }

    public function spawnChallenge(
        string $gameId,
        string $stationId,
        int $reward,
        string $challengeType = 'global',
        ?string $playerId = null,
        ?string $spawnedAt = null,
    ): int
    {
        $query = query('game_challenges');
        $id = $spawnedAt === null
            ? $query
                ->insert(
                    game_id: $gameId,
                    station_id: $stationId,
                    active: true,
                    reward: $reward,
                    challenge_type: $challengeType,
                    player_id: $playerId,
                )
                ->execute()
                ?->value
            : $query
                ->insert(
                    game_id: $gameId,
                    station_id: $stationId,
                    active: true,
                    reward: $reward,
                    challenge_type: $challengeType,
                    player_id: $playerId,
                    spawned_at: $spawnedAt,
                )
                ->execute()
                ?->value;

        return (int) $id;
    }

    /**
     * @return array{id: int, station_id: string, reward: int, challenge_type: string, player_id: ?string}|null
     */
    public function activeChallengeAtStation(string $gameId, string $stationId, ?string $playerId = null): ?array
    {
        $rows = query('game_challenges')
            ->select('id', 'station_id', 'reward', 'challenge_type', 'player_id')
            ->where('game_id = ?', $gameId)
            ->where('station_id = ?', $stationId)
            ->where('active = ?', true)
            ->all();

        if ($rows === []) {
            return null;
        }

        $row = null;
        foreach ($rows as $candidate) {
            $type = (string) ($candidate['challenge_type'] ?? 'global');
            $owner = isset($candidate['player_id']) ? (string) $candidate['player_id'] : null;
            if ($type === 'player' && $playerId !== null && $owner === $playerId) {
                $row = $candidate;
                break;
            }
        }

        if ($row === null) {
            foreach ($rows as $candidate) {
                $type = (string) ($candidate['challenge_type'] ?? 'global');
                if ($type === 'global') {
                    $row = $candidate;
                    break;
                }
            }
        }

        if ($row === null) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'station_id' => (string) $row['station_id'],
            'reward' => (int) $row['reward'],
            'challenge_type' => (string) ($row['challenge_type'] ?? 'global'),
            'player_id' => isset($row['player_id']) ? (string) $row['player_id'] : null,
        ];
    }

    public function hasActivePlayerSpecificChallenge(string $gameId, string $playerId): bool
    {
        return (int) (query('game_challenges')
            ->count()
            ->where('game_id = ?', $gameId)
            ->where('active = ?', true)
            ->where('challenge_type = ?', 'player')
            ->where('player_id = ?', $playerId)
            ->execute() ?? 0) > 0;
    }

    /**
     * @return array{id: int, station_id: string, spawned_at: string}|null
     */
    public function activePlayerSpecificChallenge(string $gameId, string $playerId): ?array
    {
        $row = query('game_challenges')
            ->select('id', 'station_id', 'spawned_at')
            ->where('game_id = ?', $gameId)
            ->where('active = ?', true)
            ->where('challenge_type = ?', 'player')
            ->where('player_id = ?', $playerId)
            ->orderBy('id DESC')
            ->first();

        if ($row === null) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'station_id' => (string) $row['station_id'],
            'spawned_at' => (string) $row['spawned_at'],
        ];
    }

    public function latestPlayerSpecificChallengeSpawnedAt(string $gameId, string $playerId): ?string
    {
        $row = query('game_challenges')
            ->select('spawned_at')
            ->where('game_id = ?', $gameId)
            ->where('challenge_type = ?', 'player')
            ->where('player_id = ?', $playerId)
            ->orderBy('id DESC')
            ->first();

        return $row !== null ? (string) $row['spawned_at'] : null;
    }

    public function completeChallenge(int $challengeId): void
    {
        query('game_challenges')
            ->update(
                active: false,
                completed_at: DateTime::now()->format(FormatPattern::SQL_DATE_TIME),
            )
            ->where('id = ?', $challengeId)
            ->execute();
    }

    public function deactivateChallenge(int $challengeId): void
    {
        query('game_challenges')
            ->update(active: false)
            ->where('id = ?', $challengeId)
            ->execute();
    }

    /**
     * @return list<array{id: int, station_id: string, reward: int, active: bool, challenge_type: string, player_id: ?string}>
     */
    public function allChallenges(string $gameId, ?string $viewerPlayerId = null): array
    {
        $rows = query('game_challenges')
            ->select('id', 'station_id', 'reward', 'active', 'challenge_type', 'player_id')
            ->where('game_id = ?', $gameId)
            ->all();

        $filteredRows = array_values(array_filter(
            $rows,
            static function (array $row) use ($viewerPlayerId): bool {
                $type = (string) ($row['challenge_type'] ?? 'global');
                if ($type !== 'player') {
                    return true;
                }

                if ($viewerPlayerId === null) {
                    return false;
                }

                return isset($row['player_id']) && (string) $row['player_id'] === $viewerPlayerId;
            },
        ));

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'station_id' => (string) $row['station_id'],
                'reward' => (int) $row['reward'],
                'active' => (bool) $row['active'],
                'challenge_type' => (string) ($row['challenge_type'] ?? 'global'),
                'player_id' => isset($row['player_id']) ? (string) $row['player_id'] : null,
            ],
            $filteredRows,
        );
    }

    /**
     * @return array<string, array{x: ?int, y: ?int, line_id: ?string}>
     */
    public function stationCoordinates(string $gameId): array
    {
        $rows = query('game_stations')
            ->select('station_id', 'x', 'y', 'line_id')
            ->where('game_id = ?', $gameId)
            ->all();

        $coordinates = [];

        foreach ($rows as $row) {
            $coordinates[(string) $row['station_id']] = [
                'x' => isset($row['x']) ? (int) $row['x'] : null,
                'y' => isset($row['y']) ? (int) $row['y'] : null,
                'line_id' => isset($row['line_id']) ? (string) $row['line_id'] : null,
            ];
        }

        return $coordinates;
    }

    /**
     * @return array{
     *   winnerPlayerId: ?string,
     *   isTie: bool,
     *   tiedPlayerIds: list<string>,
     *   scores: array<string, array{stations: int, hubs: int, score: int}>
     * }|null
     */
    public function latestMatchFinalization(string $gameId): ?array
    {
        $row = query('game_events')
            ->select('payload')
            ->where('game_id = ?', $gameId)
            ->where('type = ?', 'match_finalized')
            ->orderBy('id DESC')
            ->first();

        if ($row === null) {
            return null;
        }

        $payload = json_decode($row['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR);

        return [
            'winnerPlayerId' => $payload['winnerPlayerId'] ?? null,
            'isTie' => (bool) ($payload['isTie'] ?? false),
            'tiedPlayerIds' => array_values($payload['tiedPlayerIds'] ?? []),
            'scores' => $payload['scores'] ?? [],
        ];
    }

    /**
     * @return list<array{id: int, type: string, player_id: ?string, payload: array<string, mixed>, effective_at: ?string, created_at: string}>
     */
    public function latestEvents(string $gameId, int $limit = 30): array
    {
        $rows = query('game_events')
            ->select('id', 'type', 'player_id', 'payload', 'effective_at', 'created_at')
            ->where('game_id = ?', $gameId)
            ->orderBy('id DESC')
            ->limit($limit)
            ->all();

        $events = array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'type' => (string) $row['type'],
                'player_id' => $row['player_id'],
                'payload' => json_decode($row['payload'] ?? '{}', true, flags: JSON_THROW_ON_ERROR),
                'effective_at' => $row['effective_at'],
                'created_at' => (string) $row['created_at'],
            ];
        }, $rows);

        return array_values(array_reverse($events));
    }
}
