<?php

namespace App\Click;

use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;

use function Tempest\defer;

final readonly class ClickController
{
    private const array URIS = [
        'tdw1' => 'https://tideways.com/?utm_medium=partner&utm_source=stitcherio&utm_campaign=speed-up-application-with-performance-insights',
        'tdw2' => 'https://tideways.com/?utm_medium=partner&utm_source=stitcherio&utm_campaign=performance-insights-every-request',
        'ploi' => 'https://ploi.io/?ref=stitcher.io',
        'ohdear' => 'https://ohdear.app/for/php-developers?utm_source=stitcher&utm_medium=sponsorship&utm_campaign=stitcher-sidebar-2026-08',
    ];

    #[Get('/click/{id}')]
    public function click(string $id): Redirect|NotFound
    {
        $uri = self::URIS[$id] ?? null;

        if (! $uri) {
            return new NotFound();
        }

        defer(function () use ($uri) {
            $click = Click::findOrNew([
                'uri' => $uri,
            ], []);

            $click->clicks += 1;
            $click->save();
        });

        return new Redirect($uri);
    }
}
