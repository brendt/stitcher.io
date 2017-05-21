{extends 'index.tpl'}

{block 'head' append}
    {css src='guide.scss' inline=true}
    {css src='hljs-github.css' inline=true}
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
        <article id="read">
            <h1>{$page.title}</h1>
            {if isset($page.content)}
                {$page.content}
            {/if}
        </article>
    </div>
{/block}

{block 'footer'}
    <footer>
        <div class="container">
            <nav>
                {if $browse.prev}
                    <a class="prev cta cta--ghost" href="/guide/{$browse.prev.id}#read">Previous{if isset($browse.prev.title)}: {$browse.prev.title|strtolower}{/if}</a>
                {/if}
                {if $browse.next}
                    <a class="next cta cta--ghost" href="/guide/{$browse.next.id}#read">Next{if isset($browse.next.title)}: {$browse.next.title|strtolower}{/if}</a>
                {/if}
            </nav>
        </div>
    </footer>
{/block}

{block 'scripts'}
    {js src="js/codeClick.js" inline=true}
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.11.0/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
{/block}
