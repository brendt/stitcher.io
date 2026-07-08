<?php

namespace App\Mail\Migrations;

use App\Mail\Models\OutboxCampaign;
use Tempest\Database\Enums\DatabaseTextLength;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateOutboxCampaignTable implements MigratesUp
{
    public string $name = '2026-07-07_01_create_outbox_campaigns_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(OutboxCampaign::class)
            ->primary()
            ->string('path')
            ->datetime('startedAt')
            ->datetime('endedAt', nullable: true)
            ->datetime('failedAt', nullable: true)
            ->text('log', nullable: true, length: DatabaseTextLength::LONG);
    }
}
