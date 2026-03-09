<?php

// Valeurs par défaut si besoin de changer en fonction du code qui include le header
$basePath = isset($basePath) ? $basePath : '';
$showLoginButton = !isset($showLoginButton) || $showLoginButton;
$searchPlaceholder = isset($searchPlaceholder) ? $searchPlaceholder : 'Recherche :';
?>

<div id="navbar" class="relative w-full h-12 max-w-7xl p-4 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]">

    <div id="navbar-content" class="grid grid-cols-[repeat(7,1fr)] grid-rows-1 gap-2 h-full">

        <!--                LOGO + SEARCHBAR Section-->

        <div id="left" class="col-span-3 flex items-center gap-2">

            <a href="<?php echo $basePath; ?>home.php">
                <div id="logo" class="top-1/2 text-xl font-bold text-gray-800 p-1">
                    <img src="<?php echo $basePath; ?>img/plumbob.webp" alt="website logo" class="w-8 h-8 object-contain">
                </div>
            </a>

            <div id="searchbar">
                <form method="post">
                    <label>
                        <input type="text"
                               name="search"
                               placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>"
                               class="pl-4 pr-32 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out"
                               autocomplete="off">
                    </label>
                </form>
            </div>

        </div>

        <!--                PROFILE PIC Section-->

        <div id="middle" class="col-start-4 flex justify-center">

                <a href="<?php echo $basePath; ?>home.php">
                    <img src="<?php echo $basePath; ?>img/plumbob.webp"
                         class="absolute w-16 h-16 object-contain transform -top-8 bg-[#2a5885] border-4 border-[#33b842] rounded-full p-2"
                         alt="logo"
                         title="The Sims Den">
                </a>

        </div>

        <div id="right" class="col-start-5 col-span-3 flex flex-row-reverse items-center gap-4">

            <?php if ($showLoginButton): ?>
                <a href="<?php echo $basePath; ?>auth/login.php">
                    <button type="button"
                            class="px-6 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                        Se connecter
                    </button>
                </a>
            <?php else: ?>
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
            <?php endif; ?>

        </div>

    </div>

</div>

