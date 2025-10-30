<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Map</title>

    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="">
<livewire:map :seed="$seed"/>
@livewireScripts
</body>
</html>
