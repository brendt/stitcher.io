<?php

namespace App\Mail\Migrations;

use App\Mail\Models\OutboxMail;
use Tempest\Database\Enums\DatabaseTextLength;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateOutboxMailTable implements MigratesUp
{
    public string $name = '2026-07-07_02_create_outbox_mails_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(OutboxMail::class)
            ->primary()
            ->string('receiver')
            ->string('subject')
            ->text('content', length: DatabaseTextLength::LONG)
            ->datetime('sendingAt', nullable: true)
            ->datetime('sentAt', nullable: true)
            ->datetime('failedAt', nullable: true)
            ->text('log', nullable: true, length: DatabaseTextLength::LONG)
            ->belongsTo('outbox_mails.campaign_id', 'campaigns.id');
    }
}
