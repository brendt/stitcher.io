<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Cards\BreakthroughMinor;
use App\Dungeon\Cards\Clarity;
use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\KillDwellerMinor;
use App\Dungeon\Cards\RumbleMinor;
use App\Dungeon\Cards\UpperHandMinor;
use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonUserCard;
use App\Support\Authentication\User;
use function Tempest\Database\query;
use function Tempest\Support\arr;

final class DeckRepository
{
    public function __construct(
        private readonly CardRepository $cardRepository,
    ) {}

    /** @return \App\Dungeon\Card[] */
    public function forUser(User $user): array
    {
        $deckItems = query(DungeonUserCard::class)
            ->select()
            ->where('userId', $user->id->value)
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->where('isActive', true)
            ->all();

        if ($deckItems === []) {
            $cardsToAdd = [
                BreakthroughMinor::class,
                BreakthroughMinor::class,
                BreakthroughMinor::class,
                RumbleMinor::class,
                RumbleMinor::class,
                HealMinor::class,
                HealMinor::class,
                Clarity::class,
                KillDwellerMinor::class,
                UpperHandMinor::class,
            ];

            foreach ($cardsToAdd as $cardToAdd) {
                $card = new $cardToAdd();

                $deckItems[] = query(DungeonUserCard::class)->create(
                    userId: $user->id->value,
                    campaignId: Dungeon::CURRENT_CAMPAIGN,
                    isActive: true,
                    card: $card->name,
                );
            }
        }

        return arr($deckItems)
            ->map(fn (DungeonUserCard $item) => $this->cardRepository->findByName($item->card))
            ->filter()
            ->toArray();
    }
}