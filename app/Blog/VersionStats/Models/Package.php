<?php

namespace App\Blog\VersionStats\Models;

use Tempest\Database\Id;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\DateTime\DateTime;

final class Package
{
    use IsDatabaseModel;

    public PrimaryKey $id;
    public string $name;
    public int $downloads;
    public int $favers;
    public ?string $versionString;
    public ?string $minVersion;
    public ?string $maxVersion;
    public ?DateTime $lastReleasedAt;
    public ?DateTime $checkedAt;
}
