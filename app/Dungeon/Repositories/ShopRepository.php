<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Card;
use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonUserShop;
use App\Support\Authentication\User;
use function Tempest\Database\query;

final readonly class ShopRepository
{
    public function __construct(
        private CardRepository $cardRepository,
    ) {}

    /** @return DungeonUserShop[] */
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
                    cardName: $this->cardRepository->random()->name,
                );
            }
        }

        foreach ($shopItems as $item) {
            $item->card = $this->cardRepository->findByName($item->cardName);
        }

        return $shopItems;
    }
}