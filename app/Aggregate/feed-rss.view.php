<?php
/** @var \App\Aggregate\Posts\Post[] $posts */

use App\Aggregate\Posts\PostsController;
use Tempest\DateTime\FormatPattern;
use function Tempest\Router\uri;

?>

<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="en-US">
    <id>https://stitcher.io/feed/rss</id>
    <link href="https://stitcher.io/feed/rss" rel="self"/>
    <title>
        <![CDATA[ Stitcher's Community Feed ]]>
    </title>
    <subtitle>An aggregation of great content across the web</subtitle>
    <updated><?= date('c') ?></updated>

    <?php foreach ($posts as $post): ?>
        <entry>
            <title><![CDATA[ <?= $post->title ?> ]]></title>
            <link rel="alternate" :href="uri([PostsController::class, 'visit'], post: $post->id)" />
            <id><?= $post->cleanUri ?></id>
            <updated><?= $post->createdAt->format(FormatPattern::ISO8601) ?></updated>
            <published><?= $post->publicationDate->format(FormatPattern::ISO8601) ?></published>
            <author>
                <name>
                    <![CDATA[ Stitcher Feed ]]>
                </name>
            </author>
        </entry>
    <?php endforeach; ?>
</feed>
