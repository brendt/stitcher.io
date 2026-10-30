<?php

namespace App\Todo;

enum TaskKind: string
{
    // A line starting with `- `, which can be completed
    case TASK = 'task';

    // Any other text within a project, which is just shown
    case TEXT = 'text';
}
