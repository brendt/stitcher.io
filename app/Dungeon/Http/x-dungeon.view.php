<html>
<head>
    <title>Dungeon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fontdiner+Swanky&display=swap" rel="stylesheet">
    <x-vite-tags entrypoint="app/Dungeon/Http/dungeon.entrypoint.css"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <x-slot name="head" />

    <link rel="apple-touch-icon" sizes="180x180" href="/dungeon/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/dungeon/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/dungeon/favicon/favicon-16x16.png">
    <link rel="manifest" href="/dungeon/favicon/site.webmanifest">
</head>
<body class="bg-gray-700 text-white">

<a href="https://github.com/brendt/stitcher.io/issues/new" target="_blank" class="group z-[100] left-0 bottom-0 md:bottom-auto md:top-0 fixed p-2 px-4 bg-purple-800 shadow-xl rounded-lg border-2 border-purple-600 title m-2 hover:bg-purple-700 hover:border-purple-500 transition-all flex items-center gap-2">
    <span>Beta</span>
    <span class="text-xs text-purple-300 max-w-0 overflow-hidden group-hover:max-w-xs transition-all duration-300 whitespace-nowrap">— Click to report an issue</span>
</a>
<x-slot/>
</body>
</html>