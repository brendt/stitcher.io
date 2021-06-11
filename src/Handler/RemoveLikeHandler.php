<?php

namespace Brendt\Stitcher\Handler;

use Brendt\Stitcher\LikeRepository;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class RemoveLikeHandler
{
    public function handle(Request $request, $slug, $likeId): Response
    {
        $likes = LikeRepository::make();

        $likes
            ->remove($slug, $likeId)
            ->persist();

        return new Response(200, [], $likes->count($slug));
    }
}
