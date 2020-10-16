<?php

namespace Brendt\Stitcher\Console;

use Brendt\Stitcher\Console\DTO\BlogPost;
use Illuminate\Support\Collection;
use Stitcher\App;
use Stitcher\File;
use Symfony\Component\Yaml\Yaml;

class BlogRepository
{
    /**
     * @return \Illuminate\Support\Collection|\Brendt\Stitcher\Console\DTO\BlogPost[]
     */
    public function all(): Collection
    {
        /** @var \Symfony\Component\Yaml\Yaml $yaml */
        $yaml = App::get(Yaml::class);

        $posts = $yaml->parse(file_get_contents(File::path('src/content/blog.yaml')));

        return collect($posts)->map(
            fn(array $post, string $id) => BlogPost::make($post, $id)
        );
    }
}
