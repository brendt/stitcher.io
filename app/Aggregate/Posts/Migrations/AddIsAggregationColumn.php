<?php

namespace App\Aggregate\Posts\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\BooleanStatement;

final class AddIsAggregationColumn implements MigratesUp
{
    public string $name = '2026-05-08-000_add_is_aggregation_column';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('sources')
            ->add(new BooleanStatement('isAggregation', default: false));
    }
}
