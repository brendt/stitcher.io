{extends 'index.tpl'}

{block 'head' append}
    {css src='blog.scss' inline=true}
{/block}

{block 'content'}
    <header class="banner banner__blog">
        {include 'helper/curve.tpl'}
    </header>
    <div class="content__blog">
        <div class="container">
            <article class="blog">
                {$content}
            </article>
        </div>
    </div>
{/block}
