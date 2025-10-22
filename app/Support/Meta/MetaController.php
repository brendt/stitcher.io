<?php

namespace App\Support\Meta;

use HeadlessChromium\BrowserFactory;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\File;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\View\ViewRenderer;
use function Tempest\Router\uri;
use function Tempest\view;

final class MetaController
{
    #[Get('/_meta')]
    public function __invoke(Request $request, ViewRenderer $viewRenderer): Response
    {
        if ($request->has('html')) {
            $html = $viewRenderer->render(view('meta.view.php'));

            return new Ok($html);
        }

        $path = __DIR__ . '/meta.png';

        $browser = new BrowserFactory()->createBrowser([
//            'windowSize' => [1200, 628],
        ]);

        try {
            $page = $browser->createPage();

            $page->setDeviceMetricsOverride([
//                'width' => 1200,
//                'height' => 628,
//                'deviceScaleFactor' => 1,
            ]);

            $page->navigate(uri(self::class, html: true))->waitForNavigation();

            $screenshot = $page->screenshot([
                'captureBeyondViewport' => true,
                'clip' => $page->getFullPageClip(),
            ]);

            $screenshot->saveToFile($path);
        } finally {
            $browser->close();
        }

        return new File($path);
    }
}