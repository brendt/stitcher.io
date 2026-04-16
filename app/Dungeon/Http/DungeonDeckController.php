<?php

namespace App\Dungeon\Http;

use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\ShopRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Support\Authentication\User;
use Tempest\Router\Get;
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
        $shopCards = $this->shopRepository->forUser($user);
        $deck = $this->deckRepository->forUser($user);
        ld($deck);

        return view('dungeon-deck.view.php');
    }
}