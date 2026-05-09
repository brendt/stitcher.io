<?php

namespace App\Support\StoredEvents;

interface BufferedProjector extends Projector
{
    public function persist(): void;
}