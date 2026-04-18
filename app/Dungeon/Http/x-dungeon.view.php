<html>
<head>
    <title>Dungeon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fontdiner+Swanky&display=swap" rel="stylesheet">
    <x-vite-tags entrypoint="app/Dungeon/Http/dungeon.entrypoint.css"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <x-slot name="head" />
</head>
<body class="bg-gray-700 text-white">

<div class="z-[100] left-0 bottom-0 md:bottom-auto md:top-0 fixed p-2 px-4 bg-purple-800 shadow-xl rounded-lg border-2 border-purple-600 title m-2">Beta</div>
<x-slot/>
</body>
</html>