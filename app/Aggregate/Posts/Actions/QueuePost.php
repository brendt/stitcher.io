<?php

namespace App\Aggregate\Posts\Actions;

use App\Aggregate\Posts\Post;
use App\Aggregate\Posts\PostState;
use Tempest\Database\Query;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;

final class QueuePost
{
    public function __invoke(Post $post): Post
    {
        $lastFullDay = new Query(<<<SQL
        SELECT publicationDate
        FROM posts
        WHERE publicationDate > :publicationDate
        AND state = :state
        GROUP BY publicationDate
        HAVING COUNT(*) >= 3
        ORDER BY publicationDate DESC
        LIMIT 1;

        SQL)->fetchFirst(
            publicationDate: DateTime::now()->startOfDay()->format(FormatPattern::SQL_DATE_TIME),
            state: PostState::PUBLISHED,
        );

        $nextDate = DateTime::parse($lastFullDay['publicationDate'] ?? 'now')
            ->plusDay()
            ->startOfDay();

        $post->state = PostState::PUBLISHED;
        $post->publicationDate = $nextDate;
        $post->save();

        return $post;
    }
}