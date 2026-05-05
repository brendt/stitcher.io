<section class="chapter" :class="'chapter-' . $chapter->index" :id="$chapter->slug">
    <h1 class="chapter-title"><a href="{{ $chapter->uri }}">{{ $chapter->title }}</a></h1>

    <h2 class="chapter-date">{{ $chapter->index }} </h2>

    {!! $chapter->body !!}
</section>