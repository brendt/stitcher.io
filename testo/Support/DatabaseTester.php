<?php

declare(strict_types=1);

namespace Tests\Testo\Support;

use Tempest\Container\Container;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\MigrationManager;
use Testo\Assert;

use function Tempest\Database\query;

/**
 * Database tester backed by `Testo\Assert`.
 *
 * Mirrors `Tempest\Testing\Testers\Database\DatabaseTester`, but records its checks in Testo's
 * assertion history — otherwise a passing test that only asserts through the tester would be
 * reported as `Status::Risky`.
 */
final readonly class DatabaseTester
{
    public function __construct(
        private Container $container,
    ) {}

    public function setup(bool $migrate = true): self
    {
        return $this->reset($migrate);
    }

    public function reset(bool $migrate = true): self
    {
        $this->container->get(MigrationManager::class)->dropAll();

        if ($migrate) {
            $this->migrate();
        }

        return $this;
    }

    public function migrate(string|object ...$migrationClasses): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        if ($migrationClasses === []) {
            $migrationManager->up();

            return;
        }

        foreach ($migrationClasses as $migrationClass) {
            $migration = is_string($migrationClass) && class_exists($migrationClass)
                ? $this->container->get($migrationClass)
                : $migrationClass;

            if (! $migration instanceof MigratesUp) {
                continue;
            }

            $migrationManager->executeUp($migration);
        }
    }

    public function assertTableHasRow(string $table, mixed ...$data): self
    {
        Assert::true(
            $this->countMatching($table, $data) > 0,
            sprintf('Failed asserting that a row in the table %s matches the given data.', $table),
        );

        return $this;
    }

    public function assertTableDoesNotHaveRow(string $table, mixed ...$data): self
    {
        Assert::same(
            $this->countMatching($table, $data),
            0,
            sprintf('Failed asserting that no row in the table %s matches the given data.', $table),
        );

        return $this;
    }

    public function assertTableHasCount(string $table, int $count): self
    {
        Assert::same(
            query($table)->count()->execute(),
            $count,
            sprintf('Failed asserting that the table %s contains %s rows.', $table, $count),
        );

        return $this;
    }

    public function assertTableEmpty(string $table): self
    {
        return $this->assertTableHasCount($table, count: 0);
    }

    public function assertTableNotEmpty(string $table): self
    {
        return $this->assertTableHasRow($table);
    }

    private function countMatching(string $table, array $data): int
    {
        $select = query($table)->count();

        foreach ($data as $key => $value) {
            $select->whereField((string) $key, $value);
        }

        return $select->execute();
    }
}
