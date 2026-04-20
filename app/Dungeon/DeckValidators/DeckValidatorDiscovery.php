<?php

namespace App\Dungeon\DeckValidators;

use App\Dungeon\DeckValidator;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class DeckValidatorDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Container $container,
        private readonly CompoundDeckValidator $deckValidator,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(DeckValidator::class)) {
            return;
        }

        if ($class->is(CompoundDeckValidator::class)) {
            return;
        }

        $this->discoveryItems->add($location, $class->getName());
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $class) {
            $this->deckValidator->addValidator($this->container->get($class));
        }
    }
}