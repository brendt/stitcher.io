{function blog post=null url='/blog' tag=false}
    <article class="overview">
        <a href="{$url}/{$post.id}" class="link-hidden">
            {if isset($post.title)}
                <h1 {if $tag && isset($post.type) && $post.type === 'news'}class="news"{/if}>
                    {$post.title}
                </h1>
            {/if}
            {if isset($post.date)}
                <em>{$post.date|date_format:'%Y-%m-%d'}</em>
            {/if}
            {if isset($post.content)}
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
