<?php

namespace App\Support\StoredEvents;

interface BuffersUpdates
{
    public function persist(): void;
}