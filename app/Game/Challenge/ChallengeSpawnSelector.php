<?php

declare(strict_types=1);

namespace App\Game\Challenge;

use App\Game\Domain\Game;
use Random\Randomizer;

final class ChallengeSpawnSelector
{
    /**
     * @param list<string> $excludedStationIds
     */
    public function pickStation(Game $game, array $excludedStationIds, Randomizer $randomizer): ?string
    {
        $excluded = array_fill_keys($excludedStationIds, true);

        $ownedBy = [];

        foreach ($game->stations as $station) {
            if ($station->ownerId !== null) {
                $ownedBy[$station->ownerId] = ($ownedBy[$station->ownerId] ?? 0) + 1;
            }
        }

        if ($ownedBy === []) {
            $leaderId = null;
            $trailingId = null;
        } else {
            arsort($ownedBy);
            $leaderId = array_key_first($ownedBy);

            asort($ownedBy);
            $trailingId = array_key_first($ownedBy);
        }

        $weighted = [];

        foreach ($game->stations as $station) {
            if (isset($excluded[$station->id])) {
                continue;
            }

            $weight = match (true) {
                $station->ownerId === null => 3,
                $trailingId !== null && $station->ownerId === $trailingId => 4,
                $leaderId !== null && $station->ownerId === $leaderId => 1,
                default => 2,
            };

            for ($i = 0; $i < $weight; $i++) {
                $weighted[] = $station->id;
            }
        }

        if ($weighted === []) {
            return null;
        }

        return $weighted[$randomizer->getInt(0, count($weighted) - 1)];
    }
}
