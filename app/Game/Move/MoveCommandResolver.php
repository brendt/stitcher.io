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
}
