<?php

$basePath = $basePath ?? '/';
$showLoginButton = !isset($showLoginButton) || $showLoginButton;
$showProfilePic = $showProfilePic ?? false;
require_once dirname(__DIR__, 2) . '/src/Security/AuthMiddleware.php';
$showAdminButton = $showAdminButton ?? AuthMiddleware::is_admin();
$searchPlaceholder = $searchPlaceholder ?? 'Recherche :';

$headerUsername = $_SESSION['username'] ?? 'Invité';
$headerProfilePicPath = $_SESSION['profilePicPath'] ?? 'img/profilePics/green_plumbob.png';
?>

<style>
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(240, 238, 233, 0.5);
    }

    ::-webkit-scrollbar-thumb {
        background: #3769a9;
        border-radius: 5px;
        border: 2px solid #F0EEE9;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #2a5885;
    }

    /* Firefox */
    * {
        scrollbar-width: thin;
        scrollbar-color: #3769a9 rgba(240, 238, 233, 0.5);
    }
</style>

<div class="fixed top-0 left-0 w-full flex justify-center px-4 z-50">
    <div id="navbar"
        class="relative w-full h-12 max-w-7xl p-4 mt-16 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]">

    <div id="navbar-content" class="grid grid-cols-[repeat(7,1fr)] grid-rows-1 gap-2 h-full">

        <!--                LOGO + SEARCHBAR Section-->

        <div id="left" class="col-span-3 flex items-center gap-2">

            <a href="<?php echo $basePath; ?>index.php">
                <div id="logo" class="top-1/2 text-xl font-bold text-gray-800 p-1">
                    <img src="<?php echo $basePath; ?>img/plumbob.webp" alt="website logo"
                        class="w-8 h-8 object-contain" onerror="this.onerror=null; this.style.display='none';">
                </div>
            </a>

            <div id="searchbar">
                <form method="post">
                    <label>
                        <input type="text" name="search"
                            placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>"
                            class="pl-4 pr-32 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out"
                            autocomplete="off">
                    </label>
                </form>
            </div>

        </div>

        <!--                PROFILE PIC Section-->

        <div id="middle" class="col-start-4 relative flex justify-center">

            <?php if ($showProfilePic): ?>
                <!-- Photo de profil de l'utilisateur connecté -->
                <a href="<?php echo $basePath; ?>views/profil.php" title="<?php echo htmlspecialchars($headerUsername); ?>">
                    <img src="<?php echo $basePath . htmlspecialchars($headerProfilePicPath); ?>"
                        class="absolute left-1/2 -translate-x-1/2 w-22 h-22 object-cover -top-16 bg-[#2a5885] border-4 border-[#33b842] rounded-full hover:scale-110 hover:-translate-x-1/2 transition-transform shadow-lg"
                        alt="Photo de profil de <?php echo htmlspecialchars($headerUsername); ?>"
                        onerror="this.onerror=null; this.src='<?php echo $basePath; ?>img/profilePics/green_plumbob.png';">
                </a>
            <?php else: ?>
                <!-- Logo plumbob par défaut -->
                <a href="<?php echo $basePath; ?>index.php">
                    <img src="<?php echo $basePath . $headerProfilePicPath ?>"
                        class="absolute left-1/2 -translate-x-1/2 w-20 h-20 object-cover -top-14 bg-[#2a5885] border-4 border-[#33b842] rounded-full hover:scale-110 hover:-translate-x-1/2 transition-transform shadow-lg"
                        alt="logo" title="The Sims Den"
                        onerror="this.onerror=null; this.src='<?php echo $basePath; ?>img/profilePics/green_plumbob.png';">
                </a>
            <?php endif; ?>

        </div>

        <div id="right" class="col-start-5 col-span-3 flex flex-row-reverse items-center gap-4">

            <?php if ($showAdminButton): ?>
                <a href="<?php echo $basePath; ?>admin/globalDashboard.php">
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

                <a href="<?php echo $basePath; ?>views/favoriteList.php">
                    <button type="button"
                        class="px-6 py-1 bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                        Ma liste de favoris
                    </button>
                </a>
            <?php endif; ?>

        </div>

    </div>

</div>

</div>

<?php if (isset($_SESSION['userId'])): ?>
    <script src="<?php echo $basePath; ?>js/cheatCode.js"></script>
<?php endif; ?>