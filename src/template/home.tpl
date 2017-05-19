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
        <a href="/guide/setting-up" class="cta cta--ghost">Setting up</a>

        {include 'helper/curve.tpl'}
    </header>

    <div class="container">
        <div class="content__why">
            {$content_why}
        </div>

        {$content_installation}

        {*<hr>*}

        {*<div class="content__news">*}
            {*<h2>News <em>and</em> blog posts</h2>*}
            {*{foreach $news as $post}*}
                {*{call blog post=$post small=true}*}
            {*{/foreach}*}

            {*{foreach $blog as $post}*}
                {*{call blog post=$post small=true}*}
            {*{/foreach}*}
        {*</div>*}
    </div>
{/block}

{block 'footer'}
    <footer class="footer__large">
        <div class="container">
            <div class="left">
                &copy; {date('Y', time())}
            </div>
            <div class="right">
                <a href="https://www.github.com/brendt/stitcher" target="_blank" rel="noopener noreferrer">GitHub</a>
            </div>
        </div>
    </footer>
{/block}

{block 'scripts' append}
    {js src='codeClick.js' inline=true}
{/block}
