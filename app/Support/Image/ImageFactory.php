<?php

namespace App\Support\Image;

use Intervention\Image\ImageManager;
use Intervention\Image\Image as ScalableImage;
use Tempest\CommandBus\Async;
use Tempest\CommandBus\CommandHandler;
use function Tempest\command;
use function Tempest\Support\path;

final readonly class ImageFactory
{
    public function __construct(
        private ImageManager $imageManager,
    ) {}

    #[CommandHandler]
    public function onScaleImage(ScaleImage $command): void
    {
        $image = $command->image;

        $scalableImage = $this->imageManager->read($image->srcPath);

        foreach ($image->srcset as $srcset) {
            $scalableImage = $scalableImage
                ->resize($srcset->width, $srcset->height)
                ->save($srcset->publicPath);
        }
    }

    public function create(string $src): ?Image
    {
        $image = new Image($src);

        if (! is_file($image->srcPath)) {
            return null;
        }

        if ($image->isScalable) {
            foreach ($this->getVariations($image) as $srcset) {
                $image->srcset[] = $srcset;
            }
        }

        if (is_file($image->publicPath)) {
            return $image;
        }

        if ($image->isScalable) {
            command(new ScaleImage($image));
        }

        $dir = pathinfo($image->publicPath, PATHINFO_DIRNAME);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        copy($image->srcPath, $image->publicPath);

        return $image;
    }

    private function getVariations(Image $image): array
    {
        $fileSize = filesize($image->srcPath);

        $scalableImage = $this->imageManager->read($image->srcPath);

        $width = $scalableImage->width();

        $ratio = $scalableImage->height() / $width;
        $area = $width * $width * $ratio;
        $pixelPrice = $fileSize / $area;

        $stepAmount = $fileSize * 0.3;

        $variations = [];

        $pathInfo = pathinfo($image->src);
        $baseSrc = path($pathInfo['dirname'], $pathInfo['filename'])->toString();
        $extension = $pathInfo['extension'];

        do {
            $newWidth = (int)floor(sqrt(($fileSize / $pixelPrice) / $ratio));
            $newHeight = (int)floor($newWidth * $ratio);

            $variations[] = new SrcSet(
                src: "{$baseSrc}-{$newWidth}-{$newHeight}.{$extension}",
                width: $newWidth,
                height: $newHeight,
            );

            $fileSize -= $stepAmount;
        } while ($fileSize > 0);

        return $variations;
    }
}