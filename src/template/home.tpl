{extends 'index.tpl'}

{block 'head' append}
    {css src='css/home.scss' inline=true}
{/block}

{block 'header'}{/block}

{block 'body'}
    <div class="heading">
        <h2>
            Welcome to Stitcher
        </h2>
        <h3>
            a tool to create <em>blazing</em> fast websites.
        </h3>

        <div class="vwrapper">
            <a class="button"  href="http://stitcher.pageon.be/guide/setting-up" target="_blank">Getting started</a>
            <em class="button-link">or</em>
            <a class="button" href="http://stitcher.pageon.be/guide" target="_blank">Read the full guide</a>
        </div>
    </div>
{/block}
