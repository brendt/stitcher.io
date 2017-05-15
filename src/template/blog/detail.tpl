{extends 'index.tpl'}

{block 'meta'}
    {$description = $post.content|strip_tags|truncate:150}
    {if isset($post.description)}
        {$description = $post.description}
    {/if}

    {meta meta=['description' => $description, 'og:description' => $description]}
{/block}

{block 'content'}
    <div class="container">
        <article class="blog">
            {if isset($post.image)}
                <img src="{$post.image.src}" srcset="{$post.image.srcset}" sizes="50vw" alt="{$post.image.title}">
            {/if}
            <h1>{$post.title}</h1>

            {$post.content}
        </article>
    </div>
{/block}

{block 'footer'}
    <div class="container container--content">
        <nav>
            {if $browse.prev}
                <a class="prev" href="/blog/{$browse.prev.id}">Previous: {$browse.prev.title}</a>
            {else}
                <a class="prev" href="/blog">Back to overview</a>
            {/if}

            {if $browse.next}
                <a class="next" href="/blog/{$browse.next.id}">Continue reading: {$browse.next.title}</a>
            {/if}
        </nav>
    </div>
{/block}

{block 'scripts'}
    {js src="js/codeClick.js" inline=true}
{/block}
