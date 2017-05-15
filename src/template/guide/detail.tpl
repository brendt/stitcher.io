{extends 'index.tpl'}

{block 'head' append}
    {css src='guide.scss' inline=true}
{/block}

{block 'content'}
    <div class="content__guide">
        <nav>
            {foreach $menu as $category => $pages}
                {if $category}
                    <h2>{$category}</h2>
                {/if}

                {foreach $pages as $menuPage}
                    <a href="/guide/{$menuPage.id}" {if $menuPage.title === $page.title}class="active"{/if}>{$menuPage.title}</a>
                {/foreach}
            {/foreach}
        </nav>
        <article>
            <h1>{$page.title}</h1>
            {if isset($page.content)}
                {$page.content}
            {/if}
        </article>
    </div>
{/block}

{block 'footer'}
    <div class="wrapper">
        {if $browse.prev}
            <a class="prev" href="/guide/{$browse.prev.id}">Previous{if isset($browse.prev.title)}: {$browse.prev.title|strtolower}{/if}</a>
        {/if}
        {if $browse.next}
            <a class="next" href="/guide/{$browse.next.id}">Next{if isset($browse.next.title)}: {$browse.next.title|strtolower}{/if}</a>
        {/if}
    </div>
{/block}

{block 'scripts'}
    {js src="js/codeClick.js" inline=true}
{/block}
