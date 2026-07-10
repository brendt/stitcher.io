<?php

use function Tempest\src_path;

$version ??= null;

if (! $version) {
    return;
}

$columnCount = 4;

$data = "Blog/VersionStats/Data/{$version}-package-stats.json"
    |> src_path(...)
    |> file_get_contents(...)
    |> (fn ($x) => json_decode($x, associative: true, flags: JSON_THROW_ON_ERROR));

ksort($data);

$data = array_slice($data, -1 * $columnCount, $columnCount, preserve_keys: true);

$versions = [];

foreach ($data as $month) {
    foreach (array_keys($month) as $phpVersion) {
        $versions[$phpVersion] = $phpVersion;
    }
}

uksort($versions, version_compare(...));

$rows = [];
$headers = array_keys($data);

foreach (array_values($versions) as $phpVersion) {
    foreach ($data as $date => $month) {
        $value = $month[$phpVersion] ?? null;

        $rows[$phpVersion][$date] =
            $value === null || $value === ''
                ? '-'
                : (string) $value;
    }
}
?>

<div class="table-container">
    <table>
        <tr class="table-head">
            <td class="text-left">Version</td>
            <td :foreach="$headers as $header" class="text-right">{{ $header }}</td>
        </tr>

        <tr :foreach="$rows as $version => $row">
            <td class="text-left">{{ $version }}</td>
            <td :foreach="$row as $value" class="text-right">{{ $value }}</td>
        </tr>
    </table>
</div>
