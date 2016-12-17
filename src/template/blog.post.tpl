{extends 'index.tpl'}

{block 'content'}
    {$post.content}
{/block}

{block 'footer'}
    <div class="wrapper">
        <a class="prev" href="/blog">Back</a>
    </div>
{/block}
