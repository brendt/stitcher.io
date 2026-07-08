<?php

namespace App\Mail\Migrations;

use App\Mail\Models\Subscriber;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateSubscriberTable implements MigratesUp
{
    public string $name = '2026-07-07_00_create_subscribers_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Subscriber::class)
            ->primary()
            ->string('uuid')
            ->string('email')
            ->string('name')
            ->datetime('subscribedAt')
            ->datetime('unsubscribedAt', nullable: true);
    }
}
