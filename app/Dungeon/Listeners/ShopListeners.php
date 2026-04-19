<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Card;
use App\Dungeon\Cards\LocateShard;
use App\Dungeon\Cards\LocateVictoryPoint;
use App\Dungeon\Cards\Token;
use App\Dungeon\Cards\VictoryPoint;
use App\Dungeon\Dungeon;
use App\Dungeon\Events\PlayerExited;
use App\Dungeon\Events\UserShopInitialized;
use App\Dungeon\Persistence\DungeonShopCard;
use App\Dungeon\Repositories\CardRepository;
use App\Dungeon\Repositories\ShopRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Dungeon\Support\Random;
use App\Support\Authentication\User;
use Tempest\EventBus\EventHandler;
use function Tempest\Support\arr;

final readonly class ShopListeners
{
    public function __construct(
        private CardRepository $cardRepository,
        private ShopRepository $shopRepository,
        private StatsRepository $statsRepository,
        private Random $random,
    ) {}

    #[EventHandler]
    public function onUserShopInitialized(UserShopInitialized $event): void
    {
        $this->generateShop($event->user);
    }

    #[EventHandler]
    public function onPlayerExitedEvent(PlayerExited $event): void
    {
        $this->generateShop($event->user);
    }

    private function generateShop(User $user): void
    {
        $this->shopRepository->truncateForUser($user);
        $stats = $this->statsRepository->forUser($user);

        // Filter out permanent and other cards
        $availableCards = arr($this->cardRepository->getCards())
            ->filter(fn (Card $card) => ! $card->type->isPermanent())
            ->filter(fn (Card $card) => ! $card->type->isMeta())
            ->filter(fn (Card $card) => ! $card instanceof LocateShard && ! $card instanceof LocateVictoryPoint);

        // Split into buyable and unbuyable cards
        $buyableCards = $availableCards->filter(fn (Card $card) => $stats->level->hasAccessTo($card->level));
        $unbuyableCards = $availableCards->filter(fn (Card $card) => ! $stats->level->hasAccessTo($card->level));

        $cardsForShop = arr();

        // Add some fixed cards by chance
        if ($this->random->chance(1/3)) {
            $cardsForShop[] = $unbuyableCards->random();
        }

        if ($this->random->chance(1/3)) {
            $cardsForShop[] = $unbuyableCards->random();
        }

        if ($this->random->chance(1/3)) {
            $cardsForShop[] = new Token();
        }

        if ($this->random->chance(1/10)) {
            $cardsForShop[] = new VictoryPoint();
        }

        // Fill the rest with buyable cards
        while (count($cardsForShop) < 5) {
            /** @var Card $randomCard */
            $randomCard = $buyableCards->random();

            if ($this->random->chance($randomCard->rarity->getChance() / 100)) {
                $cardsForShop[] = $randomCard;
            }
        }

        $cardsForShop = $cardsForShop->shuffle();

        foreach ($cardsForShop as $card) {
            $priceVariation = (int) round($card->price * 0.1);

            $basePrice = $card->price + random_int(-1 * $priceVariation, $priceVariation);

            $modifiedPrice = (int) round($basePrice * $stats->level->priceModifier());

            DungeonShopCard::create(
                userId: $user->id->value,
                campaignId: Dungeon::CURRENT_CAMPAIGN,
                cardName: $card->name,
                price: $modifiedPrice,
            );
        }
    }
}