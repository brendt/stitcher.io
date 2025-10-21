<html lang="en">
<head>
    <title>Meta</title>
    <x-vite-tags/>
    <style>
        :root {
            font-size: 40px;
        }
    </style>
</head>
<body class="bg-[#333] flex justify-center items-center">
<div class="w-[1200px] h-[628px] flex justify-center items-center bg-primary relative text-white p-1">
    <div class="w-full h-full bg-white rounded-xs text-primary flex justify-around items-center relative shadow-lg px-8">
        <x-slot/>

        <div class="absolute bottom-0 mb-3 text-xs">stitcher.io</div>
    </div>
</div>
</body>
</html>
