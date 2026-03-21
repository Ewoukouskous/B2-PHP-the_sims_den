<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = dirname(__DIR__, 2);
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Database/DatabaseConnection.php';
require_once $root_path . '/src/Model/Game.php';
require_once $root_path . '/src/Repository/GameRepository.php';
require_once $root_path . '/src/Repository/UserAccountRepository.php';
require_once $root_path . '/src/Repository/ProfilePicRepository.php';
require_once $root_path . '/src/Repository/UserAchievementRepository.php';
require_once $root_path . '/src/Repository/UserFavoriteRepository.php';
require_once $root_path . '/src/Enum/GameType.php';
require_once $root_path . '/src/Enum/PegiAge.php';
require_once $root_path . '/src/Enum/UserRole.php';

if (!AuthMiddleware::is_admin($_SESSION)) {
    header('Location: /index.php');
    exit();
}

function formatShortDate(?DateTime $date): string {
    if ($date === null) {
        return 'Jamais';
    }
    return $date->format('d/m/Y');
}

$activeTab = strtolower(trim((string)($_GET['tab'] ?? 'games')));
if (!in_array($activeTab, ['games', 'users'], true)) {
    $activeTab = 'games';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'deleteGame') {

    // DEPENDENCIES TO REMOVE A GAME
    require_once $root_path . '/src/Model/Game.php';
    require_once $root_path . '/src/Model/GameMedia.php';
    require_once $root_path . '/src/Repository/GameRepository.php';
    require_once $root_path . '/src/Repository/GameMediaRepository.php';

    if (isset($_POST['gameId'])) {
        $gameRepo = new GameRepository();
        // Check if the sent game exist
        $game = $gameRepo->findById((int)$_POST['gameId']);

        if ($game !== null) {
            try {
                // Get the path of the heroPic and titlePic (dbPath is /img/games/.... , so we add the /public to get the absolute path)
                $titlePicPath = $root_path . '/public' . $game->getImageTitlePath();
                $heroPicPath = $root_path . '/public' . $game->getImageHeroPath();
                // Check if the two pics exists, if yes delete them
                if (file_exists($titlePicPath)) {unlink($titlePicPath);}
                if (file_exists($heroPicPath)) {unlink($heroPicPath);}

                // Now get all the gameMedia linked to the game to get their path after
                $gameMedias = (new GameMediaRepository())->findByGameId($game->getId());

                // Check if the game has gameMedias
                if (!empty($gameMedias)) {
                    foreach($gameMedias as $media) {
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
                header("Location: globalDashboard.php?tab=games");
                exit();
            }
            catch (Exception $exception) {
                $error_msg = "Erreur lors de la suppression de '" . $game->getGameName() . "' :\"" .$exception->getMessage() . "\"";
            }
        } else {
            $error_msg = "Erreur, le jeu que vous souhaité supprimer n'existe pas dans la base de données";
        }
    }
}

$error_msg = "";
$userRepository = new UserAccountRepository();

// Check if it's a POST request that contain a 'action' field that contains 'changeUserRole' and 'userId'
if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action']) && $_POST['action'] === 'changeUserRole' && isset($_POST['userId'])) {
    // Check that the request isn't for the user that initiate it (so you can't demote yourself)
    if ((int)$_POST['userId'] !== (int)$_SESSION['userId']) {
        // Check that the userId correspond to an actual user in the database
        $userToUpdate = $userRepository->findById((int)$_POST['userId']);
        if(!is_null($userToUpdate)) {
            // We change the user role with the select value
            $newRoleRaw = strtolower(trim((string)($_POST['newUserRole'] ?? '')));
            try {
                $newRole = UserRole::from($newRoleRaw);
                $userToUpdate->setUserRole($newRole);
                // Then update the user in DB
                $userRepository->update($userToUpdate);
                // Now we redirect
                header("Location: globalDashboard.php?tab=users&selectedUser=" . $userToUpdate->getId());
                exit();
            } catch (ValueError) {
                $error_msg = "Erreur, le rôle sélectionné est invalide.";
            }
        } else {
            $error_msg = "Erreur, l'utilisateur que vous essayez de modifier n'existe pas.";
        }
    } else {
        $error_msg = "Erreur, il est impossible de modifier le rôle de votre propre compte.";
    }
}

// Check if it's a POST request that contain a 'action' field that contains 'deleteUser' and 'userId'
if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action']) && $_POST['action'] === 'deleteUser' && isset($_POST['userId'])) {
    // Check that the request isn't for the user that initiate it (so you can't delete yourself)
    if ((int)$_POST['userId'] !== (int)$_SESSION['userId']) {
        // Check that the userId correspond to an actual user in the database
        $userToDelete = $userRepository->findById((int)$_POST['userId']);
        if(!is_null($userToDelete)) {
            // We delete the user
            $userRepository->delete($userToDelete->getId());
            // Now we redirect
            header("Location: globalDashboard.php?tab=users");
            exit();
        } else {
            $error_msg = "Erreur, l'utilisateur que vous essayez de supprimer n'existe pas.";
        }
    } else {
        $error_msg = "Erreur, il est impossible de supprimer votre propre compte.";
    }
}

$gameRepository = new GameRepository();
$games = $gameRepository->findAll();
$profilePicRepository = new ProfilePicRepository();
$userAchievementRepository = new UserAchievementRepository();
$userFavoriteRepository = new UserFavoriteRepository();

$users = $userRepository->findAll();
$userRows = [];

foreach ($users as $user) {
    $userId = $user->getId();
    if ($userId === null) {
        continue;
    }

    $profilePic = $profilePicRepository->findById($user->getIdProfilePic());
    $profilePicPath = $profilePic ? $profilePic->getPicturePath() : 'img/profilePics/green_plumbob.png';

    $userRows[$userId] = [
        'id' => $userId,
        'username' => $user->getUsername(),
        'roleLabel' => ucfirst($user->getUserRole()->value),
        'roleValue' => $user->getUserRole()->value,
        'dateJoined' => $user->getDateJoined(),
        'lastLogin' => $user->getLastLogin(),
        'profilePicPath' => $profilePicPath,
        'achievementsCount' => count($userAchievementRepository->findAchievedByUserId($userId)),
        'favoritesCount' => count($userFavoriteRepository->findAllByUserId($userId))
    ];
}

$selectedUserId = null;
$selectedUserRaw = $_GET['selectedUser'] ?? '';
$selectedUserString = trim((string)$selectedUserRaw);
if ($selectedUserString !== '' && ctype_digit($selectedUserString)) {
    $selectedUserId = (int)$selectedUserString;
}

if (($selectedUserId === null || !isset($userRows[$selectedUserId])) && !empty($userRows)) {
    $selectedUserId = (int)array_key_first($userRows);
}

$selectedUser = $selectedUserId !== null && isset($userRows[$selectedUserId]) ? $userRows[$selectedUserId] : null;
$currentSessionUserId = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : null;
$canDeleteSelectedUser = $selectedUser !== null
    && ($currentSessionUserId === null || $selectedUser['id'] !== $currentSessionUserId);
$canEditSelectedUserRole = $canDeleteSelectedUser;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard Global - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        .animate-fadeIn { animation: fadeIn 0.3s ease-in-out; }
        .animate-slideUp { animation: slideUp 0.3s ease-in-out; }
    </style>
</head>
<body class="h-screen overflow-hidden">
<div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center bg-[#3769a9]">
    <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover">
</div>

<div id="content" class="relative flex justify-center h-screen overflow-hidden p-4 pt-10 pb-6">

    <?php
    $basePath = '../';
    $showLoginButton = false;
    $showProfilePic = true;
    $searchPlaceholder = 'Recherche :';
    include '../includes/header.php';
    ?>

    <div class="absolute top-26 bottom-6 w-full max-w-7xl px-4 flex flex-col gap-4 overflow-hidden" style="padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));">

        <div class="mt-2 bg-[#F0EEE9] bg-opacity-85 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-3">
            <div class="flex items-center gap-3">
                <a href="globalDashboard.php?tab=games"
                   class="px-6 py-2 rounded-full font-bold shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] transition duration-200 <?php echo $activeTab === 'games' ? 'bg-[#33b842] text-white' : 'bg-[#F0EEE9] text-[#3769a9]'; ?>">
                    Jeux
                </a>
                <a href="globalDashboard.php?tab=users"
                   class="px-6 py-2 rounded-full font-bold shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] transition duration-200 <?php echo $activeTab === 'users' ? 'bg-[#33b842] text-white' : 'bg-[#F0EEE9] text-[#3769a9]'; ?>">
                    Utilisateurs
                </a>
            </div>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]" role="alert">
                <span class="block sm:inline font-bold"><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($activeTab === 'games'): ?>
            <div class="bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-6 space-y-5 flex-1 min-h-0 flex flex-col overflow-hidden">
                <div class="flex items-center justify-between gap-6">
                    <h1 class="text-5xl font-bold text-[#3769a9] pb-2 inline-block border-b-4 border-[#33b842]">Gestion des Jeux</h1>

                    <a href="gameDashboard.php">
                        <button type="button"
                                class="px-8 py-3 bg-[#33b842] text-white font-bold rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(0,0,0,0.1)] border border-white/20 hover:bg-[#2a9636] transition duration-200 ease-in-out">
                            + Créer un jeu
                        </button>
                    </a>
                </div>

                <div class="space-y-4 flex-1 min-h-0 overflow-hidden">
                    <?php if (empty($games)): ?>
                        <div class="text-center py-16">
                            <p class="text-2xl text-[#3769a9] font-bold">Aucun jeu cree pour le moment</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 gap-4 h-full min-h-0 overflow-y-auto pr-2">
                            <?php foreach ($games as $game): ?>
                                <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-between border-l-4 border-[#33b842] hover:shadow-lg transition-shadow duration-200">
                                    <div class="flex items-center gap-6 flex-1">
                                        <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg">
                                            <img src="../<?php echo htmlspecialchars($game->getImageTitlePath()); ?>"
                                                 alt="<?php echo htmlspecialchars($game->getGameName()); ?>"
                                                 class="w-full h-full object-cover"
                                                 onerror="this.onerror=null; this.src='../img/plumbob.webp';">
                                        </div>

                                        <div class="flex-1">
                                            <h2 class="text-xl font-bold text-[#3769a9]"><?php echo htmlspecialchars($game->getGameName()); ?></h2>
                                            <p class="text-sm text-gray-600 line-clamp-1"><?php echo htmlspecialchars($game->getGameDesc()); ?></p>
                                            <div class="flex gap-3 mt-2">
                                                <span class="text-xs bg-[#33b842] text-white px-3 py-1 rounded-full font-medium capitalize">
                                                    <?php echo htmlspecialchars($game->getGameType()->value); ?>
                                                </span>
                                                <span class="text-xs bg-[#3769a9] text-white px-3 py-1 rounded-full font-medium">
                                                    PEGI <?php echo htmlspecialchars($game->getPegiAge()->value); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-6 flex-shrink-0">
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-[#33b842]"><?php echo number_format($game->getPrice(), 2, ',', ' '); ?> €</p>
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
        <?php else: ?>
            <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-5 flex-1 min-h-0">
                <div class="bg-[#F0EEE9] bg-opacity-90 rounded-[2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-4 flex flex-col min-h-0">
                    <div class="grid grid-cols-[1.45fr_1fr_1.2fr_48px] text-[#3769a9] font-bold px-4 py-2.5 rounded-2xl bg-white/70 border border-white/70">
                        <span class="text-[1.35rem] leading-none md:text-[1.1rem]">Username</span>
                        <span class="text-[1.35rem] leading-none md:text-[1.1rem]">Role</span>
                        <span class="text-[1.35rem] leading-none md:text-[1.1rem]">Derniere connexion</span>
                        <span></span>
                    </div>

                    <div class="space-y-3 mt-3 flex-1 min-h-0 overflow-y-auto pr-1 pb-3">
                        <?php if (empty($userRows)): ?>
                            <p class="text-[#3769a9] font-bold text-center py-8">Aucun utilisateur</p>
                        <?php else: ?>
                            <?php foreach ($userRows as $userRow): ?>
                                <a href="globalDashboard.php?tab=users&selectedUser=<?php echo $userRow['id']; ?>"
                                   class="grid grid-cols-[1.45fr_1fr_1.2fr_48px] items-center min-h-[60px] rounded-full px-4 py-2 border-2 bg-[#F0EEE9] shadow-[0px_4px_0px_1px_rgba(158,158,158,1)] transition duration-200 hover:bg-white hover:-translate-y-[1px] <?php echo $selectedUser !== null && $selectedUser['id'] === $userRow['id'] ? 'border-[#33b842] ring-2 ring-[#33b842]/20' : 'border-transparent'; ?>">
                                    <span class="text-[#3769a9] font-bold text-[1.55rem] leading-none md:text-[1.45rem] lg:text-[1.5rem]">
                                        <?php echo htmlspecialchars($userRow['username']); ?>
                                    </span>
                                    <span class="text-[#3769a9] font-semibold text-[1.3rem] leading-none md:text-[1.15rem] lg:text-[1.2rem]">
                                        <?php echo htmlspecialchars($userRow['roleLabel']); ?>
                                    </span>
                                    <span class="text-[#3769a9] font-semibold text-[1.3rem] leading-none md:text-[1.15rem] lg:text-[1.2rem]">
                                        <?php echo htmlspecialchars(formatShortDate($userRow['lastLogin'])); ?>
                                    </span>
                                    <span class="flex justify-end">
                                        <span class="w-9 h-9 rounded-full bg-white border border-[#cfcfcf] shadow-[0px_2px_0px_1px_rgba(158,158,158,1)] flex items-center justify-center text-[#3769a9] transition duration-200 hover:scale-105 hover:bg-[#ecf4ff]">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M2 12C3.9 7.8 7.6 5 12 5C16.4 5 20.1 7.8 22 12C20.1 16.2 16.4 19 12 19C7.6 19 3.9 16.2 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="1.8"/>
                                            </svg>
                                        </span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-[#F0EEE9] bg-opacity-90 rounded-[2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-5 flex flex-col items-center justify-start min-h-0 overflow-y-auto">
                    <?php if ($selectedUser === null): ?>
                        <p class="text-[#3769a9] font-bold mt-10">Selectionne un utilisateur</p>
                    <?php else: ?>
                        <div class="w-full flex flex-col items-center rounded-3xl bg-white/55 border border-white/70 py-4 px-4 shadow-[0px_2px_0px_1px_rgba(176,176,176,1)]">
                            <img src="../<?php echo htmlspecialchars($selectedUser['profilePicPath']); ?>"
                                 alt="Photo de profil"
                                 class="w-20 h-20 object-cover bg-[#2a5885] border-4 border-[#33b842] rounded-full"
                                 onerror="this.onerror=null; this.src='../img/profilePics/green_plumbob.png';">

                            <h2 class="text-3xl md:text-2xl lg:text-3xl font-bold text-[#3769a9] mt-4 mb-4"><?php echo htmlspecialchars($selectedUser['username']); ?></h2>

                            <div class="w-full text-[#3769a9] font-semibold text-sm leading-relaxed space-y-1">
                                <p>Date d'inscription : <?php echo htmlspecialchars(formatShortDate($selectedUser['dateJoined'])); ?></p>
                                <p>Derniere connexion : <?php echo htmlspecialchars(formatShortDate($selectedUser['lastLogin'])); ?></p>
                                <p>Nombre de succes : <?php echo htmlspecialchars((string)$selectedUser['achievementsCount']); ?></p>
                                <p>Nombre de favoris : <?php echo htmlspecialchars((string)$selectedUser['favoritesCount']); ?></p>
                            </div>

                            <form method="post" action="" class="w-full mt-4">
                                <input type="hidden" name="action" value="changeUserRole">
                                <input type="hidden" name="userId" value="<?php echo (int)$selectedUser['id']; ?>">

                                <label for="newUserRole" class="block text-[#3769a9] font-semibold text-sm mb-2">Role du compte</label>
                                <div class="flex items-center gap-2">
                                    <select id="newUserRole"
                                            name="newUserRole"
                                            class="flex-1 bg-[#F0EEE9] border border-[#cfd6e0] rounded-full px-4 py-2 text-[#3769a9] font-semibold text-sm outline-none focus:ring-2 focus:ring-[#33b842]/30"
                                        <?php echo $canEditSelectedUserRole ? '' : 'disabled'; ?>>
                                        <?php foreach (UserRole::cases() as $role): ?>
                                            <option value="<?php echo htmlspecialchars($role->value); ?>"
                                                <?php echo $selectedUser['roleValue'] === $role->value ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(ucfirst($role->value)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <?php if ($canEditSelectedUserRole): ?>
                                        <button type="submit"
                                                class="px-4 py-2 rounded-full bg-[#33b842] text-white font-bold text-sm shadow-[0px_2px_0px_1px_rgba(0,0,0,0.08)] hover:bg-[#2a9636] transition duration-200">
                                            Mettre a jour
                                        </button>
                                    <?php else: ?>
                                        <button type="button"
                                                class="px-4 py-2 rounded-full bg-[#E5E2DB] text-[#7a8aa0] font-bold text-sm shadow-[0px_2px_0px_1px_rgba(158,158,158,1)] cursor-not-allowed"
                                                disabled>
                                            Indisponible
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <?php if ($canDeleteSelectedUser): ?>
                            <button type="button"
                                    class="admin-delete-user-btn mt-5 px-7 py-2 rounded-full bg-[#F0EEE9] text-[#3769a9] font-medium shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] hover:bg-[#ffe3e3] transition duration-200"
                                    data-user-id="<?php echo (int)$selectedUser['id']; ?>"
                                    data-user-name="<?php echo htmlspecialchars($selectedUser['username']); ?>">
                                Expulser du foyer
                            </button>
                        <?php else: ?>
                            <button type="button"
                                    class="mt-5 px-7 py-2 rounded-full bg-[#E5E2DB] text-[#7a8aa0] font-medium shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] cursor-not-allowed"
                                    disabled>
                                Expulsion indisponible
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="deleteModal"
     class="hidden fixed inset-0 flex items-center justify-center z-50 p-4 animate-fadeIn"
     style="background-color: rgba(10, 20, 35, 0.16); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
    <div class="bg-white bg-opacity-98 rounded-lg shadow-2xl p-8 max-w-sm w-full border-4 border-gray-300 animate-slideUp" style="box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);">
        <div class="text-center space-y-6">
            <h2 id="modalDeleteTitle" class="text-3xl font-bold text-blue-600 tracking-wide">EXIT GAME</h2>

            <div class="space-y-3">
                <p id="modalDeleteQuestion" class="text-lg font-bold text-gray-800">Etes-vous sur de vouloir supprimer le jeu ?</p>
                <p class="text-base font-bold text-gray-700"><span id="modalTargetName" class="text-red-600"></span></p>
            </div>

            <div class="flex items-center justify-end gap-6 pt-6">
                <button type="button"
                        id="cancelBtn"
                        class="w-16 h-16 flex items-center justify-center rounded-full bg-white border-4 border-blue-500 text-blue-500 font-bold text-3xl shadow-lg hover:bg-blue-100 transition duration-200 ease-in-out hover:scale-110">
                    X
                </button>

                <button type="button"
                        id="confirmDeleteBtn"
                        class="w-16 h-16 flex items-center justify-center rounded-full bg-white border-4 border-green-500 text-green-500 font-bold text-3xl shadow-lg hover:bg-green-100 transition duration-200 ease-in-out hover:scale-110">
                    V
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../js/adminGames.js"></script>
</body>
</html>



