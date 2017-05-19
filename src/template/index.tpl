<html>
    <head>
        {block 'head'}
            <title>{block 'title'}{if isset($title)}{$title} - {/if}Stitcher 1.0{/block}</title>
            {block 'meta'}
                <meta property="og:image" content="http://stitcher.pageon.be/img/stitcher.png" />
                <meta property="twitter:image" content="http://stitcher.pageon.be/img/stitcher.png" />
                <meta name="image" content="http://stitcher.pageon.be/img/stitcher.png" />
                {meta}
            {/block}
            {css src="main.scss" inline=true}
        {/block}
    </head>
    <body>
        {block 'body'}
            {block 'nav__main'}
                <nav class="nav__main">
                    <a href="/" {call active category='home'}>Install</a>
                    <a href="/guide/setting-up" {call active category='guide'}>Guide</a>
                    <a href="/blog" {call active category='blog'}>Blog</a>
                </nav>
            {/block}

            {block 'content'}{/block}

            {block 'footer'}{/block}

            {block 'scripts'}{/block}
        {/block}
    </body>
</html>

{function 'active' category=null}
    {if $category && isset($pageCategory) && $pageCategory === $category}
        class="active"
    {/if}
{/function}
