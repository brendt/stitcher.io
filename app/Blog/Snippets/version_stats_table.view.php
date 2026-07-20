<?php

use function Tempest\src_path;

$version ??= null;

if (! is_string($version) || $version === '') {
    return;
}

$columnCount = 4;
$rowCount = 7;

/** @var array<string, array<string, float|string|null>> $data */
$data = "Blog/VersionStats/Data/{$version}-version-stats.json"
    |> src_path(...)
    |> file_get_contents(...)
    |> (fn ($x) => json_decode($x, associative: true, flags: JSON_THROW_ON_ERROR))
    |> (fn ($x) => array_slice($x, -1 * $columnCount, $columnCount, preserve_keys: true));

foreach ($data as $date => $month) {
    $data[$date] = array_slice($month, -1 * $rowCount, $rowCount);
}

$rows = [];

$headers = array_keys($data);

foreach ($data as $date => $month) {
    foreach ($month as $version => $value) {
        $rows[$version][$date] = $value
            ? (floatval($value) * 100) . '%'
            : '-';
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
