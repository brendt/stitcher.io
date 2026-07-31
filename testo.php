<?php

declare(strict_types=1);

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;

/**
 * The Tempest kernel reads its environment through `getenv()`, and `Dotenv` is loaded immutably —
 * so these win over `.env`. This is the Testo counterpart of `phpunit.xml`'s `<php><env>` block.
 */
foreach (['ENVIRONMENT' => 'testing', 'BASE_URI' => '', 'CACHE' => 'null'] as $key => $value) {
    putenv("{$key}={$value}");
}

return new ApplicationConfig(
    src: new FinderConfig(include: ['app']),
    suites: [
        new SuiteConfig(
            name: 'Integration',
            location: ['testo/Analytics', 'testo/Dungeon'],
        ),
    ],
);
