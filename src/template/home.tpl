{extends 'index.tpl'}

{include 'helper/render.tpl'}

{block 'head' append}
    {css src='home.scss' inline=true}
{/block}

{block 'content'}
    <header class="banner__home">
        <h1>Stitcher</h1>
        <h2>High performance, static websites for PHP developers.</h2>

        <a href="#install" class="cta cta--primary">Install now</a>
        <a href="#" class="cta cta--ghost">Setting up</a>

        {include 'helper/curve.tpl'}
    </header>

    <div class="container">
        <div class="content__why">
            {$content_why}
        </div>

        {$content_installation}

        <div class="content__news">
            <h2>News <em>and</em> blog posts</h2>
            {foreach $news as $post}
                {call blog post=$post}
            {/foreach}

            {foreach $blog as $post}
                {call blog post=$post}
            {/foreach}
        </div>
    </div>
{/block}

{block 'scripts' append}
    {js src='codeClick.js' inline=true}
{/block}
