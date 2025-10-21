<?php

namespace App\Support\Sitemap;

final class Sitemap
{
    public function __construct(
        public array $uris = [],
    ) {}
}