<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center">
        <img src="img/bg.png" alt="Background Image" class="w-full h-full object-cover">
    </div>

    <div id="content" class="relative flex justify-center h-screen p-4 pt-12">

<!--        NAVBAR Section-->

        <?php
        $basePath = '';
        $showLoginButton = false;
        $showProfilePic = true;
        $searchPlaceholder = 'Recherche :';
        include 'includes/header.php';
        ?>

        <div id="filter" class="absolute top-28 w-full h-12 max-w-7xl p-4">

            <div id="filter-content" class="flex items-center gap-4 h-full">

                <input type="checkbox" id="filter1" class="hidden peer/pc">
                <label for="filter1"
                       class="px-6 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] font-medium outline-none transition duration-200 ease-in-out cursor-pointer border-2 border-transparent peer-checked/pc:bg-[#2a9636] peer-checked/pc:text-white peer-checked/pc:border-white">
                    PC
                </label>

                <input type="checkbox" id="filter2" class="hidden peer/console">
                <label for="filter2"
                       class="px-6 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] font-medium outline-none transition duration-200 ease-in-out cursor-pointer border-2 border-transparent peer-checked/console:bg-[#2a9636] peer-checked/console:text-white peer-checked/console:border-white">
                    Console
                </label>

                <input type="checkbox" id="filter3" class="hidden peer/smartphone">
                <label for="filter3"
                       class="px-6 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] font-medium outline-none transition duration-200 ease-in-out cursor-pointer border-2 border-transparent peer-checked/smartphone:bg-[#2a9636] peer-checked/smartphone:text-white peer-checked/smartphone:border-white">
                    Smartphone
                </label>

            </div>

        </div>

        <div class="absolute top-45 w-full max-w-7xl px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-8">

            <div class="relative group transition-transform duration-300 hover:-translate-y-2">

                <a href="#" class="block bg-white rounded-[2.5rem] p-3 shadow-lg border-b-8 border-[#33b842]">

                    <div class="relative h-48 w-full overflow-hidden rounded-[2rem]">
                        <img src="img/life_and_death.png" alt="Life and Death" class="w-full h-full object-cover">
                    </div>

                    <div class="mt-4 mb-16 text-center">
                        <div class="px-6 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none">
                            temp title
                        </div>
                    </div>

                    <div class="absolute -bottom-6 left-0 w-full flex justify-between items-center p-4 pointer-events-none transform -translate-y-1/2">

                        <div class="flex items-center">
                            <span class="pointer-events-auto bg-[#33b842] text-white text-[10px] font-bold px-4 py-2 rounded-full shadow-[0px_2px_0px_1.5px_rgba(0,0,0,0.1)] border border-white/20 whitespace-nowrap">
                                Pack d'extension
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="relative flex flex-col items-center">
                                <button class="pointer-events-auto bg-[#F0EEE9] p-2 rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] flex items-center justify-center hover:scale-110 transition-transform cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#3769a9]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                                <span class="absolute -bottom-4 text-[9px] font-bold text-[#3769a9]">621</span>
                            </div>

                            <div class="pointer-events-auto bg-[#F0EEE9] px-5 py-1.5 rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-bold text-sm flex items-center h-[36px]">
                                14.99 €
                            </div>
                        </div>

                    </div>

                </a>

            </div>

        </div>

    </div>
</body>
</html>