{extends 'index.tpl'}

{block 'content'}
    {$content}

    <ul>
        <li><a href="/guide/setting-up">Setting up</a></li>
        <li><a href="/guide/project-structure">Project Structure</a></li>
        <li><a href="/guide/working-with-data">Working with data</a></li>
        <li><a href="/guide/working-with-images">Working with images</a></li>
        <li><a href="/guide/helper-functions">Helper functions</a></li>
    </ul>
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
