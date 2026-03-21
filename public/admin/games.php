<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = dirname(__DIR__, 2);
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Database/DatabaseConnection.php';
require_once $root_path . '/src/Model/Game.php';
require_once $root_path . '/src/Repository/GameRepository.php';
require_once $root_path . '/src/Enum/GameType.php';
require_once $root_path . '/src/Enum/PegiAge.php';
require_once $root_path . '/src/Enum/UserRole.php';

// Check if the user is admin
if (!AuthMiddleware::is_admin($_SESSION)) {
    header('Location: ../index.php');
    exit();
}

// Handle game deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'deleteGame') {

    // DEPENDENCIES TO REMOVE A GAME
    require_once $root_path . '/src/Model/Game.php';
    require_once $root_path . '/src/Model/GameMedia.php';
    require_once $root_path . '/src/Repository/GameRepository.php';
    require_once $root_path . '/src/Repository/GameMediaRepository.php';

    if (isset($_POST['gameId'])) {
        $gameRepo = new GameRepository();
        $gameId = (int) $gameIdString;
        $game = $gameRepo->findById($gameId);

        if ($game !== null) {
            try {
                // Get the path of the heroPic and titlePic (dbPath is /img/games/.... , so we add the /public to get the absolute path)
                $titlePicPath = $root_path . '/public' . $game->getImageTitlePath();
                $heroPicPath = $root_path . '/public' . $game->getImageHeroPath();
                // Check if the two pics exists, if yes delete them
                if (file_exists($titlePicPath)) {
                    unlink($titlePicPath);
                }
                if (file_exists($heroPicPath)) {
                    unlink($heroPicPath);
                }

                // Now get all the gameMedia linked to the game to get their path after
                $gameMedias = (new GameMediaRepository())->findByGameId($game->getId());

                // Check if the game has gameMedias
                if (!empty($gameMedias)) {
                    foreach ($gameMedias as $media) {
                        $mediaPath = $root_path . '/public' . $media->getFilePath();
                        // If the file exist we delete it, else we do nothing
                        if (file_exists($mediaPath)) {
                            unlink($mediaPath);
                        }
                    }
                }
                // Now if we go there it's means that everything before goes good (all files has been deleted)
                // so we can delete the game in the database (and the db will delete all GamePegiDescriptors and GameMedia associated
                $gameRepo->delete($game->getId());
                // Then redirect
                header("Location: games.php");
                exit();
            } catch (Exception $e) {
                // Log the error or handle it appropriately
                error_log('Error deleting game: ' . $e->getMessage());
            }
        }
    }
}

$error_msg = "";
// Récupérer tous les jeux depuis la base de données
$gameRepository = new GameRepository();
$games = $gameRepository->findAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion des Jeux - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }

        .animate-slideUp {
            animation: slideUp 0.3s ease-in-out;
        }

        #deleteModal:not(.hidden) {
            animation: fadeIn 0.3s ease-in-out;
        }

        #deleteModal:not(.hidden)>div {
            animation: slideUp 0.3s ease-in-out;
        }
    </style>
</head>

<body>
    <div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center bg-[#3769a9]">
        <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover">
    </div>

    <div id="content" class="relative flex justify-center min-h-screen p-4 pt-12">

        <!--        NAVBAR Section-->

        <?php
        $basePath = '../';
        $showLoginButton = false;
        $showProfilePic = true;
        $searchPlaceholder = 'Recherche :';
        include '../includes/header.php';
        ?>

        <div class="absolute top-28 w-full max-w-7xl px-4 pb-12">

            <div
                class="bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-8 space-y-8">

                <!--            HEADER Section-->
                <div class="flex items-center justify-between gap-6">
                    <div>
                        <h1 class="text-5xl font-bold text-[#3769a9] pb-2 inline-block border-b-4 border-[#33b842]">
                            Gestion des Jeux
                        </h1>
                    </div>

                    <a href="gameDashboard.php">
                        <button type="button"
                            class="px-8 py-3 bg-[#33b842] text-white font-bold rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(0,0,0,0.1)] border border-white/20 hover:bg-[#2a9636] transition duration-200 ease-in-out">
                            + Créer un jeu
                        </button>
                    </a>
                </div>

                <!--            GAMES LIST Section-->
                <div class="space-y-4">
                    <?php if (empty($games)): ?>
                        <div class="text-center py-16">
                            <p class="text-2xl text-[#3769a9] font-bold">
                                Aucun jeu créé pour le moment
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 gap-4 max-h-[60vh] overflow-y-auto pr-2">
                            <?php foreach ($games as $game): ?>
                                <div
                                    class="bg-white rounded-2xl shadow-md p-6 flex items-center justify-between border-l-4 border-[#33b842] hover:shadow-lg transition-shadow duration-200">

                                    <!--                        GAME INFO -->
                                    <div class="flex items-center gap-6 flex-1">
                                        <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg">
                                            <img src="../<?php echo htmlspecialchars($game->getImageTitlePath()); ?>"
                                                alt="<?php echo htmlspecialchars($game->getGameName()); ?>"
                                                class="w-full h-full object-cover"
                                                onerror="this.onerror=null; this.src='../img/plumbob.webp';">
                                        </div>

                                        <div class="flex-1">
                                            <h2 class="text-xl font-bold text-[#3769a9]">
                                                <a href="../views/game.php?id=<?php echo $game->getId(); ?>"
                                                    class="hover:underline hover:text-blue-700 transition-colors">
                                                    <?php echo htmlspecialchars($game->getGameName()); ?>
                                                </a>
                                            </h2>
                                            <p class="text-sm text-gray-600 line-clamp-1">
                                                <?php echo htmlspecialchars($game->getGameDesc()); ?>
                                            </p>
                                            <div class="flex gap-3 mt-2">
                                                <span
                                                    class="text-xs bg-[#33b842] text-white px-3 py-1 rounded-full font-medium">
                                                    <?php echo match ($game->getGameType()) {
                                                        GameType::PC => 'PC',
                                                        GameType::CONSOLE => 'Console',
                                                        GameType::SMARTPHONE => 'Smartphone'
                                                    }; ?>
                                                </span>
                                                <span
                                                    class="text-xs bg-[#3769a9] text-white px-3 py-1 rounded-full font-medium">
                                                    PEGI <?php echo htmlspecialchars($game->getPegiAge()->value); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!--                        PRICE & ACTIONS -->
                                    <div class="flex items-center gap-6 flex-shrink-0">
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-[#33b842]">
                                                <?php echo number_format($game->getPrice(), 2, ',', ' '); ?> €
                                            </p>
                                        </div>

                                        <button type="button"
                                            class="admin-delete-btn px-6 py-2 bg-red-500 text-white font-bold rounded-lg shadow-md hover:bg-red-600 transition duration-200 ease-in-out"
                                            data-game-id="<?php echo $game->getId(); ?>"
                                            data-game-name="<?php echo htmlspecialchars($game->getGameName()); ?>">
                                            Supprimer
                                        </button>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    </div>

    <!--        DELETE MODAL (Sims Style - Exit Game)-->
    <div id="deleteModal" class="hidden fixed inset-0 flex items-center justify-center z-50 p-4 animate-fadeIn"
        style="background-color: rgba(10, 20, 35, 0.16); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
        <div class="bg-white bg-opacity-98 rounded-lg shadow-2xl p-8 max-w-sm w-full border-4 border-gray-300 animate-slideUp"
            style="box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);">

            <div class="text-center space-y-6">

                <!-- TITLE -->
                <h2 class="text-3xl font-bold text-blue-600 tracking-wide">
                    EXIT GAME
                </h2>

                <!-- MESSAGE -->
                <div class="space-y-3">
                    <p class="text-lg font-bold text-gray-800">
                        ├ètes-vous s├╗r de vouloir supprimer le jeu ?
                    </p>
                    <p class="text-base font-bold text-gray-700">
                        <span id="modalGameName" class="text-red-600"></span>
                    </p>
                </div>

                <!-- BUTTONS (X and Ô£ô) -->
                <div class="flex items-center justify-end gap-6 pt-6">
                    <!-- Cancel Button (X) -->
                    <button type="button" id="cancelBtn"
                        class="w-16 h-16 flex items-center justify-center rounded-full bg-white border-4 border-blue-500 text-blue-500 font-bold text-3xl shadow-lg hover:bg-blue-100 transition duration-200 ease-in-out hover:scale-110">
                        ✕
                    </button>

                    <!-- Confirm Button (✓) -->
                    <button type="button" id="confirmDeleteBtn"
                        class="w-16 h-16 flex items-center justify-center rounded-full bg-white border-4 border-green-500 text-green-500 font-bold text-3xl shadow-lg hover:bg-green-100 transition duration-200 ease-in-out hover:scale-110">
                        ✓
                    </button>
                </div>

            </div>

        </div>
    </div>

    <script src="../js/adminGames.js"></script>

</body>

</html>