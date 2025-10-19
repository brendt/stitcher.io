<p><iframe width="560" height="422" src="https://www.youtube.com/embed/aGspz-sBkyI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>

I know it looks strange the first time you see it, but hear me out for a minute: I am a code folder.

```php
class TwitterSyncCommand extends Command
{
    protected <hljs prop>$signature</hljs> = 'twitter:sync {--clean}';

    /** @var \Illuminate\Database\Eloquent\Collection<Mute> */
    private <hljs type>Collection</hljs> <hljs prop>$mutes</hljs>;

    public function <hljs prop>handle</hljs>(<hljs type>Twitter</hljs> $twitter) { /* … */ }

    public function <hljs prop>syncFromList</hljs>(<hljs type>Twitter</hljs> $twitter): void { /* … */ }

    public function syncFromSearch(<hljs type>Twitter</hljs> $twitter): void { /* … */ }

    private function storeTweets(<hljs type>array</hljs> $tweets, <hljs type>TweetFeedType</hljs> $feedType): void { /* … */ }

    private function shouldBeRejected(<hljs type>Tweet</hljs> $tweet): ?RejectionReason { /* … */ }
}
```

I hide most of my code, most of the time. I have keyboard shortcuts to easily show and hide blocks of code; and when I open a file, all method and function bodies are collapsed by default.

The reason? I’m not a superhuman speed reader that understands dozens of lines of code at one glance. And… I also don’t have a two-metre-high screen.

I just can’t read and understand all of this — you know?

```php
class TwitterSyncCommand extends Command
{
    protected <hljs prop>$signature</hljs> = 'twitter:sync {--clean}';

    /** @var \Illuminate\Database\Eloquent\Collection<Mute> */
    private <hljs type>Collection</hljs> <hljs prop>$mutes</hljs>;

    public function <hljs prop>handle</hljs>(<hljs type>Twitter</hljs> $twitter)
    {
        $this-><hljs prop>mutes</hljs> = <hljs type>Mute</hljs>::<hljs prop>query</hljs>()-><hljs prop>select</hljs>('text')-><hljs prop>get</hljs>();

        if ($this-><hljs prop>option</hljs>('clean')) {
            $this-><hljs prop>error</hljs>('Truncating tweets!');

            <hljs type>Tweet</hljs>::<hljs prop>truncate</hljs>();
        }

        $this-><hljs prop>syncFromSearch</hljs>($twitter);

        $this-><hljs prop>syncFromList</hljs>($twitter);

        $this-><hljs prop>info</hljs>('Done');
    }

    public function <hljs prop>syncFromList</hljs>(<hljs type>Twitter</hljs> $twitter): void
    {
        do {
            $lastTweet = <hljs type>Tweet</hljs>::<hljs prop>query</hljs>()
                -><hljs prop>where</hljs>('feed_type', <hljs type>TweetFeedType</hljs>::<hljs prop>LIST</hljs>)
                -><hljs prop>orderByDesc</hljs>('tweet_id')
                -><hljs prop>first</hljs>();

            $tweets = $twitter-><hljs prop>request</hljs>('lists/statuses.json', 'GET', [
                'list_id' => <hljs prop>config</hljs>('services.twitter.list_id'),
                'since_id' => $lastTweet?-><hljs prop>tweet_id</hljs>,
                'count' => 200,
                'tweet_mode' => 'extended',
            ]);

            $count = <hljs prop>count</hljs>($tweets);

            if ($count === 0) {
                $this-><hljs prop>comment</hljs>('No more new tweets');
            } else {
                $this-><hljs prop>comment</hljs>("Syncing {$count} tweets from list");

                $this-><hljs prop>storeTweets</hljs>($tweets, <hljs type>TweetFeedType</hljs>::<hljs prop>LIST</hljs>);
            }
        } while ($tweets !== []);
    }

    public function syncFromSearch(<hljs type>Twitter</hljs> $twitter): void
    {
        do {
            $lastTweet = <hljs type>Tweet</hljs>::<hljs prop>query</hljs>()
                -><hljs prop>where</hljs>('feed_type', <hljs type>TweetFeedType</hljs>::<hljs prop>SEARCH</hljs>)
                -><hljs prop>orderByDesc</hljs>('tweet_id')
                -><hljs prop>first</hljs>();

            $tweets = $twitter-><hljs prop>request</hljs>('/search/tweets.json', 'GET', [
                'q' => 'phpstorm',
                'since_id' => $lastTweet?-><hljs prop>tweet_id</hljs>,
                'count' => 200,
                'tweet_mode' => 'extended',
            ])-><hljs prop>statuses</hljs>;

            $count = <hljs prop>count</hljs>($tweets);

            if ($count === 0) {
                $this-><hljs prop>comment</hljs>('No more new tweets');
            } else {
                $this-><hljs prop>comment</hljs>("Syncing {$count} tweets from search");

                $this-><hljs prop>storeTweets</hljs>($tweets, <hljs type>TweetFeedType</hljs>::<hljs prop>SEARCH</hljs>);
            }
        } while ($tweets !== []);
    }

    private function storeTweets(<hljs type>array</hljs> $tweets, <hljs type>TweetFeedType</hljs> $feedType): <hljs type>void</hljs>
    {
        foreach ($tweets as $tweet) {
            $subject = $tweet-><hljs prop>retweeted_status</hljs> ?? $tweet;

            $tweet = <hljs type>Tweet</hljs>::<hljs prop>updateOrCreate</hljs>([
                'tweet_id' => $tweet->id,
            ], [
                'state' => <hljs type>TweetState</hljs>::<hljs prop>PENDING</hljs>,
                'feed_type' => $feedType,
                'text' => $subject-><hljs prop>full_text</hljs> ,
                'user_name' => $subject-><hljs prop>user</hljs>-><hljs prop>screen_name</hljs>,
                'retweeted_by_user_name' => isset($tweet-><hljs prop>retweeted_status</hljs>)
                    /** @phpstan-ignore-next-line  */
                    ? $tweet-><hljs prop>user</hljs>-><hljs prop>screen_name</hljs>
                    : null,
                'created_at' => <hljs type>Carbon</hljs>::<hljs prop>make</hljs>($subject-><hljs prop>created_at</hljs>),
                'payload' => <hljs prop>json_encode</hljs>($tweet),
            ]);

            if ($reason = $this-><hljs prop>shouldBeRejected</hljs>($tweet)) {
                $tweet-><hljs prop>update</hljs>([
                    'state' => <hljs type>TweetState</hljs>::<hljs prop>REJECTED</hljs>,
                    'rejection_reason' => $reason-><hljs prop>message</hljs>,
                ]);
            }

            (new <hljs type>ParseTweetText</hljs>)($tweet);
        }
    }

    private function shouldBeRejected(<hljs type>Tweet</hljs> $tweet): ?<hljs type>RejectionReason</hljs>
    {
        if ($tweet-><hljs prop>isRetweet</hljs>() && $tweet-><hljs prop>feed_type</hljs> === <hljs type>TweetFeedType</hljs>::SEARCH) {
            return <hljs type>RejectionReason</hljs>::<hljs prop>retweetedFromSearch</hljs>();
        }

        // Reject tweets containing a specific word
        foreach ($this-><hljs prop>mutes</hljs> as $mute) {
            if ($tweet-><hljs prop>containsPhrase</hljs>($mute-><hljs prop>text</hljs>)) {
                return <hljs type>RejectionReason</hljs>::<hljs prop>mute</hljs>($mute-><hljs prop>text</hljs>);
            }
        }

        // Reject replies
        if ($tweet-><hljs prop>getPayload</hljs>()-><hljs prop>in_reply_to_status_id</hljs>) {
            return <hljs type>RejectionReason</hljs>::<hljs prop>isReply</hljs>();
        }

        // Reject mentions
        if (<hljs prop>str_starts_with</hljs>($tweet-><hljs prop>text</hljs>, '@')) {
            return <hljs type>RejectionReason</hljs>::<hljs prop>isMention</hljs>();
        }

        // Reject non-english tweets
        $language = $tweet-><hljs prop>getPayload</hljs>()-><hljs prop>lang</hljs>;

        if ($language !== 'en') {
            return <hljs type>RejectionReason</hljs>::<hljs prop>otherLanguage</hljs>($language);
        }

        return null;
    }
}
```



I feel overwhelmed looking at all that code, especially when I’m searching for one specific method.

Now, when I create a new file, it’s all fine, it’s a blank slate, and I’ve got control. But code grows rapidly, and real life projects more often than not include work in existing files instead of blank slates. So I really need a way to reduce cognitive load, I can’t “take it all in at once”. And that’s why I’ve grown to like code folding so much.

Give yourself a week to get used to it. Configure your IDE to automatically fold method bodies and functions, and assign proper key bindings to show and hide blocks. I promise you, you’ll like it.


<p><iframe width="560" height="422" src="https://www.youtube.com/embed/aGspz-sBkyI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>