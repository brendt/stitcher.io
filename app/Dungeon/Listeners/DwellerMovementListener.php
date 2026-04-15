<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Dweller;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Point;
use App\Dungeon\Support\Random;
use Tempest\EventBus\EventHandler;

final class DwellerMovementListener
{
    public function __construct(
        private Dungeon $dungeon,
        private Random $random,
    ) {}

    #[EventHandler]
    public function moveDwellers(PlayerMoved $event): void
    {
        foreach ($this->dungeon->loopDwellers() as $dweller) {
            for ($i = 1; $i <= 2; $i++) {
                if ($this->random->chance(1/3)) {
                    continue;
                }

                $movement = $this->getMovement($dweller);

                $originalPoint = $dweller->point;
                $newPoint = $originalPoint->translate(...$movement);

                if (! $this->canMoveTo($dweller, $newPoint)) {
                    continue;
                }

                $this->dungeon->moveDweller($dweller, $newPoint);

                break;
            }
        }
    }

    private function getMovement(Dweller $dweller): array
    {
        $axis = $this->random->item(['x', 'y']);

        if ($this->dungeon->currentTile->point->{$axis} > $dweller->point->{$axis}) {
            $direction = $this->random->item([1, 1, 1, 0, 0, -1]);
        } else {
            $direction = $this->random->item([-1, -1, -1, 0, 0, 1]);
        }

        return [$axis => $direction];
    }

    private function canMoveTo(Dweller $dweller, Point $to): bool
    {
        // No movement by RNG
        if ($dweller->point->equals($to)) {
            return true;
        }

        // Check whether the Dweller doesn't walk through walls or into collapsed tiles
        $direction = $dweller->point->directionTo($to);

        $fromTile = $this->dungeon->tryTile($dweller->point);
        $toTile = $this->dungeon->tryTile($to);

        if ($toTile && $toTile->isCollapsed) {
            return false;
        }

        $canMoveFromTile = ! $fromTile?->hasBorder($direction);
        $canMoveToTile = ! $toTile?->hasBorder($direction->opposite());

        if (! $canMoveFromTile || ! $canMoveToTile) {
            return false;
        }

        if (isset($this->dwellers[$to->x][$to->y])) {
            return false;
        }

        return true;
    }
}