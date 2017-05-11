{extends 'index.tpl'}

{block 'content_wide'}
    <div class="guide">
        <nav>
            {foreach $pages as $page}
                <a href="/guide/{$page.id}">{$page.title}</a>
            {/foreach}
        </nav>
        <article>
            {if isset($page.content)}
                {$page.content}
            {/if}
        </article>
    </div>

{/block}

{block 'footer'}
    <div class="wrapper">
        {if isset($pagination.prev)}
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
