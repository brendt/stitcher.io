{extends 'index.tpl'}

{include 'helper/render.tpl'}

{block 'header'}
    <header class="home">
        <div class="wrapper">
            <h1>Stitcher :</h1>
            <em>high performance, static websites for PHP developers.</em>
            <div class="vwrapper">
                <a href="/guide" class="btn">read the guide</a>
                <a href="#read" class="btn">news</a>
            </div>
            <div class="vwrapper">
                <a href="/blog" class="highlight">Read all blogposts</a>
            </div>
        </div>
        <div class="overlay"></div>
        <img src="{$banner.src}" srcset="{$banner.srcset}" alt="">
    </header>
{/block}

{block 'content'}
    <a name="read"></a>
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
