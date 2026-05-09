<?php

namespace App\Analytics;

interface Chartable
{
    public string $label {
        get;
    }

    public int $value {
        get;
    }
}