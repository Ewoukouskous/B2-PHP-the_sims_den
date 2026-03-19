<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = dirname(__DIR__, 2);
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Repository/UserFavoriteRepository.php';
require_once $root_path . '/src/Repository/GameRepository.php';
require_once $root_path . '/src/Enum/GameType.php';

$isConnected = AuthMiddleware::is_connected($_SESSION);

$favoriteGames = [];
if ($isConnected && isset($_SESSION['userId']) && is_numeric($_SESSION['userId'])) {
    $userFavoriteRepository = new UserFavoriteRepository();
    $gameRepository = new GameRepository();

    foreach ($userFavoriteRepository->findAllByUserId((int)$_SESSION['userId']) as $userFavorite) {
        $game = $gameRepository->findById($userFavorite->getIdGame());
        if ($game === null) {
            continue;
        }

        $typeLabel = match($game->getGameType()) {
            GameType::PC => 'PC',
            GameType::CONSOLE => 'Console',
            GameType::SMARTPHONE => 'Smartphone'
        };

        $favoriteGames[] = [
            'title' => $game->getGameName(),
            'description' => $game->getGameDesc(),
            'price' => number_format($game->getPrice(), 2, ',', ' ') . ' €',
            'image' => '../' . $game->getImageHeroPath(),
            'type' => $typeLabel
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes favoris - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        .description-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="h-screen overflow-hidden">
<div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center bg-[#3769a9]">
    <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover" onerror="this.style.display='none';">
</div>

<div id="content" class="relative flex justify-center h-screen overflow-hidden p-4 pt-12">

    <?php
    $basePath = '../';
    $showLoginButton = !$isConnected;
    $showProfilePic = $isConnected;
    $searchPlaceholder = 'Recherche :';
    include '../includes/header.php';
    ?>

    <div class="absolute top-28 bottom-6 w-full max-w-7xl px-4 overflow-y-auto pt-2">

        <div class="space-y-3 pb-2">
            <?php if (empty($favoriteGames)): ?>
                <section class="bg-[#F0EEE9] bg-opacity-90 rounded-[1.8rem] shadow-[0px_8px_0px_0px_rgba(51,184,66,0.9)] p-5 text-[#3769a9] text-xl font-medium text-center">
                    Aucun jeu en favoris pour le moment.
                </section>
            <?php endif; ?>

            <?php foreach ($favoriteGames as $favorite): ?>
                <section class="bg-[#F0EEE9] bg-opacity-90 rounded-[1.8rem] shadow-[0px_8px_0px_0px_rgba(51,184,66,0.9)] p-2.5 md:p-3">
                    <article class="grid grid-cols-1 lg:grid-cols-[240px_1fr_150px] gap-3 items-center">

                        <div class="h-36 md:h-40 lg:h-34 rounded-[1rem] overflow-hidden">
                            <img src="<?php echo htmlspecialchars($favorite['image']); ?>"
                                 alt="<?php echo htmlspecialchars($favorite['title']); ?>"
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.src='../img/plumbob.webp';">
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="bg-[#F0EEE9] rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] px-4 py-1.5 text-left text-[#3769a9] text-xl md:text-2xl font-medium leading-tight">
                                <?php echo htmlspecialchars($favorite['title']); ?>
                            </div>

                            <div class="flex flex-wrap gap-1.5 px-1">
                                <span class="bg-[#33b842] text-white text-xs font-bold px-2.5 py-0.5 rounded-full shadow-[0px_1px_0px_1px_rgba(0,0,0,0.08)] border border-white/20">
                                    <?php echo htmlspecialchars($favorite['type']); ?>
                                </span>
                            </div>

                            <div class="bg-[#F0EEE9] rounded-[1rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] px-4 py-2 text-left text-[#3769a9] text-lg md:text-xl font-medium h-[4.5rem] flex items-start justify-start overflow-hidden">
                                <p class="description-clamp w-full leading-tight text-left"><?php echo htmlspecialchars($favorite['description']); ?></p>
                            </div>
                        </div>

                        <div class="flex flex-col justify-center items-center gap-2.5 lg:pr-1">
                            <button type="button"
                                    aria-label="Retirer des favoris"
                                    class="w-14 h-14 bg-[#F0EEE9] rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] flex items-center justify-center text-red-500 hover:scale-105 transition duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 21s-6.716-4.437-9.428-8.13A5.727 5.727 0 0 1 12 6.422a5.727 5.727 0 0 1 9.428 6.448C18.716 16.563 12 21 12 21z"/>
                                </svg>
                            </button>

                            <div class="w-full bg-[#F0EEE9] rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] px-3 py-1.5 text-center text-[#3769a9] text-2xl font-medium whitespace-nowrap">
                                <?php echo htmlspecialchars($favorite['price']); ?>
                            </div>
                        </div>

                    </article>
                </section>
            <?php endforeach; ?>
        </div>

    </div>

</div>
</body>
</html>
