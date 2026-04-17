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

final readonly class DeckRepository
{
    public function __construct(
        private CardRepository $cardRepository,
    ) {}

    /** @return \App\Dungeon\Persistence\DungeonUserCard[] */
    public function forUser(User $user): array
    {
        $deckItems = query(DungeonUserCard::class)
            ->select()
            ->where('userId', $user->id->value)
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
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
                    cardName: $card->name,
                );
            }
        }

        foreach ($deckItems as $item) {
            $item->card = $this->cardRepository->findByName($item->cardName);
        }

        return $deckItems;
    }

    public function activeCardsForUser(User $user): array
    {
        $deckItems = query(DungeonUserCard::class)
            ->select()
            ->where('userId', $user->id->value)
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->where('isActive', true)
            ->all();

        foreach ($deckItems as $item) {
            $item->card = $this->cardRepository->findByName($item->cardName);
        }

        return $deckItems;
    }

    public function markActive(DungeonUserCard $card): void
    {
        $card->isActive = true;
        $card->save();
    }

    public function markInactive(DungeonUserCard $card): void
    {
        $card->isActive = false;
        $card->save();
    }
}