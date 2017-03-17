{extends 'index.tpl'}

{block 'content'}
    {foreach $collection as $entry}
        <h2>{$entry.title}</h2>
        <p>{$entry.intro}</p>
        <a href="/examples/{$entry.id}">Read more</a>
    {/foreach}

    <div>
        {if isset($pagination.previous)}
            <a href="{$pagination.previous.url}">Previous page ({$pagination.previous.index})</a>
        {/if}
        {if isset($pagination.next)}
            <a href="{$pagination.next.url}">Next page ({$pagination.next.index})</a>
        {/if}
    </div>
{/block}
