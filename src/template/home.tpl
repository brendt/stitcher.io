{extends 'index.tpl'}

{block 'head' append}
    {css src='home.scss' inline=true}
{/block}

{block 'content'}
    <header class="banner__home">
        <h1>Stitcher</h1>
        <h2>high performance, static websites for PHP developers.</h2>

        <a href="#" class="cta">Install now</a>
        <a href="#" class="cta cta--ghost">Setting up</a>
    </header>

    <div class="container container--content">
        <div class="content__why">
            {$content_why}
        </div>
    </div>
{/block}
