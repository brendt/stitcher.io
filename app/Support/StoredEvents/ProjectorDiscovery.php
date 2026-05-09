<?php

declare(strict_types=1);

namespace App\Support\StoredEvents;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ProjectorDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly StoredEventConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Projector::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->projectors[] = $className;
        }
    }
}
