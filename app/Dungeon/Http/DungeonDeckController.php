<?php

namespace App\Dungeon\Http;

use App\Dungeon\Persistence\DungeonUserCard;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\ShopRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Support\Authentication\User;
use Tempest\Http\Request;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;
use function Tempest\View\view;

#[DungeonAuth]
final readonly class DungeonDeckController
{
    public function __construct(
        private StatsRepository $statsRepository,
        private ShopRepository $shopRepository,
        private DeckRepository $deckRepository,
    ) {}

    #[Get('/dungeon')]
    public function index(User $user): View
    {
        $stats = $this->statsRepository->forUser($user);
        $shop = $this->shopRepository->forUser($user);
        $deck = $this->deckRepository->forUser($user);

        return view('dungeon-deck.view.php', stats: $stats, shop: $shop, deck: $deck);
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

    private function renderDeckBuilder(User $user): View
    {
        $deck = $this->deckRepository->forUser($user);

        return view('x-deck-builder.view.php', deck: $deck);
    }
}