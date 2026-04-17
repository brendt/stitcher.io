<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\UserShopInitialized;
use App\Dungeon\Persistence\DungeonShopCard;
use App\Support\Authentication\User;
use function Tempest\Database\query;
use function Tempest\EventBus\event;

final readonly class ShopRepository
{
    public function __construct(
        private CardRepository $cardRepository,
    ) {}

    public function truncateForUser(User $user): void
    {
        query(DungeonShopCard::class)
            ->delete()
            ->where('userId', $user->id->value)
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->execute();
    }

    /** @return DungeonShopCard[] */
    public function forUser(User $user): array
    {
        $query = query(DungeonShopCard::class)
            ->select()
            ->where('userId', $user->id->value)
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN);

        $shopCards = $query->all();

        foreach ($shopCards as $shopCard) {
            $shopCard->card = $this->cardRepository->findByName($shopCard->cardName);
        }

        return $shopCards;
    }

    public function findForUser(User $user, int $id): ?DungeonShopCard
    {
        $shopCard = DungeonShopCard::select()
            ->where('id', $id)
            ->where('userId', $user->id->value)
            ->first();

        if ($shopCard) {
            $shopCard->card = $this->cardRepository->findByName($shopCard->cardName);
        }

        return $shopCard;
    }

    public function remove(DungeonShopCard $dungeonShopCard): void
    {
        $dungeonShopCard->delete();
    }
}