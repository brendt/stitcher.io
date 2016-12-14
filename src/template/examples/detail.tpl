{extends 'index.tpl'}

{block 'content'}
    <h2>{$example.title}</h2>

    {if isset($example.image)}
        <img src="{$example.image.src}" srcset="{$example.image.srcset}" {if isset($example.image.alt)}alt="{$example.image.alt}"{/if}>
    {/if}

    {$example.body}

    <a href="/examples">Back</a>
{/block}
