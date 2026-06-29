<?php

use Tempest\Markdown\Markdown;

require_once __DIR__ . '/vendor/autoload.php';

$markdown = new Markdown();

$content = file_get_contents(__DIR__ . '/input.md');

if (! $content) {
    die('Something went wrong!');
}

if (file_exists(__DIR__ . '/output.html')) {
    unlink(__DIR__ . '/output.html');
}

file_put_contents(__DIR__ . '/output.html', $markdown->parse($content));