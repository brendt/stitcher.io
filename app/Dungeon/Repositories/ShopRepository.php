<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Card;
use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonUserShop;
use App\Support\Authentication\User;
use function Tempest\Database\query;
use function Tempest\Support\arr;

final readonly class ShopRepository
{
    public function __construct(
        private CardRepository $cardRepository,
    ) {}

    /** @return Card[] */
    public function forUser(User $user): array
    {
        $shopItems = query(DungeonUserShop::class)
            ->select()
            ->where('userId', $user->id->value)
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->all();

        if ($shopItems === []) {
            for ($i = 0; $i < 5; $i++) {
                // TODO: improve
                $shopItems[] = query(DungeonUserShop::class)->create(
                    userId: $user->id->value,
                    campaignId: Dungeon::CURRENT_CAMPAIGN,
                    card: $this->cardRepository->random()->name,
                );
            }
        }

        return arr($shopItems)
            ->map(fn (DungeonUserShop $item) => $this->cardRepository->findByName($item->card))
            ->filter()
            ->toArray();
    }
}