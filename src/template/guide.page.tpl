{extends 'index.tpl'}

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
