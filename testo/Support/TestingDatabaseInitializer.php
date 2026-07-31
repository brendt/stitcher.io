<?php

declare(strict_types=1);

namespace Tests\Testo\Support;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Database;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBus;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

use function Tempest\Support\str;

/**
 * Connects to the database configured in `testo/database.testing.config.php` as-is.
 *
 * `Tempest\Testing\Testers\Database\TestingDatabaseInitializer` cannot be reused here: it appends
 * the name of a `Tempest\Testing\Runner\TestRunner` singleton to the database name, to keep the
 * parallel workers of the Tempest runner apart. Testo has no such runner, so the lookup fails.
 */
final class TestingDatabaseInitializer implements DynamicInitializer
{
    /** @var array<string, Connection> */
    private static array $connections = [];

    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $class->getType()->matches(Database::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): Database
    {
        $key = str($tag)->toString();

        $connection = self::$connections[$key] ?? null;

        if ($connection === null) {
            $connection = new PDOConnection($container->get(DatabaseConfig::class, $tag));
            $connection->connect();

            self::$connections[$key] = $connection;
        } elseif ($connection->ping() === false) {
            $connection->reconnect();
        }

        $container->singleton(Connection::class, $connection, $tag);

        return new GenericDatabase(
            connection: $connection,
            transactionManager: new GenericTransactionManager($connection),
            serializerFactory: $container->get(SerializerFactory::class),
            eventBus: $container->get(EventBus::class),
        );
    }
}
