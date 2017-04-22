<html>
    <head>
        {block 'head'}
            <title>{block 'title'}{if isset($title)}{$title} - {/if}Stitcher 1.0{/block}</title>
            {block 'meta'}
                {meta}
            {/block}
            {css src="main.scss" inline=true}
        {/block}
    </head>
    <body>
        {block 'ribbon'}
            <a href="https://github.com/brendt/stitcher" target="_blank" rel="nofollow noopener"><img style="position: absolute; z-index:99;top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/652c5b9acfaddf3a9c326fa6bde407b87f7be0f4/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6f72616e67655f6666373630302e706e67" alt="Fork Stitcher on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png"></a>
        {/block}
        {block 'body'}
            {block 'header'}
                <header>
                    <nav class="wrapper">
                        <a href="/" class="stitcher">Stitcher</a>
                        <a href="/guide">Guide</a>
                        <a href="/blog">News &amp; blogposts</a>
                    </nav>
                </header>
            {/block}

            {block 'title'}
                {if isset($title)}
                    <div class="wrapper">
                        <h2>{$title}</h2>
                    </div>
                {/if}
            {/block}

            <div class="wrapper">
                {block 'content'}{/block}
            </div>

            <footer>
                {block 'footer'}{/block}
            </footer>

            {block 'scripts'}{/block}
        {/block}
    </body>
</html>
