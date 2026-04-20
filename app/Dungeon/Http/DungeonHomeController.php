<?php

namespace App\Dungeon\Http;

use App\Dungeon\DeckValidator;
use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonUserCard;
use App\Dungeon\Repositories\CardRepository;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\DungeonRepository;
use App\Dungeon\Repositories\ShopRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Support\Authentication\User;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
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
    public function index(User $user): View|Redirect
    {
        $stats = $this->statsRepository->forUser($user);

        if (! $stats->nickname) {
            return new Redirect(uri([self::class, 'nickname']));
        }

        $shop = $this->shopRepository->forUser($user);
        $deck = $this->deckRepository->forUser($user);
        $dungeon = $this->dungeonRepository->forUser($user);

        return view(
            'dungeon-home.view.php',
            stats: $stats,
            shop: $shop,
            deck: $deck,
            dungeon: $dungeon,
            rank: $this->statsRepository->getRank($user),
        );
    }

    #[Post('/dungeon/deck/{id}/activate')]
    public function activateCard(int $id, User $user, CardRepository $cardRepository, DeckValidator $deckValidator): View
    {
        $dungeonUserCard = DungeonUserCard::select()
            ->where('id', $id)
            ->where('userId', $user->id->value)
            ->where('isActive', false)
            ->first();

        $dungeonUserCard->card = $cardRepository->findByName($dungeonUserCard->cardName);

        if (! $dungeonUserCard) {
            return $this->renderDeckBuilder($user);
        }

        $deck = arr($this->deckRepository->activeCardsForUser($user))
            ->map(fn (DungeonUserCard $dungeonUserCard) => $dungeonUserCard->card);

        if (($validationResult = $deckValidator->validate($dungeonUserCard->card, $deck)) !== null) {
            return $this->renderDeckBuilder($user, deckValidationFailed: $validationResult);
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

    #[Post('/dungeon/deck/{id}/sell')]
    public function sellCard(int $id, User $user, CardRepository $cardRepository): View
    {
        $dungeonUserCard = DungeonUserCard::select()
            ->where('id', $id)
            ->where('userId', $user->id->value)
            ->where('isActive', false)
            ->first();

        if (! $dungeonUserCard) {
            return $this->renderDeckBuilder($user);
        }

        $dungeonUserCard->card = $cardRepository->findByName($dungeonUserCard->cardName);

        $this->statsRepository->increaseStats($user, coins: $dungeonUserCard->card->sellPrice);
        $dungeonUserCard->delete();

        return $this->renderDeckBuilder($user);
    }

    #[Get('/dungeon/nickname')]
    public function nickname(): View
    {
        return view('dungeon-nickname.view.php');
    }

    #[Post('/dungeon/nickname')]
    public function storeNickname(User $user, NicknameRequest $request): Redirect
    {
        $stats = $this->statsRepository->forUser($user);
        $stats->extra[Dungeon::NICKNAME] = $request->nickname;
        $stats->save();

        return new Redirect(uri([self::class, 'index']));
    }

    #[Get('/dungeon/leaderboard')]
    public function leaderboard(): View
    {
        return view(
            'dungeon-leaderboard.view.php',
            leaderboard: $this->statsRepository->getLeaderboard(),
        );
    }

    private function renderDeckBuilder(User $user, ...$data): View
    {
        $data['deck'] = $this->deckRepository->forUser($user);
        $data['shop'] = $this->shopRepository->forUser($user);
        $data['stats'] = $this->statsRepository->forUser($user);
        $data['rank'] = $this->statsRepository->getRank($user);
        $data['dungeon'] = $this->dungeonRepository->forUser($user);

        return view(
            'x-deck-builder.view.php',
            ...$data,
        );
    }
}