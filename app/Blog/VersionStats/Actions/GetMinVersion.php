<?php

namespace App\Blog\VersionStats\Actions;

final class GetMinVersion
{
    public function __invoke(string $versionConstraint): ?string
    {
        $packageVersions = [];

        preg_match('/(\d\.\d)/', $versionConstraint, $packageVersions);

        sort($packageVersions);

        return $packageVersions[0] ?? null;
    }
}
