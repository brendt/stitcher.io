{extends 'index.tpl'}

{include 'helper/render.tpl'}

{block 'header'}
    <header class="home">
        <div class="wrapper">
            <h1>Stitcher :</h1>
            <em>high performance, static websites for PHP developers.</em>
            <div class="vwrapper">
                <a href="#installation" class="btn">Install now</a>
                <a href="/guide/setting-up" class="btn">Setting Up</a>
            </div>
            <div class="vwrapper">
                <a href="/blog" class="highlight">News &amp; blogposts</a>
            </div>
        </div>
        <div class="overlay"></div>
        <img src="{$banner.src}" srcset="{$banner.srcset}" alt="">
    </header>
{/block}

{block 'content'}
    <h2>Why Stitcher?</h2>

    <div class="why">
        {$content_why}
    </div>

    <div class="installation" id="installation">
        {$content_installation}
    </div>

    <hr>

    <h2>News</h2>
    {foreach $news as $newsPost}
        {call blog post=$newsPost}
    {/foreach}
    <h2>Latest blog posts</h2>
    {foreach $blog as $blogPost}
        {call blog post=$blogPost}
    {/foreach}
    <div class="vwrapper">
        <a href="/blog">Read all blogposts</a>
    </div>
{/block}

{block 'scripts'}
    {js src="js/codeClick.js" inline=true}
{/block}
