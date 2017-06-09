{function blog post=null url='/blog' tag=false small=false}
    <article class="overview {if $small}small{/if}">
        <a href="{$url}/{$post.id}" class="link-hidden">
            {if isset($post.title)}
                <h1 {if $tag && isset($post.type) && $post.type === 'news'}class="news"{/if}>
                    {$post.title}

                    {if $post.type === 'piece of code'}
                        <span class="badge--piece-of-code">
                            Piece of Code
                        </span>
                    {/if}
                </h1>
            {/if}
            {if isset($post.date)}
                <em>{$post.date|date_format:'%Y-%m-%d'}</em>
            {/if}

            {if isset($post.teaser)}
                <div>
                    <p>
                        {$post.teaser}
                    </p>
                </div>
            {else if isset($post.content)}
                <div>
                    <p>
                        {$post.content|strip_tags|truncate:250}
                    </p>
                </div>
            {/if}

            <span class="link">
                Read more
            </span>
        </a>
    </article>
{/function}
