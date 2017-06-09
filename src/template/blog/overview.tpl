{extends 'index.tpl'}

{include 'helper/render.tpl'}

{block 'head' append}
    {css src='blog.scss' inline=true}
{/block}

{block 'content'}
    <header class="banner banner__blog">
        {include 'helper/curve.tpl'}
    </header>
    <div class="content__blog">
        <div class="container">
            {if isset($title)}
                <h1>{$title}</h1>
            {else}
                <h1>Blog</h1>
            {/if}

            {if isset($intro)}
                <p>
                    {$intro}
                </p>
                <hr>
            {/if}

            {foreach $posts as $post}
                {call blog post=$post tag=true}
            {/foreach}

            <footer>
                <nav>
                    {if isset($pagination.previous)}
                        <a class="prev cta cta--ghost" href="{$pagination.previous.url}">Previous</a>
                    {/if}
                    {if isset($pagination.next)}
                        <a class="next cta cta--ghost" href="{$pagination.next.url}">Next</a>
                    {/if}
                </nav>
            </footer>
        </div>
    </div>
{/block}

{block 'footer'}{/block}
