<?php

namespace App\Support\Sitemap;

use Tempest\Container\Container;
use Tempest\Http\Method;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\RouteConfig;
use Tempest\Router\StaticPage;
use function Tempest\Router\uri;
use function Tempest\view;

final class SitemapController
{
    #[Get('/sitemap.xml')]
    public function __invoke(RouteConfig $routeConfig, Container $container): Response
    {
        $sitemap = new Sitemap();

        foreach ($routeConfig->staticRoutes[Method::GET->value] as $route) {
            $uri = uri($route->handler);

            $sitemap->uris[$uri] = $uri;
        }

        foreach ($routeConfig->dynamicRoutes[Method::GET->value] as $route) {
            /** @var StaticPage $staticPage */
            $staticPage = $route->handler->getAttribute(StaticPage::class);

            if (! $staticPage?->dataProviderClass) {
                continue;
            }

            /** @var \Tempest\Router\DataProvider $dataProvider */
            $dataProvider = $container->get($staticPage->dataProviderClass);

            foreach ($dataProvider->provide() as $params) {
                $uri = uri($route->handler, ...$params);

                $sitemap->uris[$uri] = $uri;
            }
        }

        $body = view(
            'sitemap.view.php',
            sitemap: $sitemap,
        );

        return new Ok($body)->addHeader('Content-Type', 'application/xml;charset=UTF-8');
    }
}