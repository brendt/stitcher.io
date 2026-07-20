<?php

namespace App\Mail\Migrations;

use App\Mail\Models\Campaign;
use Tempest\Database\Enums\DatabaseTextLength;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateCampaignTable implements MigratesUp
{
    public string $name = '2026-07-07_01_create_campaigns_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Campaign::class)
            ->primary()
            ->string('path')
            ->datetime('startedAt')
            ->datetime('processedAt', nullable: true)
            ->datetime('failedAt', nullable: true)
            ->text('log', nullable: true, length: DatabaseTextLength::LONG);
    }
}
