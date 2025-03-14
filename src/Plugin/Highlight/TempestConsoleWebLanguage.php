<?php

declare(strict_types=1);

namespace Brendt\Stitcher\Plugin\Highlight;

use Brendt\Stitcher\Plugin\Highlight\Injections\CommentInjection;
use Brendt\Stitcher\Plugin\Highlight\Injections\DimInjection;
use Brendt\Stitcher\Plugin\Highlight\Injections\EmphasizeInjection;
use Brendt\Stitcher\Plugin\Highlight\Injections\ErrorInjection;
use Brendt\Stitcher\Plugin\Highlight\Injections\H1Injection;
use Brendt\Stitcher\Plugin\Highlight\Injections\H2Injection;
use Brendt\Stitcher\Plugin\Highlight\Injections\QuestionInjection;
use Brendt\Stitcher\Plugin\Highlight\Injections\StrongInjection;
use Brendt\Stitcher\Plugin\Highlight\Injections\SuccessInjection;
use Brendt\Stitcher\Plugin\Highlight\Injections\UnderlineInjection;
use Tempest\Highlight\Language;

final readonly class TempestConsoleWebLanguage implements Language
{
    #[\Override]
    public function getName(): string
    {
        return 'console';
    }

    #[\Override]
    public function getAliases(): array
    {
        return [];
    }

    #[\Override]
    public function getInjections(): array
    {
        return [
            new QuestionInjection(),
            new EmphasizeInjection(),
            new DimInjection(),
            new StrongInjection(),
            new UnderlineInjection(),
            new ErrorInjection(),
            new CommentInjection(),
            new H1Injection(),
            new H2Injection(),
            new SuccessInjection(),
        ];
    }

    #[\Override]
    public function getPatterns(): array
    {
        return [];
    }
}
