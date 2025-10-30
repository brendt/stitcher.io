<?php

namespace App\Map;

use App\Map\Item\HandHeldItem\Axe;
use App\Map\Item\HandHeldItem\FishingNet;
use App\Map\Item\HandHeldItem;
use App\Map\Item\HasMenu;
use App\Map\Item\Item;
use App\Map\Item\HandHeldItem\Pickaxe;
use App\Map\Item\HandHeldItem\Shears;
use App\Map\Item\TileItem\FishFarmer;
use App\Map\Item\TileItem\FlaxFarmer;
use App\Map\Item\TileItem\GoldVeinFarmer;
use App\Map\Item\TileItem\TradingPost;
use App\Map\Item\TileItem\TreeFarmer;
use App\Map\Item\TileItem\StoneVeinFarmer;
use App\Map\Layer\BaseLayer;
use App\Map\Layer\BiomeLayer;
use App\Map\Layer\ElevationLayer;
use App\Map\Layer\FishLayer;
use App\Map\Layer\FlaxLayer;
use App\Map\Layer\GoldVeinLayer;
use App\Map\Layer\LandLayer;
use App\Map\Layer\StoneVeinLayer;
use App\Map\Layer\TemperatureLayer;
use App\Map\Layer\TreeLayer;
use App\Map\Noise\PerlinGenerator;
use App\Map\Tile\HandlesClick;
use App\Map\Tile\HandlesTicks;
use App\Map\Tile\ResourceTile;
use App\Map\Tile\ResourceTile\Resource;
use App\Map\Tile\Tile;
use Tempest\Http\Session\Session;
use function Tempest\get;

/**
 * @property HandHeldItem[] $items
 */
final class MapGame
{
    private Session $session {
        get => get(Session::class);
    }

    public function __construct(
        public int $seed,
        public BaseLayer $baseLayer,
        public int $woodCount = 0,
        public int $stoneCount = 0,
        public int $goldCount = 0,
        public int $fishCount = 0,
        public int $flaxCount = 0,
        public ?Item $selectedItem = null,
        public int $gameTime = 0,
        public array $handHeldItems = [],
        public bool $paused = false,
        public ?Menu $menu = null,
    ) {}

    public static function resolve(Map $component, ?int $seed = null): self
    {
        if ($fromSession = get(Session::class)->get('map')) {
            $game = unserialize($fromSession);
        } else {
            $game = self::init($seed ?? 1);
        }

        $game->setComponent($component);

        if (request()->query->has('cheat')) {
            foreach (Resource::cases() as $case) {
                $property = $case->getCountPropertyName();

                while ($game->{$property} < 1000) {
                    $game->{$property} += 1000;
                }
            }
        }

        return $game;
    }

    private function setComponent(Map $component): void
    {
        $this->component = $component;
    }

    public function persist(): self
    {
        $this->updateGameTime();

        $this->session->put('map', serialize($this));

        return $this;
    }

    public function destroy(): void
    {
        $this->session->remove('map');
    }

    public static function init(int $seed): self
    {
        $generator = new PerlinGenerator($seed);

        $baseLayer = (new BaseLayer(width: 100, height: 70))
            ->add(new TemperatureLayer($generator))
            ->add(new ElevationLayer($generator))
            ->add(new BiomeLayer())
            ->add(new LandLayer($generator))
            ->add(new TreeLayer($generator))
            ->add(new FishLayer($generator))
            ->add(new GoldVeinLayer($generator))
            ->add(new StoneVeinLayer($generator))
            ->add(new FlaxLayer($generator))
            ->generate();

        return new self(
            seed: $seed,
            baseLayer: $baseLayer,
            gameTime: time(),
        );
    }

    public function handleClick($x, $y): self
    {
        $tile = $this->baseLayer->get($x, $y);

        if ($tile instanceof HandlesClick && $tile->canClick($this)) {
            $tile->handleClick($this);
        }

        return $this;
    }

    public function selectItem(string $itemId): self
    {
        $item = $this->getAvailableItems()[$itemId] ?? null;

        if (! $item) {
            return $this;
        }

        $this->selectedItem = $item;

        return $this;
    }

    public function buyHandHeldItem(string $itemId): self
    {
        $item = $this->getAvailableItems()[$itemId] ?? null;

        if (! $item instanceof HandHeldItem) {
            return $this;
        }

        $this->buyItem($item);

        return $this;
    }

    public function buyItem(Item $item): self
    {
        $itemPrice = $item->getPrice();

        $this->woodCount -= $itemPrice->wood;
        $this->goldCount -= $itemPrice->gold;
        $this->stoneCount -= $itemPrice->stone;
        $this->flaxCount -= $itemPrice->flax;
        $this->fishCount -= $itemPrice->fish;

        if ($item instanceof HandHeldItem) {
            $this->handHeldItems[$item->getId()] = $item;
        }

        $this->unselectItem();

        return $this;
    }

    public function unselectItem(): self
    {
        $this->selectedItem = null;

        return $this;
    }

    private function updateGameTime(): void
    {
        $oldTime = $this->gameTime;

        $newTime = time();

        $difference = $newTime - $oldTime;

        foreach ($this->baseLayer->loop() as $tile) {
            if ($tile instanceof HandlesTicks) {
                $tile->handleTicks($this, $difference);
            }
        }

        $this->gameTime = $newTime;
    }

    public function canBuy(Item $item): bool
    {
        $itemPrice = $item->getPrice();

        return
            $itemPrice->wood <= $this->woodCount
            && $itemPrice->gold <= $this->goldCount
            && $itemPrice->stone <= $this->stoneCount
            && $itemPrice->flax <= $this->flaxCount
            && $itemPrice->fish <= $this->fishCount;
    }

    /**
     * @return Item[]|Collection
     */
    public function getAvailableItems(): Collection
    {
        $handHeldItems = collect([
            new Pickaxe(),
            new Axe(),
            new FishingNet(),
            new Shears(),
        ])
            ->reject(fn (HandHeldItem $item) => isset($this->handHeldItems[$item->getId()]));

        $tileItems = [
            new TreeFarmer(),
            new StoneVeinFarmer(),
            new GoldVeinFarmer(),
            new FlaxFarmer(),
            new FishFarmer(),
            new TradingPost(),
        ];

        return $handHeldItems
            ->merge($tileItems)
            ->mapWithKeys(fn (Item $item) => [$item->getId() => $item]);
    }

    public function getHandHeldItemForTile(Tile $tile): ?HandHeldItem
    {
        foreach ($this->handHeldItems as $item) {
            if ($item->canInteract($tile)) {
                return $item;
            }
        }

        return null;
    }

    public function incrementResource(Resource $resource, int $amount): self
    {
        $property = $resource->getCountPropertyName();

        $this->{$property} += $amount;

        return $this;
    }

    public function resourcePerTick(Resource $resource): int
    {
        $count = 0;

        foreach ($this->baseLayer->loop() as $tile) {
            if (! $tile instanceof ResourceTile) {
                continue;
            }

            if ($tile->getResource() !== $resource) {
                continue;
            }

            $count += $tile->getItem()?->getModifier() ?? 0;
        }

        return $count;
    }

    /**
     * @return Tile[]
     */
    public function getNeighbours(Tile $tile): array
    {
        return array_filter([
            $this->baseLayer->get($tile->getX() - 1, $tile->getY()),
            $this->baseLayer->get($tile->getX() + 1, $tile->getY()),
            $this->baseLayer->get($tile->getX(), $tile->getY() - 1),
            $this->baseLayer->get($tile->getX(), $tile->getY() + 1),
        ]);
    }

    public function openMenu(HasMenu $hasMenu): void
    {
        $this->paused = true;
        $this->menu = $hasMenu->getMenu();
    }

    public function closeMenu(): self
    {
        $this->paused = false;
        $this->menu = null;

        return $this;
    }

    public function saveMenu(array $form): self
    {
        $this->menu->hasMenu->saveMenu($form);
        $this->closeMenu();

        return $this;
    }
}
