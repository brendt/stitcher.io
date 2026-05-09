<?php

namespace App\Aggregate\Suggestions;

use Tempest\Router\Get;
use function Tempest\Router\uri;
use App\Aggregate\FeedController;
use App\Aggregate\Posts\Actions\QueuePost;
use App\Aggregate\Posts\Actions\ResolveTitle;
use App\Aggregate\Posts\Post;
use App\Aggregate\Posts\PostState;
use App\Aggregate\Posts\Source;
use App\Aggregate\Posts\SourceState;
use App\Aggregate\Posts\SyncSource;
use App\Support\Authentication\Admin;
use Tempest\DateTime\DateTime;
use Tempest\Http\Request;
use Tempest\Http\Responses\Redirect;
use Tempest\Router;
use Tempest\Router\Stateless;
use Tempest\View\View;
use function Tempest\defer;
use function Tempest\View\view;

final readonly class SuggestionController
{
    public function __construct(
        private ResolveTitle $resolveTitle,
    ) {}

    #[Get('/suggest')]
    public function suggest(): View
    {
        return view('suggest.view.php');
    }

    #[Router\Post('/suggest')]
    public function createSuggestion(CreateSuggestionRequest $request): Redirect
    {
        $suggestion = Suggestion::create(
            uri: $request->suggestion,
            suggestedAt: DateTime::now(),
            suggestedBy: '',
        );

        defer(function () use ($suggestion) {
            if ($title = ($this->resolveTitle)($suggestion->uri)) {
                $suggestion->title = $title;
                $suggestion->save();
            }

            if ($feedUri = new FindSuggestionFeedUri()($suggestion->uri)) {
                $suggestion->feedUri = $feedUri;
                $suggestion->save();
            }
        });

        return new Redirect(uri([FeedController::class, 'home'], success: 1));
    }

    #[Admin, Stateless, Router\Post('/suggestions/deny/{suggestion}')]
    public function deny(Suggestion $suggestion): View
    {
        $suggestion->delete();

        return $this->render();
    }

    #[Admin, Stateless, Router\Post('/suggestions/publish/{suggestion}')]
    public function publish(Suggestion $suggestion, Request $request, SyncSource $syncSource, ResolveTitle $resolveTitle): View
    {
        $publishFeed = $request->has('feed');

        if ($publishFeed) {
            $source = Source::create(
                name: parse_url($suggestion->uri, PHP_URL_HOST),
                uri: $suggestion->feedUri,
                state: SourceState::PUBLISHED,
            );

            $syncSource($source);
        } else {
            $title = $resolveTitle($suggestion->uri);

            Post::create(
                title: $title,
                uri: $suggestion->uri,
                createdAt: DateTime::now(),
                publicationDate: DateTime::now(),
                state: PostState::PUBLISHED,
            );
        }

        $suggestion->delete();

        return $this->render();
    }

    #[Admin, Stateless, Router\Post('/suggestions/queue/{suggestion}')]
    public function queue(Suggestion $suggestion, ResolveTitle $resolveTitle, QueuePost $queuePost): View
    {
        $title = $resolveTitle($suggestion->uri);

        $post = Post::create(
            title: $title,
            uri: $suggestion->uri,
            createdAt: DateTime::now(),
            publicationDate: null,
            state: PostState::PENDING,
        );

        $queuePost($post);

        $suggestion->delete();

        return $this->render();
    }

    private function render(): View
    {
        return view(
            'x-suggestions.view.php',
            suggestions: Suggestion::select()->all(),
            shouldQueue: Post::shouldQueue(),
        );
    }
}