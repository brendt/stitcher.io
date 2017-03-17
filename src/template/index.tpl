<html>
    <head>
        {block 'head'}
            <title>Stitcher 1.0</title>
            {meta}
            {css src="main.scss" inline=true}
        {/block}
    </head>
    <body>
        {block 'body'}
            {block 'header'}
                <header>
                    <nav class="wrapper">
                        <a href="/" class="stitcher">Stitcher</a>
                        <a href="http://stitcher.pageon.be/guide/setting-up" target="_blank">Guide</a>
                    </nav>
                </header>
            {/block}

            <div class="wrapper">
                {block 'content'}{/block}
            </div>

            {block 'footer'}{/block}

            {block 'scripts'}{/block}
        {/block}
    </body>
</html>
