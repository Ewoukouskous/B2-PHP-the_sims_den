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

            <div id="navbar-content" class="grid grid-cols-[repeat(7,1fr)] grid-rows-1 gap-2 h-full">

                <div id="left" class="col-span-3 flex items-center gap-2">

                    <div id="logo" class="absolute top-1/2 transform -translate-y-1/2 text-xl font-bold text-gray-800 p-1">
                        <img src="/public/img/plumbob.webp" alt="website logo" class="w-8 h-8 object-contain">
                    </div>

<!--                    <div id="searchbar">-->
<!--                        <form method="post">-->
<!--                            <input>-->
<!--                        </form>-->
<!--                    </div>-->

                </div>

                <div id="middle" class="col-start-4 flex justify-center">

                </div>

                <div id="right" class="col-start-5 col-span-3 flex flex-row-reverse">

                </div>

            </div>

        </div>
    </div>
</body>
</html>