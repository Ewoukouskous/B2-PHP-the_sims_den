<?php ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
<div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center">
    <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover">
</div>

<div id="content" class="relative flex flex-col items-center min-h-screen p-3 pt-8">

    <!--        NAVBAR Section-->

    <?php
    $basePath = '../';
    $showLoginButton = true;
    $showProfilePic = false;
    $searchPlaceholder = 'Recherche :';
    include '../includes/header.php';
    ?>

    <!--        REGISTRATION FORM Section-->

    <div id="registration-container" class="relative w-full max-w-6xl mt-12">

        <!--            TITLE (positioned above the container)-->
        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 z-10">
            <h1 class="text-4xl font-bold text-[#3769a9] bg-[#F0EEE9] inline-block px-12 py-3 rounded-full border-4 border-[#33b842] shadow-lg whitespace-nowrap">
                Inscription
            </h1>
        </div>

        <div class="bg-[#F0EEE9] bg-opacity-95 rounded-[3rem] pt-24 px-8 pb-8 shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

                <!--                LEFT SIDE - FORM-->
                <div class="space-y-4">

                    <form method="post" action="" class="space-y-4">

                        <!--                        EMAIL FIELD-->
                        <div>
                            <label for="email" class="block text-lg font-medium text-[#3769a9] mb-1">
                                Adresse Courriel<span class="text-red-500">*</span> :
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   placeholder="SulSul23400@sims.com"
                                   required
                                   class="w-full px-5 py-2 bg-white rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] placeholder:opacity-50 text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out">
                        </div>

                        <!--                        USERNAME FIELD-->
                        <div>
                            <label for="username" class="block text-lg font-medium text-[#3769a9] mb-1">
                                Pseudonyme<span class="text-red-500">*</span> :
                            </label>
                            <input type="text"
                                   id="username"
                                   name="username"
                                   placeholder="SulSul23400"
                                   required
                                   class="w-full px-5 py-2 bg-white rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] placeholder:opacity-50 text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out">
                        </div>

                        <!--                        PASSWORD FIELD-->
                        <div>
                            <label for="password" class="block text-lg font-medium text-[#3769a9] mb-1">
                                Mot de passe<span class="text-red-500">*</span> :
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="****************"
                                   required
                                   class="w-full px-5 py-2 bg-white rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] placeholder:opacity-50 text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out">
                        </div>

                        <!--                        CONFIRM PASSWORD FIELD-->
                        <div>
                            <label for="confirmPassword" class="block text-lg font-medium text-[#3769a9] mb-1">
                                Confirmation mot de passe<span class="text-red-500">*</span> :
                            </label>
                            <input type="password"
                                   id="confirmPassword"
                                   name="confirmPassword"
                                   placeholder="****************"
                                   required
                                   class="w-full px-5 py-2 bg-white rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] placeholder:opacity-50 text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out">
                        </div>

                        <!--                        ALREADY HAVE ACCOUNT LINK-->
                        <div class="text-center pt-1">
                            <a href="login.php" class="text-[#3769a9] hover:text-[#2a5885] underline transition duration-200 text-sm">
                                Vous avez déjà un compte ?
                            </a>
                        </div>

                        <!--                        SUBMIT BUTTON-->
                        <div class="flex justify-center pt-2">
                            <button type="submit"
                                    name="register"
                                    class="px-14 py-2.5 bg-[#3769a9] hover:bg-[#2a5885] text-white text-lg font-bold rounded-full shadow-lg transition duration-200 ease-in-out transform hover:scale-105">
                                S'inscrire !
                            </button>
                        </div>

                    </form>

                </div>

                <!--                RIGHT SIDE - ILLUSTRATION-->
                <div class="hidden lg:flex justify-center items-center">
                    <div class="relative">
                        <img src="../img/campfire_sims.png"
                             alt="Sims autour d'un feu de camp"
                             class="w-full max-w-md object-contain drop-shadow-2xl"
                             onerror="this.style.display='none'">
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
</body>
</html>