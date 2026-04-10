<?php

declare(strict_types=1);

namespace App\Game\Ai;

use App\Game\Challenge\ChallengeCommandRequest;
use App\Game\Challenge\ChallengeCommandResolver;
use App\Game\Domain\Game;
use App\Game\Move\MoveCommandRequest;
use App\Game\Move\MoveCommandResolver;
use App\Game\Persistence\GameRepository;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\Http\Method;

final readonly class SimpleBotResolver
{
    private const OVERCLAIM_CAP = 5;
    private const MIN_THINK_SECONDS = 1;
    private const MAX_THINK_SECONDS = 4;

    public function __construct(
        private GameRepository $games,
        private MoveCommandResolver $moves,
        private ChallengeCommandResolver $challenges,
    ) {}

    public function playTurn(string $gameId, Game $game, ?string $effectiveAt = null): void
    {
        $effectiveAt ??= DateTime::now()->format(FormatPattern::SQL_DATE_TIME);
        $seed = abs(crc32($gameId . '|bot|' . $effectiveAt));
        $randomizer = new Randomizer(new Mt19937($seed));

        foreach ($game->players as $player) {
            try {
                $this->playForPlayer($gameId, $game, $player, $effectiveAt, $randomizer);
            } catch (\Throwable) {
                continue;
            }
        }
    }

    private function playForPlayer(
        string $gameId,
        Game $game,
        \App\Game\Domain\Player $player,
        string $effectiveAt,
        Randomizer $randomizer,
    ): void {
            if (! $this->isBotPlayerId($player->id)) {
                return;
            }

            if ($player->stationId === null) {
                return;
            }

            if ($this->games->hasPendingMoveForPlayer($gameId, $player->id)) {
                return;
            }

            if ($this->shouldWaitForThink($gameId, $player->id, $effectiveAt, $randomizer)) {
                return;
            }

            if ($this->tryClaimChallenge($gameId, $player->id, $player->stationId, $effectiveAt)) {
                $this->scheduleNextThink($gameId, $player->id, $effectiveAt, $randomizer);
                return;
            }

            $neighbors = $this->neighborsFor($game, $player->stationId);
            if ($neighbors === []) {
                $this->scheduleNextThink($gameId, $player->id, $effectiveAt, $randomizer);
                return;
            }
            $challengeTargets = $this->targetChallengeStations($gameId, $player->id);
            $preferredStep = $challengeTargets === []
                ? null
                : $this->nextStepTowardNearestChallenge($game, $player->stationId, $challengeTargets);

            $scored = [];
            foreach ($neighbors as $stationId) {
                if (! isset($game->stations[$stationId])) {
                    continue;
                }

                $target = $game->station($stationId);
                [$depositMin, $depositMax] = $this->depositBounds(
                    playerCoins: $player->coins,
                    ownerId: $target->ownerId,
                    topValue: $target->topValue,
                    playerId: $player->id,
                );

                if ($depositMax < $depositMin) {
                    continue;
                }

                $deposit = $target->ownerId === $player->id ? 0 : $depositMin;
                $score = 0;
                if ($target->ownerId === $player->id) {
                    $score = 10;
                } elseif ($target->ownerId === null) {
                    $score = 24;
                } else {
                    $score = 36 + $target->topValue;
                }
                if ($preferredStep !== null && $stationId === $preferredStep) {
                    $score += 60;
                }

                $challenge = $this->games->activeChallengeAtStation($gameId, $stationId, $player->id);
                if ($challenge !== null) {
                    $score += (int) ceil(((int) $challenge['reward']) / 5);
                }

                $score += $randomizer->getInt(0, 4);
                $scored[] = [
                    'stationId' => $stationId,
                    'deposit' => $deposit,
                    'score' => $score,
                ];
            }

            if ($scored === []) {
                return;
            }

            usort(
                $scored,
                static fn (array $left, array $right): int => $right['score'] <=> $left['score'],
            );
            $moved = false;
            foreach ($scored as $picked) {
                if ($this->attemptMove(
                    gameId: $gameId,
                    playerId: $player->id,
                    fromStationId: $player->stationId,
                    toStationId: $picked['stationId'],
                    deposit: (int) $picked['deposit'],
                    effectiveAt: $effectiveAt,
                )) {
                    $moved = true;
                    break;
                }
            }

            if ($moved) {
                $this->scheduleNextThink($gameId, $player->id, $effectiveAt, $randomizer);
                return;
            }

            // Fallback: try every neighbor with minimal legal deposits.
            foreach ($neighbors as $stationId) {
                if (! isset($game->stations[$stationId])) {
                    continue;
                }

                $target = $game->station($stationId);
                [$depositMin, $depositMax] = $this->depositBounds(
                    playerCoins: $player->coins,
                    ownerId: $target->ownerId,
                    topValue: $target->topValue,
                    playerId: $player->id,
                );
                if ($depositMax < $depositMin) {
                    continue;
                }

                $attemptDeposits = array_values(array_unique([$depositMin]));
                foreach ($attemptDeposits as $deposit) {
                    if ($this->attemptMove(
                        gameId: $gameId,
                        playerId: $player->id,
                        fromStationId: $player->stationId,
                        toStationId: $stationId,
                        deposit: (int) $deposit,
                        effectiveAt: $effectiveAt,
                    )) {
                        $this->scheduleNextThink($gameId, $player->id, $effectiveAt, $randomizer);
                        return;
                    }
                }
            }

            $this->scheduleNextThink($gameId, $player->id, $effectiveAt, $randomizer);
    }

    private function attemptMove(
        string $gameId,
        string $playerId,
        string $fromStationId,
        string $toStationId,
        int $deposit,
        string $effectiveAt,
    ): bool {
            $request = new MoveCommandRequest(
                method: Method::POST,
                uri: '/internal/bot/move',
            );
            $request->playerId = $playerId;
            $request->fromStationId = $fromStationId;
            $request->toStationId = $toStationId;
            $request->deposit = $deposit;
            $request->effectiveAt = $effectiveAt;
        $result = $this->moves->handle($gameId, $request);
        if (($result['accepted'] ?? false) !== true) {
            return false;
        }

        return true;
    }

    private function tryClaimChallenge(string $gameId, string $playerId, string $stationId, string $effectiveAt): bool
    {
        $request = new ChallengeCommandRequest(
            method: Method::POST,
            uri: '/internal/bot/challenge',
        );
        $request->playerId = $playerId;
        $request->stationId = $stationId;
        $request->effectiveAt = $effectiveAt;
        $result = $this->challenges->handle($gameId, $request);

        return (bool) ($result['accepted'] ?? false);
    }

    /**
     * @return array{int, int}
     */
    private function depositBounds(int $playerCoins, ?string $ownerId, int $topValue, string $playerId): array
    {
        if ($ownerId === $playerId) {
            return [0, 0];
        }

        if ($ownerId === null) {
            return [1, min(self::OVERCLAIM_CAP, $playerCoins)];
        }

        $min = $topValue + 1;
        $max = min($topValue + self::OVERCLAIM_CAP, $playerCoins);
        return [$min, $max];
    }

    /**
     * @return list<string>
     */
    private function neighborsFor(Game $game, string $stationId): array
    {
        $neighbors = [];
        foreach ($game->edges as $edge) {
            if ($edge->fromStationId === $stationId) {
                $neighbors[$edge->toStationId] = true;
            } elseif ($edge->toStationId === $stationId) {
                $neighbors[$edge->fromStationId] = true;
            }
        }

        return array_keys($neighbors);
    }

    private function isBotPlayerId(string $playerId): bool
    {
        return str_starts_with($playerId, 'ai');
    }

    private function shouldWaitForThink(
        string $gameId,
        string $playerId,
        string $effectiveAt,
        Randomizer $randomizer,
    ): bool {
        $nowTimestamp = strtotime($effectiveAt);
        if ($nowTimestamp === false) {
            return false;
        }

        $thinkUntil = $this->games->latestBotThinkUntil($gameId, $playerId);
        if ($thinkUntil === null) {
            $this->scheduleNextThink($gameId, $playerId, $effectiveAt, $randomizer);
            return true;
        }

        $untilTimestamp = strtotime($thinkUntil);
        if ($untilTimestamp === false) {
            return false;
        }

        return $nowTimestamp < $untilTimestamp;
    }

    private function scheduleNextThink(string $gameId, string $playerId, string $effectiveAt, Randomizer $randomizer): void
    {
        $base = DateTime::parse($effectiveAt);
        $delay = $randomizer->getInt(self::MIN_THINK_SECONDS, self::MAX_THINK_SECONDS);
        $until = $base->plusSeconds($delay)->format(FormatPattern::SQL_DATE_TIME);

        $this->games->appendEvent(
            gameId: $gameId,
            type: 'bot_think_until',
            playerId: $playerId,
            payload: ['delaySeconds' => $delay],
            effectiveAt: $until,
        );
    }

    /**
     * @return list<string>
     */
    private function targetChallengeStations(string $gameId, string $playerId): array
    {
        $active = $this->games->activeChallenges($gameId);
        $targets = [];

        foreach ($active as $challenge) {
            $type = (string) ($challenge['challenge_type'] ?? 'global');
            if ($type === 'player' && ($challenge['player_id'] ?? null) !== $playerId) {
                continue;
            }

            $targets[] = (string) $challenge['station_id'];
        }

        return array_values(array_unique($targets));
    }

    /**
     * @param list<string> $targetStationIds
     */
    private function nextStepTowardNearestChallenge(Game $game, string $originStationId, array $targetStationIds): ?string
    {
        $targetSet = array_fill_keys($targetStationIds, true);
        if (isset($targetSet[$originStationId])) {
            return null;
        }

        $queue = [$originStationId];
        $visited = [$originStationId => true];
        $parent = [];
        $cursor = 0;

        while ($cursor < count($queue)) {
            $current = $queue[$cursor++];
            foreach ($this->neighborsFor($game, $current) as $neighbor) {
                if (isset($visited[$neighbor]) || ! isset($game->stations[$neighbor])) {
                    continue;
                }

                $visited[$neighbor] = true;
                $parent[$neighbor] = $current;
                if (isset($targetSet[$neighbor])) {
                    $step = $neighbor;
                    while (($parent[$step] ?? null) !== $originStationId && isset($parent[$step])) {
                        $step = $parent[$step];
                    }

                    return $step;
                }

                $queue[] = $neighbor;
            }
        }

        return null;
    }
}
