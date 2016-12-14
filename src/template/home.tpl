{extends 'index.tpl'}

{block 'head' append}
    {css src='home.scss' inline=true}
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
            <a class="button" href="./guide">Read the guide</a>
            <em class="button-link">or</em>
            <a class="button" href="./examples">Show examples</a>
        </div>
    </div>
{/block}
