<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = dirname(__DIR__, 2);
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Model/UserAccount.php';
require_once $root_path . '/src/Model/ProfilePic.php';
require_once $root_path . '/src/Model/Game.php';
require_once $root_path . '/src/Repository/UserAccountRepository.php';
require_once $root_path . '/src/Repository/ProfilePicRepository.php';
require_once $root_path . '/src/Repository/UserFavoriteRepository.php';
require_once $root_path . '/src/Repository/GameRepository.php';
require_once $root_path . '/src/Repository/UserAchievementRepository.php';
require_once $root_path . '/src/Enum/GameType.php';
require_once $root_path . '/src/Enum/UserRole.php';

$isConnected = AuthMiddleware::is_connected($_SESSION);
$currentUserId = ($isConnected && isset($_SESSION['userId']) && is_numeric($_SESSION['userId'])) ? (int) $_SESSION['userId'] : null;

$userAccountRepository = new UserAccountRepository();
$profilePicRepository = new ProfilePicRepository();
$userFavoriteRepository = new UserFavoriteRepository();
$gameRepository = new GameRepository();
$userAchievementRepository = new UserAchievementRepository();

$userAccount = $currentUserId !== null ? $userAccountRepository->findById($currentUserId) : null;

// --- PROFILE UPDATES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userAccount !== null) {
    if (isset($_POST['update_pic']) && !empty($_POST['profile_pic'])) {
        $newPath = ltrim($_POST['profile_pic'], '/');
        foreach ($profilePicRepository->findAll() as $p) {
            if (ltrim($p->getPicturePath(), '/') === $newPath) {
                $userAccount->setIdProfilePic($p->getId());
                $userAccountRepository->update($userAccount);
                $_SESSION['profilePicPath'] = ltrim($p->getPicturePath(), '/');
                break;
            }
        }
        header("Location: profil.php");
        exit();
    }

    if (isset($_POST['update_username']) && !empty(trim($_POST['username']))) {
        $newUsername = trim($_POST['username']);
        $userAccount->setUsername($newUsername);
        $userAccountRepository->update($userAccount);
        $_SESSION['username'] = $newUsername;
        header("Location: profil.php");
        exit();
    }

    if (isset($_POST['update_email']) && !empty(trim($_POST['email']))) {
        $newEmail = trim($_POST['email']);
        $userAccount->setEmail($newEmail);
        $userAccountRepository->update($userAccount);
        header("Location: profil.php");
        exit();
    }

    if (isset($_POST['update_password']) && !empty($_POST['password']) && !empty($_POST['password_confirm'])) {
        if ($_POST['password'] === $_POST['password_confirm']) {
            $userAccount->setPasswordHash(password_hash($_POST['password'], PASSWORD_DEFAULT));
            $userAccountRepository->update($userAccount);
            header("Location: profil.php");
            exit();
        }
    }
}

$username = $userAccount?->getUsername() ?? 'Invité';
$memberSince = $userAccount?->getDateJoined()->format('d/m/Y') ?? '--/--/----';

$profilePicPath = 'img/profilePics/green_plumbob.png';
if ($userAccount !== null) {
    $profilePic = $profilePicRepository->findById($userAccount->getIdProfilePic());
    if ($profilePic !== null) {
        $profilePicPath = ltrim($profilePic->getPicturePath(), '/');
    }
}

$favoriteGames = [];
$favoritesCount = 0;
if ($currentUserId !== null) {
    foreach ($userFavoriteRepository->findAllByUserId($currentUserId) as $userFavorite) {
        $game = $gameRepository->findById($userFavorite->getIdGame());
        if ($game === null) {
            continue;
        }
        $favoritesCount++;
        $typeLabel = match ($game->getGameType()) {
            GameType::PC => 'PC',
            GameType::CONSOLE => 'Console',
            GameType::SMARTPHONE => 'Smartphone',
        };
        $favoriteGames[] = [
            'id' => $game->getId(),
            'title' => $game->getGameName(),
            'type' => $typeLabel,
            'price' => number_format($game->getPrice(), 2, ',', ' ') . ' €',
            'favorites' => $game->getFavoritesNumber(),
            'image' => '../' . $game->getImageHeroPath(),
        ];
    }
}

$favoriteGames = array_slice($favoriteGames, 0, 4);

$achievementsCount = 0;
if ($currentUserId !== null) {
    $achievementsCount = count($userAchievementRepository->findAchievedByUserId($currentUserId));
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($username); ?> - Profil - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="h-screen overflow-hidden">
    <div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center bg-[#3769a9]">
        <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover"
            onerror="this.style.display='none';">
    </div>

    <div id="content" class="relative flex justify-center h-screen overflow-hidden p-4 pt-12">

        <?php
        $basePath = '../';
        $showLoginButton = !$isConnected;
        $showProfilePic = $isConnected;
        $searchPlaceholder = 'Recherche :';
        include '../includes/header.php';
        ?>

        <div class="absolute top-36 w-full max-w-7xl px-4">

            <div
                class="relative bg-[#F0EEE9] bg-opacity-90 rounded-[1.8rem] shadow-[0px_8px_0px_0px_rgba(51,184,66,0.9)] p-6 flex flex-col gap-4">

                <button type="button" title="Modifier le profil"
                    class="absolute top-4 right-4 w-10 h-10 bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] flex items-center justify-center text-[#3769a9] hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.213l-4.5 1 1-4.5L16.862 3.487z" />
                    </svg>
                </button>

                <div class="grid grid-cols-[1fr_auto_1fr] gap-6 items-start">

                    <div class="flex flex-col gap-3">
                        <h2
                            class="text-2xl font-bold text-[#3769a9] pb-1 inline-block border-b-2 border-[#33b842] w-fit">
                            Succès :
                        </h2>
                        <div class="grid grid-cols-5 gap-2">
                            <?php for ($i = 0; $i < 10; $i++): ?>
                                <div
                                    class="w-12 h-12 rounded-full bg-[#3769a9] bg-opacity-20 border-2 border-[#3769a9] border-opacity-20 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-[#3769a9] opacity-30"
                                        fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-2">
                        <div
                            class="w-20 h-20 rounded-full bg-[#2a5885] border-4 border-[#33b842] overflow-hidden flex items-center justify-center shadow-lg">
                            <img src="../<?php echo htmlspecialchars($profilePicPath); ?>"
                                alt="Photo de profil de <?php echo htmlspecialchars($username); ?>"
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null; this.src='../img/profilePics/green_plumbob.png';">
                        </div>
                        <span
                            class="text-2xl font-bold text-[#3769a9]"><?php echo htmlspecialchars($username); ?></span>
                    </div>

                    <div class="flex flex-col gap-3 pl-10">
                        <h2
                            class="text-2xl font-bold text-[#3769a9] pb-1 inline-block border-b-2 border-[#33b842] w-fit">
                            Infos :
                        </h2>
                        <div class="flex flex-col gap-1.5 text-[#3769a9] font-semibold text-base">
                            <p>Membre depuis le : <span
                                    class="font-bold"><?php echo htmlspecialchars($memberSince); ?></span></p>
                            <p>Nombre de favoris : <span class="font-bold"><?php echo $favoritesCount; ?></span></p>
                            <p>Nombre de succès : <span class="font-bold"><?php echo $achievementsCount; ?></span></p>
                        </div>
                    </div>

                </div>

                <hr class="border-t-2 border-[#3769a9] opacity-10">

                <div class="flex flex-col gap-3">
                    <h2 class="text-2xl font-bold text-[#3769a9] pb-1 inline-block border-b-2 border-[#33b842] w-fit">
                        Favoris :
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 min-h-[14rem]">
                        <?php foreach ($favoriteGames as $favorite): ?>
                            <a href="game.php?id=<?php echo (int) $favorite['id']; ?>"
                                class="group relative bg-white rounded-[1.5rem] p-2.5 shadow-[0px_4px_0px_0px_rgba(51,184,66,0.9)] hover:-translate-y-1 transition-transform duration-200 flex flex-col gap-2 h-[14rem]">

                                <div class="h-28 w-full overflow-hidden rounded-[1rem]">
                                    <img src="<?php echo htmlspecialchars($favorite['image']); ?>"
                                        alt="<?php echo htmlspecialchars($favorite['title']); ?>"
                                        class="w-full h-full object-cover"
                                        onerror="this.onerror=null; this.src='../img/plumbob.webp';">
                                </div>

                                <div
                                    class="px-2 py-0.5 bg-[#F0EEE9] rounded-full shadow-[0px_1px_0px_1px_rgba(158,158,158,1)] text-[#3769a9] font-medium text-sm text-center truncate">
                                    <?php echo htmlspecialchars($favorite['title']); ?>
                                </div>

                                <div class="flex items-center justify-between px-1">
                                    <span
                                        class="bg-[#33b842] text-white text-[9px] font-bold px-2.5 py-1 rounded-full shadow-[0px_1px_0px_1px_rgba(0,0,0,0.08)] border border-white/20 whitespace-nowrap">
                                        <?php echo htmlspecialchars($favorite['type']); ?>
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="bg-[#F0EEE9] px-2 py-0.5 rounded-full shadow-[0px_1px_0px_1px_rgba(158,158,158,1)] text-[#3769a9] font-bold text-xs">
                                            <?php echo htmlspecialchars($favorite['price']); ?>
                                        </div>
                                        <div class="flex items-center gap-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-red-400"
                                                fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                            <span
                                                class="text-[9px] font-bold text-[#3769a9]"><?php echo $favorite['favorites']; ?></span>
                                        </div>
                                    </div>
                                </div>

                            </a>
                        <?php endforeach; ?>

                        <?php for ($i = count($favoriteGames); $i < 4; $i++): ?>
                            <div
                                class="h-[14rem] rounded-[1.5rem] border-2 border-dashed border-[#3769a9] border-opacity-20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#3769a9] opacity-20"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                        <?php endfor; ?>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div id="edit-modal-overlay" class="fixed inset-0 z-50 flex items-center justify-center hidden"
        style="background: rgba(30, 50, 90, 0.55); backdrop-filter: blur(4px);">

        <div
            class="relative bg-[#F0EEE9] bg-opacity-98 rounded-[2rem] shadow-[0px_8px_0px_0px_rgba(51,184,66,0.9)] w-full max-w-lg mx-4 mt-8">

            <div class="absolute -top-6 left-1/2 -translate-x-1/2 z-10">
                <span
                    class="text-2xl font-bold text-[#3769a9] bg-[#F0EEE9] inline-block px-10 py-2 rounded-full shadow-[0px_2px_0px_1.5px_rgba(51,184,66,1)] whitespace-nowrap">
                    Modifier le profil
                </span>
            </div>

            <button type="button" id="close-edit-modal"
                class="absolute top-3 right-4 w-8 h-8 bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] flex items-center justify-center text-[#3769a9] hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="pt-8 px-5 pb-5 space-y-2.5">

                <form method="POST" class="bg-white rounded-[1.2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-2.5 space-y-1.5">
                    <p class="text-[#3769a9] font-bold text-sm border-b-2 border-[#33b842] pb-0.5 w-fit">Photo de profil
                    </p>
                    <div class="flex items-center justify-around gap-2">
                        <?php
                        $pics = [
                            ['path' => 'img/profilePics/green_plumbob.png', 'label' => 'Vert'],
                            ['path' => 'img/profilePics/cyan_plumbob.png', 'label' => 'Cyan'],
                            ['path' => 'img/profilePics/orange_plumbob.png', 'label' => 'Orange'],
                            ['path' => 'img/profilePics/red_plumbob.png', 'label' => 'Rouge'],
                        ];
                        foreach ($pics as $pic): ?>
                            <label class="cursor-pointer flex flex-col items-center gap-1 group">
                                <input type="radio" name="profile_pic" value="<?php echo htmlspecialchars($pic['path']); ?>"
                                    class="hidden peer" <?php echo (ltrim($profilePicPath, '/') === ltrim($pic['path'], '/')) ? 'checked' : ''; ?>>
                                <div
                                    class="w-10 h-10 rounded-full border-2 border-transparent peer-checked:border-[#33b842] overflow-hidden shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] transition duration-200 group-hover:scale-105">
                                    <img src="../<?php echo htmlspecialchars($pic['path']); ?>"
                                        alt="<?php echo $pic['label']; ?>" class="w-full h-full object-cover"
                                        onerror="this.onerror=null; this.src='../img/profilePics/green_plumbob.png';">
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-end mt-1">
                        <button type="submit" name="update_pic"
                            class="px-5 py-1 bg-[#3769a9] hover:bg-[#2a5885] text-white text-xs font-bold rounded-full shadow-lg transition duration-200 hover:scale-105">
                            Enregistrer
                        </button>
                    </div>
                </form>

                <form method="POST" class="bg-white rounded-[1.2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-2.5 space-y-1">
                    <label for="edit-username"
                        class="block text-[#3769a9] font-bold text-sm border-b-2 border-[#33b842] pb-0.5 w-fit">Nom
                        d'utilisateur</label>
                    <input type="text" id="edit-username" name="username"
                        value="<?php echo htmlspecialchars($username); ?>"
                        class="w-full px-4 py-1.5 text-sm bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200">
                    <div class="flex justify-end pt-1">
                        <button type="submit" name="update_username"
                            class="px-5 py-1 bg-[#3769a9] hover:bg-[#2a5885] text-white text-xs font-bold rounded-full shadow-lg transition duration-200 hover:scale-105">
                            Enregistrer
                        </button>
                    </div>
                </form>

                <form method="POST" class="bg-white rounded-[1.2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-2.5 space-y-1">
                    <label for="edit-email"
                        class="block text-[#3769a9] font-bold text-sm border-b-2 border-[#33b842] pb-0.5 w-fit">Adresse
                        e-mail</label>
                    <input type="email" id="edit-email" name="email"
                        value="<?php echo htmlspecialchars($userAccount?->getEmail() ?? ''); ?>"
                        class="w-full px-4 py-1.5 text-sm bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200">
                    <div class="flex justify-end pt-1">
                        <button type="submit" name="update_email"
                            class="px-5 py-1 bg-[#3769a9] hover:bg-[#2a5885] text-white text-xs font-bold rounded-full shadow-lg transition duration-200 hover:scale-105">
                            Enregistrer
                        </button>
                    </div>
                </form>

                <form method="POST" id="edit-password-form" class="bg-white rounded-[1.2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-2.5 space-y-1">
                    <p class="text-[#3769a9] font-bold text-sm border-b-2 border-[#33b842] pb-0.5 w-fit">Mot de passe
                    </p>
                    <div class="relative pb-2">
                        <input type="password" id="edit-password" name="password" placeholder="Nouveau mot de passe"
                            class="w-full px-4 py-1.5 pr-10 text-sm bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200">
                        <button type="button" id="toggle-edit-password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#3769a9] hover:text-[#2a5885] transition duration-200"
                            aria-label="Afficher/Masquer">
                            <svg id="eye-edit" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-slash-edit" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="password" id="edit-password-confirm" name="password_confirm"
                            placeholder="Confirmer le mot de passe"
                            class="w-full px-4 py-1.5 pr-10 text-sm bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200">
                        <button type="button" id="toggle-edit-password-confirm"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#3769a9] hover:text-[#2a5885] transition duration-200"
                            aria-label="Afficher/Masquer">
                            <svg id="eye-edit-confirm" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-slash-edit-confirm" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <p id="password-mismatch-msg" class="text-red-500 text-[10px] font-semibold hidden px-2">Les mots de
                        passe ne correspondent pas.</p>
                    <div class="flex justify-end pt-1">
                        <button type="submit" name="update_password" id="save-password-btn"
                            class="px-5 py-1 bg-[#3769a9] hover:bg-[#2a5885] text-white text-xs font-bold rounded-full shadow-lg transition duration-200 hover:scale-105">
                            Enregistrer
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        const overlay = document.getElementById('edit-modal-overlay');
        const openBtn = document.querySelector('button[title="Modifier le profil"]');
        const closeBtn = document.getElementById('close-edit-modal');

        openBtn.addEventListener('click', () => overlay.classList.remove('hidden'));
        closeBtn.addEventListener('click', () => overlay.classList.add('hidden'));
        overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.add('hidden'); });

        const togglePwd = (inputId, eyeId, slashId) => {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const slash = document.getElementById(slashId);
            if (!input) return;
            document.getElementById('toggle-' + inputId.replace('edit-', 'edit-'))?.addEventListener('click', () => {
                input.type = input.type === 'password' ? 'text' : 'password';
                eye.classList.toggle('hidden');
                slash.classList.toggle('hidden');
            });
        };

        document.getElementById('toggle-edit-password')?.addEventListener('click', () => {
            const input = document.getElementById('edit-password');
            input.type = input.type === 'password' ? 'text' : 'password';
            document.getElementById('eye-edit').classList.toggle('hidden');
            document.getElementById('eye-slash-edit').classList.toggle('hidden');
        });

        document.getElementById('toggle-edit-password-confirm')?.addEventListener('click', () => {
            const input = document.getElementById('edit-password-confirm');
            input.type = input.type === 'password' ? 'text' : 'password';
            document.getElementById('eye-edit-confirm').classList.toggle('hidden');
            document.getElementById('eye-slash-edit-confirm').classList.toggle('hidden');
        });

        document.getElementById('edit-password-form')?.addEventListener('submit', (e) => {
            const pwd = document.getElementById('edit-password').value;
            const confirm = document.getElementById('edit-password-confirm').value;
            const msg = document.getElementById('password-mismatch-msg');
            if (pwd !== confirm) {
                e.preventDefault();
                msg.classList.remove('hidden');
            } else {
                msg.classList.add('hidden');
            }
        });
    </script>
</body>

</html>