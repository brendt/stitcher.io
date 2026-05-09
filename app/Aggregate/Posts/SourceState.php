<?php

namespace App\Aggregate\Posts;

enum SourceState: string
{
    case PENDING = 'pending';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case DENIED = 'denied';
    case INVALID = 'invalid';
    case DUPLICATE = 'duplicate';
}
