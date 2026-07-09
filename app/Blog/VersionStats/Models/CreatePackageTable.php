<?php

namespace App\Blog\VersionStats\Models;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreatePackageTable implements MigratesUp
{
    public string $name = '2026_07_09_create_package_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Package::class)
            ->primary()
            ->text('name')
            ->integer('downloads', unsigned: true)
            ->integer('favers', unsigned: true)
            ->text('versionString', nullable: true)
            ->text('minVersion', nullable: true)
            ->text('maxVersion', nullable: true)
            ->datetime('lastReleasedAt', nullable: true)
            ->datetime('checkedAt', nullable: true);
    }
}