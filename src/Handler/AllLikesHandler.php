<?php

namespace Brendt\Stitcher\Handler;

use Brendt\Stitcher\LikeRepository;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class AllLikesHandler
{
    public function handle(Request $request): Response
    {
        $likes = LikeRepository::make();

        $all = $likes->all();

        return new Response(200, [
            'content-type' => 'application/json',
        ], json_encode(
            [
                'total' => collect($all)->flatten()->count(),
                'data' => $all,
            ],
            JSON_PRETTY_PRINT
        ));
    }
}
