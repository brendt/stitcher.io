{extends 'index.tpl'}

{block 'content'}
    {if isset($content)}
        {$content}
    {/if}
{/block}

{block 'footer'}
    <div class="wrapper">
        {if isset($prevUrl)}
            <a class="prev" href="{$prevUrl}">Previous{if isset($prevTitle)}: {$prevTitle|strtolower}{/if}</a>
        {/if}
        {if isset($nextUrl)}
            <a class="next" href="{$nextUrl}">Next{if isset($nextTitle)}: {$nextTitle|strtolower}{/if}</a>
        {/if}
    </div>
{/block}

{block 'scripts'}
    {js src="js/codeClick.js" inline=true}
{/block}
