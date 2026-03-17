<?php

$basePath = $basePath ?? '';
$showLoginButton = !isset($showLoginButton) || $showLoginButton;
$showProfilePic = $showProfilePic ?? false;
$showAdminButton = $showAdminButton ?? false;
$searchPlaceholder = $searchPlaceholder ?? 'Recherche :';

$username = $_SESSION['username'] ?? '';
$profilePicPath = $_SESSION['profilePicPath'] ?? 'img/profilePics/green_plumbob.png';
?>

<div id="navbar" class="relative w-full h-12 max-w-7xl p-4 mt-2 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]">

    <div id="navbar-content" class="grid grid-cols-[repeat(7,1fr)] grid-rows-1 gap-2 h-full">

        <!--                LOGO + SEARCHBAR Section-->

        <div id="left" class="col-span-3 flex items-center gap-2">

            <a href="<?php echo $basePath; ?>index.php">
                <div id="logo" class="top-1/2 text-xl font-bold text-gray-800 p-1">
                    <img src="<?php echo $basePath; ?>img/plumbob.webp"
                         alt="website logo"
                         class="w-8 h-8 object-contain"
                         onerror="this.onerror=null; this.style.display='none';">
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

            <?php if ($showProfilePic): ?>
                <!-- Photo de profil de l'utilisateur connecté -->
                <a href="#" title="<?php echo htmlspecialchars($username); ?>">
                    <img src="<?php echo $basePath . htmlspecialchars($profilePicPath); ?>"
                         class="absolute w-16 h-16 object-cover transform -top-6 bg-[#2a5885] border-4 border-[#33b842] rounded-full hover:scale-110 transition-transform"
                         alt="Photo de profil de <?php echo htmlspecialchars($username); ?>"
                         onerror="this.onerror=null; this.src='<?php echo $basePath; ?>img/profilePics/green_plumbob.png';">
                </a>
            <?php else: ?>
                <!-- Logo plumbob par défaut -->
                <a href="<?php echo $basePath; ?>index.php">
                    <img src="<?php echo  $basePath.$profilePicPath ?>"
                         class="absolute w-16 h-16 object-cover transform -top-6 bg-[#2a5885] border-4 border-[#33b842] rounded-full hover:scale-110 transition-transform"
                         alt="logo"
                         title="The Sims Den"
                         onerror="this.onerror=null; this.src='<?php echo $basePath; ?>img/profilePics/green_plumbob.png';">
                </a>
            <?php endif; ?>

        </div>

        <div id="right" class="col-start-5 col-span-3 flex flex-row-reverse items-center gap-4">

            <?php if ($showAdminButton): ?>
                <a href="<?php echo $basePath; ?>admin/games.php">
                    <button type="button"
                            class="px-6 py-1 bg-[#33b842] text-white rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] font-medium outline-none hover:bg-[#2a9636] transition duration-200 ease-in-out">
                        Admin
                    </button>
                </a>
            <?php endif; ?>

            <?php if ($showLoginButton): ?>
                <a href="<?php echo $basePath; ?>auth/login.php">
                    <button type="button"
                            class="px-6 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                        Se connecter
                    </button>
                </a>

                <a href="<?php echo $basePath; ?>auth/register.php">
                    <button type="button"
                            class="px-6 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                        S'inscrire
                    </button>
                </a>
            <?php else: ?>
                <a href="<?php echo $basePath; ?>auth/logout.php">
                    <button type="button"
                            class="px-6 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                        Déconnexion
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

