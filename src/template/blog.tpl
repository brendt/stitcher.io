{extends 'index.tpl'}

{include 'helper/render.tpl'}

{block 'content'}
    <h2 class="spacer">Blog</h2>
    {foreach $posts as $post}
        {call blog post=$post tag=true}
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
