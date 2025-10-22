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
<div class="w-full h-full flex justify-center items-center relative">
    <div class="w-full h-full bg-white text-primary flex justify-around items-center relative shadow-lg px-8">
        <x-slot/>

        <div class="absolute bottom-0 mb-3 text-xs">stitcher.io</div>
    </div>
</div>
</body>
</html>
