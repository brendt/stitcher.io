<?php

namespace App\Map\Item\TileItem;

use App\Map\Item\HasMenu;
use App\Map\Item\ItemPrice;
use App\Map\Item\TileItem;
use App\Map\MapGame;
use App\Map\Menu;
use App\Map\Tile\GenericTile\LandTile;
use App\Map\Tile\GenericTile\WaterTile;
use App\Map\Tile\ResourceTile\Resource;
use App\Map\Tile\Tile;
use Illuminate\View\View;

final class TradingPost implements TileItem, HasMenu
{
    public ?Resource $input = null;

    public ?Resource $output = null;

    public function getId(): string
    {
        return 'TradingPost';
    }

    public function getName(): string
    {
        return 'Trading Post';
    }

    public function getPrice(): ItemPrice
    {
        return new ItemPrice();
    }

    public function canInteract(MapGame $game, Tile $tile): bool
    {
        if (! $tile instanceof LandTile) {
            return false;
        }

        $neighbours = $game->getNeighbours($tile);

        foreach ($neighbours as $tile) {
            if ($tile instanceof WaterTile) {
                return true;
            }
        }

        return false;
    }

    public function handleTicks(MapGame $game, Tile $tile, int $ticks): void
    {
        if (! $this->input || ! $this->output) {
            return;
        }

        foreach (range(1, $ticks) as $tick) {
            if ($game->{$this->input->getCountPropertyName()} >= 4) {
                $game->{$this->input->getCountPropertyName()} -= 4;
                $game->{$this->output->getCountPropertyName()} += 1;
            }
        }
    }

    public function getModifier(): int
    {
        return 1;
    }

    public function getMenu(): Menu
    {
        return new Menu(
            hasMenu: $this,
            viewPath: 'mapGame.tradingPostMenu',
            form: [
                'input' => $this->input,
                'output' => $this->output,
            ],
        );
    }

    public function saveMenu(array $form): void
    {
        $this->input = Resource::tryFrom($form['input']);
        $this->output = Resource::tryFrom($form['output']);
    }
}
