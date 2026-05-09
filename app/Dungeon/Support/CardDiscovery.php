<?php

namespace App\Dungeon\Support;

use App\Dungeon\Card;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class CardDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly CardConfig $cardConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Card::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->cardConfig->cards[] = new $className();
        }
    }
}