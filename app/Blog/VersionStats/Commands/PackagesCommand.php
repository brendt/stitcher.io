<?php

namespace App\Blog\VersionStats\Commands;

use App\Blog\VersionStats\Actions\GetMinVersion;
use App\Blog\VersionStats\Models\Package;
use Tempest\Cache\Cache;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Duration;
use Tempest\Http\Response;
use Tempest\HttpClient\HttpClient;

use function Tempest\Database\query;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class PackagesCommand
{
    use HasConsole;

    public function __construct(
        private readonly HttpClient $http,
        private readonly Cache $cache,
    ) {}

    #[ConsoleCommand]
    public function fetch(): void
    {
        foreach (range(1, 10) as $page) {
            $this->info("Fetching page {$page}");

            $payload = $this->cache->resolve(
                key: 'packages' . $page,
                callback: fn () => $this->http
                    ->get(
                        'https://packagist.org/explore/popular.json?per_page=100&page=' . $page,
                        ['Accept' => 'application/json'],
                    ),
                expiration: Duration::minutes(10),
            );

            if (! $payload instanceof Response || ! is_string($payload->body)) {
                continue;
            }

            $decoded = json_decode($payload->body, associative: true, flags: JSON_THROW_ON_ERROR);
            $packages = is_array($decoded) && isset($decoded['packages']) && is_array($decoded['packages'])
                ? $decoded['packages']
                : [];

            arr($packages)
                ->each(function (array $packageData) {
                    $name = $packageData['name'] ?? null;

                    if (! is_string($name) || $name === '') {
                        return null;
                    }

                    $package = Package::updateOrCreate(
                        ['name' => $name],
                        [
                            'name' => $name,
                            'downloads' => $packageData['downloads'],
                            'favers' => $packageData['favers'],
                        ],
                    );

                    $this->success($package->name);
                });
        }
    }

    #[ConsoleCommand]
    public function parse(): void
    {
        Package::select()->chunk($this->parsePackages(...), 10);
    }

    #[ConsoleCommand]
    public function store(): void
    {
        $data = arr(
            query('packages')
                ->select('minVersion, COUNT(*) as amount')
                ->whereNotNull('minVersion')
                ->groupBy('minVersion')
                ->orderBy('minVersion')
                ->limit(1000)
                ->all(),
        )
            ->mapWithKeys(function (mixed $row) {
                if (! is_array($row)) {
                    return;
                }

                $minVersion = $row['minVersion'] ?? null;

                if (! is_string($minVersion) || $minVersion === '') {
                    return;
                }

                yield $minVersion => $row['amount'] ?? 0;
            });

        $this->success((string) $data->encodeJson(true));
    }

    /**
     * @param \App\Blog\VersionStats\Models\Package[] $packages
     */
    private function parsePackages(array $packages): void
    {
        foreach ($packages as $package) {
            $payload = $this->cache->resolve(
                str('package-composer-' . $package->name)->slug(),
                fn () => $this->http->get("https://repo.packagist.org/p2/{$package->name}.json"),
                expiration: Duration::minutes(10),
            );

            if (! $payload instanceof Response || ! is_string($payload->body)) {
                continue;
            }

            $decoded = json_decode($payload->body, associative: true, flags: JSON_THROW_ON_ERROR);
            $rawPackageData = is_array($decoded)
                ? $decoded['packages'][$package->name][0] ?? []
                : [];
            $packageData = arr(is_array($rawPackageData) ? $rawPackageData : []);

            $versionString = $packageData->get('require.php') ?? $packageData->get('require.php-64bit');

            if (! is_string($versionString) || $versionString === '') {
                // TODO
                continue;
            }

            $minVersion = (new GetMinVersion())($versionString);

            if ($minVersion === null) {
                return;
            }

            $package->versionString = $versionString;
            $package->minVersion = $minVersion;
            $time = $packageData->get('time');

            if (! is_string($time) || $time === '') {
                continue;
            }

            $package->lastReleasedAt = DateTime::parse($time);
            $package->checkedAt = DateTime::now();
            $package->save();

            $this->success("Package {$package->name} parsed");
        }
    }
}
