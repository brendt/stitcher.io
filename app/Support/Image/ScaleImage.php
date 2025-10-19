<?php

namespace App\Support\Image;

use Tempest\CommandBus\Async;

#[Async]
final readonly class ScaleImage
{
    public function __construct(
        public Image $image,
    ) {}
}