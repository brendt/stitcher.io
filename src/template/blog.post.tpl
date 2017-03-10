{extends 'index.tpl'}

{block 'content'}
    <article class="blog">
        {if isset($post.image)}
            <img src="{$post.image.src}" srcset="{$post.image.srcset}" sizes="50vw" alt="{$post.image.title}">
        {/if}
        <h1>{$post.title}</h1>

        {$post.content}
    </article>
{/block}

{block 'footer'}
    <div class="wrapper">
        <a class="prev" href="/blog">Back</a>
        {if isset($post.next)}
            <a class="next" href="/blog/{$post.next.id}">Next: {$post.next.title}</a>
        {/if}
    </div>
{/block}

{block 'scripts'}
    {js src="js/codeClick.js" inline=true}
{/block}
