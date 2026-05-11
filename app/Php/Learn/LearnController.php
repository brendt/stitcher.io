<?php

namespace App\Php\Learn;

use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\Support\str;
use function Tempest\View\view;

final readonly class LearnController
{
    public function __construct(
        private MarkdownConverter $markdown,
    ) {}

    #[Get('/php/learn')]
    public function index(): Redirect
    {
        return new Redirect(uri([self::class, 'show'], chapter: 'getting-started'));
    }


    #[Get('/php/learn/{chapter}')]
    public function show(string $chapter): View
    {
        $chapters = $this->getChapters();

        $chapter = $this->parse($chapters->filter(fn (LearnChapter $search) => $search->slug === $chapter))->first();

        return view('php-learn.view.php', chapters: $chapters, chapter: $chapter);
    }



    private function getChapters(): ImmutableArray
    {
        return arr(glob(__DIR__ . '/Content/*.md'))
            ->map(function (string $path) {
                $content = file_get_contents($path);
                preg_match('/(?<index>\d+)-(?<slug>.*)\.md/', $path, $matches);
                $frontMatter = YamlFrontMatter::parse($content)->matter();

                $index = $matches['index'];
                $slug = $matches['slug'];

                return new LearnChapter(
                    index: $index,
                    slug: $slug,
                    title: $frontMatter['title'] ?? str($slug)->replace('-', ' ')->upperFirst()->toString(),
                    content: $content,
                );
            });
    }

    private function parse(ImmutableArray $chapters): ImmutableArray
    {
        return $chapters->map(function (LearnChapter $chapter) {
            $chapter->content = $this->markdown->convert($chapter->content)->getContent();

            return $chapter;
        });
    }
}