<?php

namespace Brendt\Stitcher\Handler;

use Brendt\Stitcher\LikeRepository;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class GetLikesHandler
{
    public function handle(Request $request, $slug, $likeId): Response
    {
        $likes = LikeRepository::make();

        return new Response(200, [], json_encode([
            'like_count' => number_format($likes->count($slug)),
            'has_liked' => $likes->hasLiked($slug, $likeId),
        ]));
    }
}
