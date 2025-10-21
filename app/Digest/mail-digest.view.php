There are some new comments.

<div :foreach="$comments as $comment" style="padding: 10px; border-radius: 5px; background-color: #eee; margin-top: 1em">
    <div style="font-weight: bold">
        {{ $comment->content }}
    </div>
    <div>
        By {{ $comment->user->name }} — <a :href="$comment->uri">show</a>
    </div>
</div>

