<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Cards\BreakthroughMinor;
use App\Dungeon\Cards\Clarity;
use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\KillDwellerMinor;
use App\Dungeon\Cards\RumbleMinor;
use App\Dungeon\Cards\StabilityMinor;
use App\Dungeon\Cards\Token;
use App\Dungeon\Cards\UpperHandMinor;
use App\Dungeon\Cards\VictoryPoint;
use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonShopCard;
use App\Dungeon\Persistence\DungeonUserCard;
use App\Support\Authentication\User;
use function Tempest\Database\query;

final readonly class DeckRepository
{
    public function __construct(
        private CardRepository $cardRepository,
        private StatsRepository $statsRepository,
    ) {}

    /** @return \App\Dungeon\Persistence\DungeonUserCard[] */
    public function forUser(User $user): array
    {
        $deckCards = query(DungeonUserCard::class)
            ->select()
            ->where('userId', $user->id->value)
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->all();

        if ($deckCards === []) {
            $cardsToAdd = [
                BreakthroughMinor::class,
                BreakthroughMinor::class,
                BreakthroughMinor::class,
                RumbleMinor::class,
                RumbleMinor::class,
                HealMinor::class,
                HealMinor::class,
                KillDwellerMinor::class,
                UpperHandMinor::class,
                StabilityMinor::class,
            ];

            foreach ($cardsToAdd as $cardToAdd) {
                $card = new $cardToAdd();

                $deckCards[] = query(DungeonUserCard::class)->create(
                    userId: $user->id->value,
                    campaignId: Dungeon::CURRENT_CAMPAIGN,
                    isActive: true,
                    cardName: $card->name,
                );
            }
        }

        foreach ($deckCards as $deckCard) {
            $deckCard->card = $this->cardRepository->findByName($deckCard->cardName);
        }

        return $deckCards;
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

    public function addFromShop(DungeonShopCard $shopCard): void
    {
        $user = User::findById($shopCard->userId);

        if ($shopCard->card instanceof Token) {
            $this->statsRepository->increaseStats($user, tokens : 1);
        } elseif ($shopCard->card instanceof VictoryPoint) {
            $this->statsRepository->increaseStats($user, victoryPoints: 1);
        } else {
            $activeCards = $this->activeCardsForUser($user);

            DungeonUserCard::create(
                userId: $shopCard->userId,
                campaignId: Dungeon::CURRENT_CAMPAIGN,
                isActive: count($activeCards) < Dungeon::MAX_HAND_COUNT,
                cardName: $shopCard->card->name,
            );
        }
    }
}