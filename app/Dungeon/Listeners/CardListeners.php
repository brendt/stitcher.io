<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\CardPlayed;
use Tempest\EventBus\EventHandler;

final readonly class CardListeners
{
    public function __construct(
        private Dungeon $dungeon,
    ) {}

    #[EventHandler]
    public function handleImmediateCard(CardPlayed $event): void
    {
        if (! $event->card->type->isImmediate()) {
            return;
        }

        $this->dungeon->drawCard();
    }

    #[EventHandler]
    public function handlePassiveCard(CardPlayed $event): void
    {
        if (! $event->card->type->isPassive()) {
            return;
        }

        $this->dungeon->setPassiveCard($event->card);
    }

    #[EventHandler]
    public function handleActiveCard(CardPlayed $event): void
    {
        if (! $event->card->type->isActive()) {
            return;
        }

        $this->dungeon->setActiveCard($event->card);
    }

    #[EventHandler]
    public function handlePermanentCard(CardPlayed $event): void
    {
        if (! $event->card->type->isPermanent()) {
            return;
        }

        $this->dungeon->addPermanentCard($event->card);
    }
}