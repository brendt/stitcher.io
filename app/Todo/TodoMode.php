<?php

namespace App\Todo;

/**
 * What the todo page is showing, which determines what can be done there.
 */
enum TodoMode: string
{
    // A workspace's projects, which can be edited
    case WORKSPACE = 'workspace';

    // Open tasks with a date from all workspaces, which can be completed but not edited
    case SOON = 'soon';

    // Archived projects from all workspaces, which can only be unarchived
    case ARCHIVED = 'archived';

    public function placeholder(): string
    {
        return match ($this) {
            self::WORKSPACE => "# Project name\n- Todo 1\n- Todo 2",
            self::SOON => 'Nothing coming up',
            self::ARCHIVED => 'Nothing archived yet',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::WORKSPACE => '',
            self::SOON => 'Soon',
            self::ARCHIVED => 'Archived',
        };
    }
}
