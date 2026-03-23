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
require_once $root_path . '/src/Repository/AchievementRepository.php';
require_once $root_path . '/src/Enum/GameType.php';
require_once $root_path . '/src/Enum/UserRole.php';

$isConnected = AuthMiddleware::is_connected();

if (!$isConnected) {
    header("Location: ../index.php");
    exit();
}

$currentUserId = ($isConnected && isset($_SESSION['userId']) && is_numeric($_SESSION['userId'])) ? (int) $_SESSION['userId'] : null;

// On regarde si un ID est passé en paramètre
$targetUserId = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $targetUserId = (int) $_GET['id'];
} else {
    $targetUserId = $currentUserId;
}

$userAccountRepository = new UserAccountRepository();
$profilePicRepository = new ProfilePicRepository();
$userFavoriteRepository = new UserFavoriteRepository();
$gameRepository = new GameRepository();
$userAchievementRepository = new UserAchievementRepository();
$achievementRepository = new AchievementRepository();

$userAccount = $targetUserId !== null ? $userAccountRepository->findById($targetUserId) : null;
$isOwnProfile = ($isConnected && $targetUserId === $currentUserId);

$errors = $_SESSION['profile_errors'] ?? [];
$success_msg = $_SESSION['profile_success'] ?? '';
unset($_SESSION['profile_errors'], $_SESSION['profile_success']);

// --- PROFILE UPDATES --- 
if ($isOwnProfile && $_SERVER['REQUEST_METHOD'] === 'POST' && $userAccount !== null) {

    // 1. Profile picture changes
    if (isset($_POST['update_pic']) && isset($_POST['newProfilePicId']) && $_POST['newProfilePicId'] != '') {
        $newProfilePicId = filter_var($_POST['newProfilePicId'], FILTER_VALIDATE_INT);

        if ($newProfilePicId === false || $newProfilePicId <= 0) {
            $errors[] = "La photo de profil sélectionnée est invalide";
        } elseif ($newProfilePicId === $userAccount->getIdProfilePic()) {
            $errors[] = "La photo de profil est la même que l'ancienne";
        } else {
            $profilePic = $profilePicRepository->findById($newProfilePicId);
            if ($profilePic === null) {
                $errors[] = "La photo de profil sélectionnée est inexistante";
            } else {
                $userAccount->setIdProfilePic($profilePic->getId());
                $userAccountRepository->update($userAccount);
                $_SESSION['profilePicPath'] = ltrim($profilePic->getPicturePath(), '/');
                $_SESSION['profile_success'] = "La modification de la photo de profil a été appliquée avec succès !";
                header("Location: profile.php?status=success");
                exit();
            }
        }
    }

    // 2. Username changes
    if (isset($_POST['update_username']) && isset($_POST['newUsername']) && $_POST['newUsername'] != '') {
        $newUsername = trim($_POST['newUsername']);

        if ($userAccount->getUsername() === $newUsername) {
            $errors[] = "Le nouveau nom d'utilisateur est le même que l'ancien";
        } elseif (strlen($newUsername) < 5 || strlen($newUsername) > 30) {
            $errors[] = "Le nom d'utilisateur doit faire entre 5 et 30 caractères";
        } elseif ($userAccountRepository->findByUsername($newUsername) !== null) {
            $errors[] = "Le nom d'utilisateur est déjà utilisé";
        } else {
            $userAccount->setUsername($newUsername);
            $userAccountRepository->update($userAccount);
            $_SESSION['username'] = $newUsername;
            $_SESSION['profile_success'] = "La modification du nom d'utilisateur a été appliquée avec succès !";
            header("Location: profile.php?status=success");
            exit();
        }
    }

    // 3. Email changes 
    if (isset($_POST['update_email']) && isset($_POST['newEmail']) && $_POST['newEmail'] != '') {
        $newEmail = trim($_POST['newEmail']);

        if ($userAccount->getEmail() === $newEmail) {
            $errors[] = "Le nouveau courriel est le même que l'ancien";
        } elseif (filter_var($newEmail, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = "Le courriel fourni n'est pas valide";
        } elseif ($userAccountRepository->findByEmail($newEmail) !== null) {
            $errors[] = "Le courriel est déjà associé à un autre compte utilisateur";
        } else {
            $userAccount->setEmail($newEmail);
            $userAccountRepository->update($userAccount);
            $_SESSION['profile_success'] = "La modification du courriel a été appliquée avec succès !";
            header("Location: profile.php?status=success");
            exit();
        }
    }

    // 4. Password changes
    if (
        isset($_POST['update_password']) && isset($_POST['newPassword']) && $_POST['newPassword'] != '' && isset($_POST['confirmNewPassword']) && $_POST['confirmNewPassword'] != ''
    ) {
        $newPassword = $_POST['newPassword'];
        $passwordConfirmation = $_POST['confirmNewPassword'];

        if ($newPassword !== $passwordConfirmation) {
            $errors[] = "Les deux mots de passe ne correspondent pas";
        } elseif (strlen($newPassword) < 8) {
            $errors[] = "Le mot de passe doit faire un minimum de 8 caractères";
        } else {
            $userAccount->setPasswordHash(password_hash($newPassword, PASSWORD_BCRYPT));
            $userAccountRepository->update($userAccount);
            $_SESSION['profile_success'] = "La modification du mot de passe a été appliquée avec succès !";
            header("Location: profile.php?status=success");
            exit();
        }
    }

    if (!empty($errors)) {
        $_SESSION['profile_errors'] = $errors;
        header("Location: profile.php?status=error");

        exit();
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
if ($targetUserId !== null) {
    $allFavorites = $userFavoriteRepository->findAllByUserId($targetUserId);
    foreach ($allFavorites as $userFavorite) {
        $game = $gameRepository->findById($userFavorite->getIdGame());
        if ($game === null) {
            continue;
        }
        $favoritesCount++;
        $typeLabel = ucfirst($game->getGameType()->value);
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

$achievements = [];
$achievementsCount = 0;
if ($targetUserId !== null) {
    $achievements = $achievementRepository->findAllWithUserProgress($targetUserId);
    foreach ($achievements as $achievement) {
        if ($achievement->getUnlockedAt() !== null) {
            $achievementsCount++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($username); ?> - Profil - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        .achievements-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .achievements-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="h-screen overflow-hidden">
    <div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center bg-[#3769a9]">
        <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover"
            onerror="this.style.display='none';">
    </div>

    <div id="content" class="relative flex justify-center h-screen overflow-hidden p-4 pt-5">

        <?php
        $basePath = '../';
        $showLoginButton = !$isConnected;
        $showProfilePic = $isConnected;
        $searchPlaceholder = 'Recherche :';
        include '../includes/header.php';
        ?>

        <div class="absolute top-40 w-full max-w-7xl px-4">

            <div
                class="relative bg-[#F0EEE9] bg-opacity-90 rounded-[1.8rem] shadow-[0px_8px_0px_0px_rgba(51,184,66,0.9)] p-6 pt-10 flex flex-col gap-4">

                <?php if ($isOwnProfile): ?>
                    <button type="button" title="Modifier le profil"
                        class="absolute top-4 right-4 w-10 h-10 bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] flex items-center justify-center text-[#3769a9] hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200 ease-in-out">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.213l-4.5 1 1-4.5L16.862 3.487z" />
                        </svg>
                    </button>
                <?php endif; ?>

                <div class="grid grid-cols-[1fr_auto_1fr] gap-8 items-start">

                    <div class="flex flex-col gap-3">
                        <h2
                            class="text-2xl font-bold text-[#3769a9] pb-1 inline-block border-b-3 border-[#33b842] w-fit">
                            Succès :
                        </h2>
                        <div id="achievements-scroll"
                            class="achievements-scroll h-[104px] overflow-y-auto overflow-x-visible pr-1">
                            <div class="grid grid-cols-5 gap-y-2 gap-x-3 w-fit">
                                <?php if (empty($achievements)): ?>
                                    <p class="text-[#3769a9] text-[10px] opacity-50 italic">Aucun succès débloqué.</p>
                                <?php else: ?>
                                    <?php foreach ($achievements as $achievement): ?>
                                        <div
                                            class="achievement-badge relative w-12 h-12 rounded-full border-2 border-[#3769a9] border-opacity-20 flex items-center justify-center transition-transform hover:scale-105 <?php echo $achievement->getUnlockedAt() ? 'bg-[#33b842] bg-opacity-20 border-[#33b842]' : 'bg-[#3769a9] bg-opacity-5'; ?>">

                                            <img src="../<?php echo htmlspecialchars(ltrim($achievement->getIconPath(), '/')); ?>"
                                                alt="Icon"
                                                class="w-9 h-9 object-contain <?php echo $achievement->getUnlockedAt() ? '' : 'grayscale opacity-40'; ?>">

                                            <!-- Tooltip personnalisé -->
                                            <div
                                                class="achievement-tooltip hidden flex-col bg-[#3769a9] text-white text-[10px] px-3 py-2 rounded-xl shadow-xl whitespace-nowrap pointer-events-none border border-white/20">
                                                <span
                                                    class="font-bold underline mb-0.5"><?php echo htmlspecialchars($achievement->getAchievementName()); ?></span>
                                                <span
                                                    class="opacity-90 italic"><?php echo htmlspecialchars($achievement->getAchievementDesc()); ?></span>
                                                <?php if ($achievement->getUnlockedAt()): ?>
                                                    <span class="mt-1.5 text-[9px] text-[#33b842] font-bold">Débloqué le :
                                                        <?php echo $achievement->getUnlockedAt()->format('d/m/Y'); ?></span>
                                                <?php else: ?>
                                                    <span
                                                        class="mt-1.5 text-[9px] text-gray-300 font-bold uppercase tracking-wider">Verrouillé</span>
                                                <?php endif; ?>
                                                <!-- Petite pointe du tooltip -->
                                                <div
                                                    class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#3769a9] rotate-45 border-r border-b border-white/10">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-1.5 pt-7" style="transform: translateY(-18px);">
                        <div
                            class="w-24 h-24 rounded-full bg-[#2a5885] border-4 border-[#33b842] overflow-hidden flex items-center justify-center shadow-lg -mt-20 z-10">
                            <img src="../<?php echo htmlspecialchars($profilePicPath); ?>"
                                alt="Photo de profil de <?php echo htmlspecialchars($username); ?>"
                                class="w-full h-full object-cover"
                                onerror="this.onerror=null; this.src='../img/profilePics/green_plumbob.png';">
                        </div>
                        <span
                            class="text-2xl font-bold text-[#3769a9]"><?php echo htmlspecialchars($username); ?></span>
                    </div>

                    <div class="flex flex-col gap-3 justify-self-end w-fit">
                        <h2
                            class="text-2xl font-bold text-[#3769a9] pb-1 inline-block border-b-3 border-[#33b842] w-fit">
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
                    <h2 class="text-2xl font-bold text-[#3769a9] pb-1 inline-block border-b-3 border-[#33b842] w-fit">
                        Favoris :
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 min-h-[14rem]">
                        <?php foreach ($favoriteGames as $favorite): ?>
                            <a href="game.php?id=<?php echo (int) $favorite['id']; ?>"
                                class="group relative bg-white rounded-[1.5rem] p-2.5 shadow-[0px_4px_0px_0px_rgba(51,184,66,0.9)] hover:-translate-y-1 transition-transform duration-200 flex flex-col gap-2 h-[14rem]">

                                <div class="h-35 w-full overflow-hidden rounded-[1rem]">
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

    <?php if ($isOwnProfile): ?>
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

                <div id="success-banner"
                    class="<?php echo $success_msg ? '' : 'hidden'; ?> mx-5 mt-10 p-3 bg-green-100 border-2 border-green-500 rounded-2xl flex items-center gap-3 shadow-md">
                    <div class="bg-green-500 rounded-full p-1 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-green-700 font-bold text-sm">
                        <?php echo htmlspecialchars($success_msg ?: 'Modifications enregistrées !'); ?>
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div id="error-banner" class="mx-5 mt-10 p-4 bg-red-100 border-2 border-red-500 rounded-2xl shadow-md">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-red-500 rounded-full p-1 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <p class="text-red-700 font-bold text-sm">Erreur(s) :</p>
                        </div>
                        <ul class="list-disc list-inside text-red-600 text-xs font-semibold space-y-1 ml-7">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div id="error-banner"
                        class="hidden mx-5 mt-10 p-3 bg-red-100 border-2 border-red-500 rounded-2xl flex items-center gap-3 shadow-md">
                        <div class="bg-red-500 rounded-full p-1 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <p id="error-banner-text" class="text-red-700 font-bold text-sm">Une erreur est survenue.</p>
                    </div>
                <?php endif; ?>


                <button type="button" id="close-edit-modal"
                    class="absolute top-3 right-4 w-8 h-8 bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] flex items-center justify-center text-[#3769a9] hover:ring-2 hover:ring-[#3769a9] hover:ring-opacity-50 transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="pt-8 px-5 pb-5 space-y-2.5">

                    <form method="POST"
                        class="bg-white rounded-[1.2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-2.5 space-y-1.5">
                        <p class="text-[#3769a9] font-bold text-sm border-b-2 border-[#33b842] pb-0.5 w-fit">Photo de profil
                        </p>
                        <div class="flex items-center justify-around gap-2">
                            <?php
                            $pics = $profilePicRepository->findAll();
                            foreach ($pics as $pic):
                                if (ltrim($pic->getPicturePath(), '/') === ltrim($profilePicPath, '/'))
                                    continue;
                                ?>
                                <label class="cursor-pointer flex flex-col items-center gap-1 group">
                                    <input type="radio" name="newProfilePicId" value="<?php echo (int) $pic->getId(); ?>"
                                        class="hidden peer">
                                    <div
                                        class="w-10 h-10 rounded-full border-2 border-transparent peer-checked:border-[#33b842] overflow-hidden shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] transition duration-200 group-hover:scale-105">
                                        <img src="../<?php echo htmlspecialchars(ltrim($pic->getPicturePath(), '/')); ?>"
                                            alt="Option" class="w-full h-full object-cover">
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

                    <form method="POST"
                        class="bg-white rounded-[1.2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-2.5 space-y-1">
                        <label for="edit-username"
                            class="block text-[#3769a9] font-bold text-sm border-b-2 border-[#33b842] pb-0.5 w-fit">Nom
                            d'utilisateur</label>
                        <input type="text" id="edit-username" name="newUsername"
                            placeholder="<?php echo htmlspecialchars($username); ?>"
                            class="w-full px-4 py-1.5 text-sm bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200">
                        <div class="flex justify-end pt-1">
                            <button type="submit" name="update_username"
                                class="px-5 py-1 bg-[#3769a9] hover:bg-[#2a5885] text-white text-xs font-bold rounded-full shadow-lg transition duration-200 hover:scale-105">
                                Enregistrer
                            </button>
                        </div>
                    </form>

                    <form method="POST"
                        class="bg-white rounded-[1.2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-2.5 space-y-1">
                        <label for="edit-email"
                            class="block text-[#3769a9] font-bold text-sm border-b-2 border-[#33b842] pb-0.5 w-fit">Adresse
                            e-mail</label>
                        <input type="email" id="edit-email" name="newEmail"
                            placeholder="<?php echo htmlspecialchars($userAccount?->getEmail() ?? ''); ?>"
                            class="w-full px-4 py-1.5 text-sm bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200">
                        <div class="flex justify-end pt-1">
                            <button type="submit" name="update_email"
                                class="px-5 py-1 bg-[#3769a9] hover:bg-[#2a5885] text-white text-xs font-bold rounded-full shadow-lg transition duration-200 hover:scale-105">
                                Enregistrer
                            </button>
                        </div>
                    </form>

                    <form method="POST" id="edit-password-form"
                        class="bg-white rounded-[1.2rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-2.5 space-y-1">
                        <p class="text-[#3769a9] font-bold text-sm border-b-2 border-[#33b842] pb-0.5 w-fit">Mot de passe
                        </p>
                        <div class="relative pb-2">
                            <input type="password" id="edit-password" name="newPassword" placeholder="Nouveau mot de passe"
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
                            <input type="password" id="edit-password-confirm" name="confirmNewPassword"
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
                if (pwd && confirm && pwd !== confirm) {
                    e.preventDefault();
                    alert("Les mots de passe ne correspondent pas.");
                }
            });

            // Gestion automatique des messages de statut (Succès / Erreur)
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status) {
                // Dans tous les cas on rouvre la pop-up
                overlay.classList.remove('hidden');

                if (status === 'success') {
                    const banner = document.getElementById('success-banner');
                    if (banner) banner.classList.remove('hidden');
                } else if (status === 'error') {
                    const banner = document.getElementById('error-banner');
                    const bannerText = document.getElementById('error-banner-text');
                    if (banner) banner.classList.remove('hidden');

                    // Message d'erreur spécifique si besoin
                    if (urlParams.get('msg') === 'mismatch') {
                        bannerText.innerText = "Les mots de passe ne correspondent pas.";
                    }
                }

                // Nettoyer l'URL proprement sans recharger
                window.history.replaceState({}, document.title, window.location.pathname);

                // On cache les bannières après 5 sec si elles sont visibles
                setTimeout(() => {
                    const successBanner = document.getElementById('success-banner');
                    const errorBanner = document.getElementById('error-banner');

                    const activeBanner = (successBanner && !successBanner.classList.contains('hidden'))
                        ? successBanner
                        : (!errorBanner.classList.contains('hidden') ? errorBanner : null);

                    if (activeBanner) {
                        activeBanner.classList.add('transition-opacity', 'duration-500', 'opacity-0');
                        setTimeout(() => {
                            activeBanner.classList.add('hidden');
                            activeBanner.classList.remove('opacity-0');
                        }, 500);
                    }
                }, 5000);
            }
        </script>
    <?php endif; ?>

    <script>
        function initAchievementTooltips() {
            // Select every achievement that trigger a tooltip
            const achievementBadges = document.querySelectorAll('.achievement-badge');
            // If there are no achievements on the page, we exit immediately to keep
            if (!achievementBadges.length) return;

            // Keep a single floating tooltip instance,we replace this instance on each hover to be sure there is only one
            let floatingTooltip = null;

            const createFloatingTooltip = (sourceTooltip) => {
                if (floatingTooltip) floatingTooltip.remove();
                floatingTooltip = sourceTooltip.cloneNode(true);
                // The base tooltip stay hidden in the badge we show only the clone by removing "hidden" from it
                floatingTooltip.classList.remove('hidden');
                // We add `fixed` + very high z-index for the tooltip is above everything
                floatingTooltip.classList.add('fixed', 'z-[9999]', 'flex');
                // Initialize with a start position before calculate them
                floatingTooltip.style.left = '0px';
                floatingTooltip.style.top = '0px';

                document.body.appendChild(floatingTooltip);
            };

            // Position the floating tooltip directly above the hovered badge
            const positionFloatingTooltip = (badge) => {
                if (!floatingTooltip) return;

                const badgeRect = badge.getBoundingClientRect();
                const tooltipRect = floatingTooltip.getBoundingClientRect();
                const spacing = 8;
                const viewportPadding = 8;

                // Calculs details:
                // horizontal: center tooltip on badge center
                // vertical: place tooltip above badge with a small gap
                // clamps: keep tooltip inside viewport limit
                let left = badgeRect.left + (badgeRect.width / 2) - (tooltipRect.width / 2);
                let top = badgeRect.top - tooltipRect.height - spacing;
                left = Math.max(viewportPadding, Math.min(left, window.innerWidth - tooltipRect.width - viewportPadding));
                top = Math.max(viewportPadding, top);

                floatingTooltip.style.left = `${left}px`;
                floatingTooltip.style.top = `${top}px`;
            };

            // Remove the floating tooltip and reset the reference
            const hideFloatingTooltip = () => {
                if (!floatingTooltip) return;
                floatingTooltip.remove();
                floatingTooltip = null;
            };

            achievementBadges.forEach((badge) => {
                const sourceTooltip = badge.querySelector('.achievement-tooltip');
                if (!sourceTooltip) return;

                // On hover start, clone and show tooltip on the badge
                badge.addEventListener('mouseenter', () => {
                    createFloatingTooltip(sourceTooltip);
                    positionFloatingTooltip(badge);
                });

                // Hide tooltip when the pointer leave the achievement
                badge.addEventListener('mouseleave', hideFloatingTooltip);

            });

        }
        // Call the function at the end of loading of the page
        initAchievementTooltips();
    </script>

</body>

</html>