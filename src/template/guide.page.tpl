{extends 'index.tpl'}

{block 'meta'}
    {if isset($description)}
        {meta meta=['description' => $description, 'og:description' => $description]}
    {else}
        {meta}
    {/if}
{/block}

{block 'content'}
    {if isset($content)}
        {$content}
    {/if}
{/block}

{block 'footer'}
    <div class="wrapper">
        {if isset($prev)}
            <a class="prev" href="{$prev.url}">Previous{if isset($prev.title)}: {$prev.title|strtolower}{/if}</a>
        {/if}
        {if isset($next)}
            <a class="next" href="{$next.url}">Next{if isset($next.title)}: {$next.title|strtolower}{/if}</a>
        {/if}
    </div>
{/block}

{block 'scripts'}
    {js src="js/codeClick.js" inline=true}
{/block}
