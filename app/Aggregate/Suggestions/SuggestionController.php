<?php

namespace App\Aggregate\Suggestions;

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
use function Tempest\view;

final readonly class SuggestionController
{
    #[Router\Get('/suggest')]
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
            $feedUri = new FindSuggestionFeedUri()($suggestion->uri);

            if ($feedUri) {
                $suggestion->feedUri = $feedUri;
                $suggestion->save();
            }
        });

        return new Redirect('/?success');
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

    private function render(): View
    {
        $suggestions = Suggestion::select()->all();

        return view(
            'x-suggestions.view.php',
            suggestions: $suggestions,
        );
    }
}