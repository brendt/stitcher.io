{extends 'index.tpl'}

{include 'helper/render.tpl'}

{block 'head' append}
    {css src='blog.scss' inline=true}
{/block}

{block 'content'}
    <div class="content__blog container container--content">
        <h2>Blog</h2>
        {foreach $posts as $post}
            {call blog post=$post tag=true}
        {/foreach}
    </div>
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
