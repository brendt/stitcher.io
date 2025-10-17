<?php
/** @var \App\Blog\BlogPost[] $posts */
use Tempest\DateTime\FormatPattern;
use function Tempest\Router\uri;
?>

<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="en-US">
    <id>https://stitcher.io/rss</id>
    <link href="https://stitcher.io/rss" rel="self"/>
    <title>
        <![CDATA[ stitcher.io ]]>
    </title>
    <updated><?= date('c') ?></updated>

    <?php foreach ($posts as $post): ?>
        <entry>
            <title><![CDATA[ {!! $post->title !!} ]]></title>
            <link rel="alternate" :href="$post->uri"/>
            <id>{{ $post->uri }}</id>
            <updated>{{ $post->date->format(FormatPattern::ISO8601) }}</updated>
            <published>{{ $post->date->format(FormatPattern::ISO8601) }}</published>
            <author>
                <name>
                    <![CDATA[ Brent ]]>
                </name>
            </author>
            <summary type="html"><![CDATA[ {!! $post->content !!} ]]></summary>
        </entry>
    <?php endforeach; ?>
</feed>
