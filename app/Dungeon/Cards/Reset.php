<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Card;
use App\Dungeon\Dungeon;
use App\Dungeon\Events\DeckUpdated;
use App\Dungeon\Events\HandUpdated;
use App\Dungeon\Level;
use App\Dungeon\Point;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use function Tempest\EventBus\event;

final class Reset implements Card
{
    use IsCard;

    public string $name = 'Reset';
    public string $description = 'At the cost of half your health and a random teleport, get back all your cards';
    public int $mana = 200;
    public Rarity $rarity = Rarity::EPIC;
    public Type $type = Type::IMMEDIATE;
    public string $image = '/cards/artifact.png';
    public int $price = 50_000;
    public Level $level = Level::GRANDMASTER;

    public function play(Dungeon $dungeon): void
    {
        $point = new Point(
            random_int(50, 80),
            random_int(50, 80),
        );

        $dungeon->generateTile(from: null, to: $point);
        $dungeon->placePlayer($point);
        $dungeon->decreaseHealth((int) round($dungeon->health / 2));

        $newDeck = [];

        foreach ($dungeon->discardedCards as $discardedCard) {
            $newDeck[$discardedCard->id] = $discardedCard;
        }

        foreach ($dungeon->hand as $handCard) {
            if ($handCard->id === $this->id) {
                continue;
            }

            $newDeck[$handCard->id] = $handCard;
        }

        foreach ($dungeon->deck as $deckCard) {
            $newDeck[$deckCard->id] = $deckCard;
        }

        $dungeon->deck = $newDeck;
        $dungeon->hand = [];
        $dungeon->discardedCards = [];
        $dungeon->discard($this);

        event(new DeckUpdated($dungeon->deck));

        foreach (range(0, $dungeon->maxHandCount) as $i) {
            $dungeon->drawCard();
        }

        event(new HandUpdated($dungeon->hand));
    }
}