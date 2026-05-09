<?php

namespace App\Nws;

enum Sentiment: string
{
    case POSITIVE = 'POSITIVE';
    case NEUTRAL = 'NEUTRAL';
    case NEGATIVE = 'NEGATIVE';
}
