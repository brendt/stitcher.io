<?php

namespace App\Dungeon\Http;

use App\Dungeon\Persistence\DungeonUserCard;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\DungeonRepository;
use App\Dungeon\Repositories\ShopRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Support\Authentication\User;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;
use function Tempest\View\view;

#[DungeonAuth]
final readonly class DungeonHomeController
{
    public function __construct(
        private StatsRepository $statsRepository,
        private ShopRepository $shopRepository,
        private DeckRepository $deckRepository,
        private DungeonRepository $dungeonRepository,
    ) {}

    #[Get('/dungeon')]
    public function index(User $user): View
    {
        $stats = $this->statsRepository->forUser($user);
        $shop = $this->shopRepository->forUser($user);
        $deck = $this->deckRepository->forUser($user);
        $dungeon = $this->dungeonRepository->forUser($user);

        return view(
            'dungeon-home.view.php',
            stats: $stats,
            shop: $shop,
            deck: $deck,
            dungeon: $dungeon,
        );
    }

    #[Post('/dungeon/deck/{id}/activate')]
    public function activateCard(int $id, User $user): View
    {
        $dungeonUserCard = DungeonUserCard::select()
            ->where('id', $id)
            ->where('userId', $user->id->value)
            ->where('isActive', false)
            ->first();

        if (! $dungeonUserCard) {
            return $this->renderDeckBuilder($user);
        }

        if (count($this->deckRepository->activeCardsForUser($user)) >= 20) {
            return $this->renderDeckBuilder($user);
        }

        $this->deckRepository->markActive($dungeonUserCard);

        return $this->renderDeckBuilder($user);
    }

    #[Post('/dungeon/deck/{id}/deactivate')]
    public function deactivateCard(int $id, User $user): View
    {
        $dungeonUserCard = DungeonUserCard::select()
            ->where('id', $id)
            ->where('userId', $user->id->value)
            ->where('isActive', true)
            ->first();

        if (! $dungeonUserCard) {
            return $this->renderDeckBuilder($user);
        }

        $this->deckRepository->markInactive($dungeonUserCard);

        return $this->renderDeckBuilder($user);
    }

    #[Post('/dungeon/deck/{id}/buy')]
    public function buyCard(int $id, User $user): View
    {
        $shopCard = $this->shopRepository->findForUser($user, $id);

        if (! $shopCard) {
            return $this->renderDeckBuilder($user);
        }

        $stats = $this->statsRepository->forUser($user);

        if (! $stats->canBuy($shopCard)) {
            return $this->renderDeckBuilder($user);
        }

        $this->shopRepository->remove($shopCard);
        $this->deckRepository->addFromShop($shopCard);
        $this->statsRepository->decreaseCoins($user, $shopCard->price);

        return $this->renderDeckBuilder($user);
    }

    private function renderDeckBuilder(User $user): View
    {
        return view(
            'x-deck-builder.view.php',
            deck: $this->deckRepository->forUser($user),
            shop: $this->shopRepository->forUser($user),
            stats: $this->statsRepository->forUser($user),
            dungeon: $this->dungeonRepository->forUser($user),
        );
    }
}