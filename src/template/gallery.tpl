{extends 'index.tpl'}

{block 'content'}
    {$content}

    <div class="gallery">
        {foreach $images.gallery as $image}
            {if !isset($image.src)}
                {continue}
            {/if}

            <img src="{$image.src}" srcset="{$image.srcset}" sizes="(max-width: 1000px) 100vw, calc(960px / 3)" alt="">
        {/foreach}
    </div>
{/block}
