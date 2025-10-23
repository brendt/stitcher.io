<?php

namespace App\Support\CommandPalette;

use Override;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class IndexerDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly IndexerConfig $indexerConfig,
    ) {}

    #[Override]
    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Indexer::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    #[Override]
    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->indexerConfig->indexerClasses[] = $className;
        }
    }
}
