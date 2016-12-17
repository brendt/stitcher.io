{extends 'index.tpl'}

{block 'content'}
    {foreach $posts as $id => $post}
        <div>
            {$post.content|truncate:100}
        </div>
        <a href="/blog/{$id}">
            Read more
        </a>
    {/foreach}
{/block}

{block 'footer'}
    <div class="wrapper">
        {if isset($pagination.previous)}
            <a class="prev" href="{$pagination.previous.url}">Previous</a>
        {/if}
        {if isset($pagination.next)}
            <a class="next" href="{$pagination.next.url}">Next</a>
        {/if}
    </div>
{/block}
