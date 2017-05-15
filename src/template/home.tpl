{extends 'index.tpl'}

{include 'helper/render.tpl'}

{block 'head' append}
    {css src='home.scss' inline=true}
{/block}

{block 'content'}
    <header class="banner__home">
        <h1>Stitcher</h1>
        <h2>High performance, static websites for PHP developers.</h2>

        <a href="#install" class="cta">Install now</a>
        <a href="#" class="cta cta--ghost">Setting up</a>

        <svg id="curve" style="width:100%;;" viewBox="0 0 1440 126" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g id="Desktop-HD-Copy-3" transform="translate(0.000000, -163.000000)" fill="#FFFFFF">
                    <path d="M1440,163 C1439.99979,163.000049 1071.41006,250.769645 720,250.769645 C368.589942,250.769645 0.00020550191,163.000049 8.59309448e-11,163 L0,288.461052 L1440,288.461052 L1440,163 Z" id="Combined-Shape"></path>
                </g>
            </g>
        </svg>
    </header>

    <div class="container">
        <div class="content__why">
            {$content_why}
        </div>

        {$content_installation}

        <div class="content__news">
            <h2>News <em>and</em> blog posts</h2>
            {foreach $news as $post}
                {call blog post=$post}
            {/foreach}

            {foreach $blog as $post}
                {call blog post=$post}
            {/foreach}
        </div>
    </div>
{/block}

{block 'scripts' append}
    {js src='codeClick.js' inline=true}
{/block}
