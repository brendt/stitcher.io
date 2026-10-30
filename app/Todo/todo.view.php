<!doctype html>
<html lang="en" class="bg-white">
<head>
    <title :if="$workspace">{{ $workspace->name }} | Todo</title>
    <title :else>Todo</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <meta name="theme-color" content="#ffffff">

    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">

    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
</head>
<body class="antialiased bg-white">
<textarea
    id="todo-markdown"
    name="markdown"
    class="block w-full max-w-[80ch] min-h-dvh mx-auto px-6 py-12 bg-white font-mono text-sm resize-none overflow-hidden focus:outline-none"
    placeholder="# Project name&#10;&#10;- Todo 1&#10;- Todo 2"
    autofocus
>{{ $markdown }}</textarea>

<script>
    const textarea = document.querySelector('#todo-markdown');

    const grow = () => {
        const scrollY = window.scrollY;

        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';

        window.scrollTo(0, scrollY);
    };

    textarea.addEventListener('input', grow);
    window.addEventListener('resize', grow);
    grow();
</script>
</body>
</html>
