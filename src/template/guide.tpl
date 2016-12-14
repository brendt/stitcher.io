{extends 'index.tpl'}

{block 'content'}
    {$content}

    <ul>
        <li><a href="/guide/setting-up">Setting up</a></li>
        <li><a href="/guide/project-structure">Project Structure</a></li>
        <li><a href="/guide/working-with-data">Working with data</a></li>
        <li><a href="/guide/working-with-images">Working with images</a></li>
        <li><a href="/guide/helper-functions">Helper functions</a></li>
    </ul>
{/block}

{block 'scripts'}
    {js src="js/codeClick.js" inline=true}
{/block}
