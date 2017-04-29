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
        {block 'body'}
            {block 'header'}
                <header>
                    <nav class="wrapper">
                        <a href="/" class="stitcher">Stitcher</a>
                        <a href="/guide">Guide</a>
                        <a href="/blog">News &amp; blogposts</a>
                        <a class="ribbon"
                           href="https://github.com/pageon/stitcher"
                           target="_blank"
                           rel="nofollow noopener">
                            GitHub
                        </a>
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
