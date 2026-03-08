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

    <div id="content" class="relative flex justify-center h-screen p-4 pt-12">

<!--        NAVBAR Section-->

        <div id="navbar" class="relative w-full h-12 max-w-7xl p-4 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]">

            <div id="navbar-content" class="grid grid-cols-[repeat(7,1fr)] grid-rows-1 gap-2 h-full">

<!--                LOGO + SEARCHBAR Section-->

                <div id="left" class="col-span-3 flex items-center gap-2">

                    <div id="logo" class="top-1/2 text-xl font-bold text-gray-800 p-1">
                        <img src="/public/img/plumbob.webp" alt="website logo" class="w-8 h-8 object-contain">
                    </div>

                    <div id="searchbar">
                        <form method="post">
                            <label>
                                <input type="text"
                                       name="search"
                                       placeholder="Search..."
                                       class="pl-4 pr-32 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out"
                                       autocomplete="off">
                            </label>
                        </form>
                    </div>

                </div>

<!--                PROFILE PIC Section-->
                
                <div id="middle" class="col-start-4 flex justify-center">

                    <img src="/public/img/temp/profile_pic.webp"
                         class="absolute w-20 h-20 object-cover transform -top-10 border-2 rounded-full"
                         style="border-color: #33b842;"
                         alt="profile picture"
                         title="profile picture">

                </div>

                <div id="right" class="col-start-5 col-span-3 flex flex-row-reverse items-center gap-4">

                    <a href="#">
                        <button type="button"
                                class="px-6 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                            Connexion
                        </button>
                    </a>

                    <a href="#">
                        <button type="button"
                                class="px-6 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                            Ma liste de favoris
                        </button>
                    </a>

                </div>

            </div>

        </div>

        <div id="filter" class="absolute top-28 w-full h-12 max-w-7xl p-4 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]">

            <div id="filter-content" class="flex items-center gap-4 h-full">

                <input type="checkbox" id="filter1" class="hidden peer/pc">
                <label for="filter1"
                       class="px-6 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] font-medium outline-none transition duration-200 ease-in-out cursor-pointer peer-checked/pc:bg-[#2a9636] peer-checked/pc:scale-110 peer-checked/pc:text-white peer-checked/pc:border-2 peer-checked/pc:border-white">
                    PC
                </label>

                <input type="checkbox" id="filter2" class="hidden peer/console">
                <label for="filter2"
                       class="px-6 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] font-medium outline-none transition duration-200 ease-in-out cursor-pointer peer-checked/console:bg-[#2a9636] peer-checked/console:scale-110 peer-checked/console:text-white peer-checked/console:border-2 peer-checked/console:border-white">
                    Console
                </label>

                <input type="checkbox" id="filter3" class="hidden peer/smartphone">
                <label for="filter3"
                       class="px-6 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] font-medium outline-none transition duration-200 ease-in-out cursor-pointer peer-checked/smartphone:bg-[#2a9636] peer-checked/smartphone:scale-110 peer-checked/smartphone:text-white peer-checked/smartphone:border-2 peer-checked/smartphone:border-white">
                    Smartphone
                </label>

            </div>

        </div>

    </div>
</body>
</html>