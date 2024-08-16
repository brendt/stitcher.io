<?php

namespace App\Comments;

use Tempest\Database\Migration;
use Tempest\Database\Query;

final readonly class CreateCommentsTable implements Migration
{
    public function getName(): string
    {
        return '001-create-comments-table';
    }

    public function up(): Query|null
    {
        return new Query("CREATE TABLE Comment (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `postId` TEXT NOT NULL,
            `email` TEXT NOT NULL,
            `comment` TEXT NOT NULL,
            `createdAt` DATETIME NOT NULL,
            `ip` TEXT NOT NULL
        )");
    }

    public function down(): Query|null
    {
        return null;
    }
}