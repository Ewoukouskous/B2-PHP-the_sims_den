<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center">
        <img src="/public/img/bg.png" alt="Background Image" class="w-full h-full object-cover">
    </div>

    <div id="content" class="relative flex justify-center h-screen p-4">
        <div id="navbar" class="relative w-full h-12 max-w-7xl p-4 bg-white bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]">

            <div id="logo" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-xl font-bold text-gray-800">
                <img src="/public/img/plumbob.webp" alt="website logo" class="w-8 h-8 object-contain">
            </div>
        </div>
    </div>
</body>
</html>