<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = dirname(__DIR__, 2);
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Model/Game.php';
require_once $root_path . '/src/Model/GamePegiDescriptor.php';
require_once $root_path . '/src/Model/PegiDescriptor.php';
require_once $root_path . '/src/Model/GameMedia.php';
require_once $root_path . '/src/Repository/GameRepository.php';
require_once $root_path . '/src/Repository/GamePegiDescriptorRepository.php';
require_once $root_path . '/src/Repository/PegiDescriptorRepository.php';
require_once $root_path . '/src/Repository/GameMediaRepository.php';
require_once $root_path . '/src/Enum/GameType.php';
require_once $root_path . '/src/Enum/PegiAge.php';

// Check if the user is connected
$isConnected = AuthMiddleware::is_connected($_SESSION);
$currentUserId = ($isConnected && isset($_SESSION['userId']) && is_numeric($_SESSION['userId'])) ? (int) $_SESSION['userId'] : null;

$gameId = isset($_GET['id']) ? (int) $_GET['id'] : 1;

$gameRepository = new GameRepository();
$userFavoriteRepository = new UserFavoriteRepository();
$game = $gameRepository->findById($gameId);

$gameMediaRepo = new GameMediaRepository();
$gameMedias = $gameMediaRepo->findByGameId($gameId);

// If the game is not found, redirect to the homepage
if (!$game) {
    header('Location: ../index.php');
    exit();
}

$favoriteGameIds = [];
if ($currentUserId !== null) {
    foreach ($userFavoriteRepository->findAllByUserId($currentUserId) as $favorite) {
        $favoriteGameIds[$favorite->getIdGame()] = true;
    }
}
$isFavorite = isset($favoriteGameIds[$gameId]);

$gamePegiDescriptorRepository = new GamePegiDescriptorRepository();
$pegiDescriptorRepository = new PegiDescriptorRepository();
$gamePegiDescriptors = $gamePegiDescriptorRepository->findByGameId($gameId);
$pegiDescriptors = [];
// Associative array ('label' : 'path')
$pegiDescriptorImages = [];

foreach ($gamePegiDescriptors as $gamePegiDescriptor) {
    $pegiDescriptor = $pegiDescriptorRepository->findById($gamePegiDescriptor->getIdDescriptor());
    if ($pegiDescriptor !== null) {
        $pegiDescriptors[] = $pegiDescriptor;
        // Get the label and normalize it
        $label = $pegiDescriptor->getLabel();
        $normalizedLabel = strtolower(str_replace(' ', '-', $label));
        // Get the corresponding image name
        $pegiDescriptorImages[$label] = $normalizedLabel . '.jpg';
    }
}


$pegiAgeValue = $game->getPegiAge()->value;
$pegiAgeImage = 'age-' . $pegiAgeValue . '.jpg';


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($game->getGameName()); ?> - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>
    <div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center bg-[#3769a9]">
        <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover"
            onerror="this.style.display='none';">
    </div>

    <div id="content" class="relative flex justify-center min-h-screen p-4 pt-12">

        <!--        NAVBAR Section-->

        <?php
        $basePath = '../';
        $showLoginButton = !$isConnected;
        $showProfilePic = $isConnected;
        $searchPlaceholder = 'Recherche :';
        include '../includes/header.php';
        ?>

        <div class="absolute top-28 w-full max-w-7xl px-4 pb-12">

            <div
                class="bg-[#F0EEE9] bg-opacity-80 rounded-4xl shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] p-8 m-8 space-y-8">

                <!--            GAME HEADER Section-->
                <div>
                    <!--                TITLE -->
                    <div class="mb-6">
                        <h1 class="text-5xl font-bold text-[#3769a9] pb-2 inline-block border-b-4 border-[#33b842]">
                            <?php echo htmlspecialchars($game->getGameName()); ?> :
                        </h1>
                    </div>

                    <!--                GRID -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <div class="lg:col-span-2">
                            <div class="relative h-[30rem] w-full overflow-hidden rounded-[2rem]">
                                <img src="../<?php echo htmlspecialchars($game->getImageHeroPath()); ?>"
                                    alt="<?php echo htmlspecialchars($game->getGameName()); ?>"
                                    class="w-full h-full object-cover"
                                    onerror="this.onerror=null; this.src='../img/plumbob.webp';">
                            </div>
                        </div>

                        <div class="space-y-4">

                            <div class="relative h-48 w-full overflow-hidden rounded-[2rem]">
                                <img src="../<?php echo htmlspecialchars($game->getImageTitlePath()); ?>"
                                    alt="Miniature <?php echo htmlspecialchars($game->getGameName()); ?>"
                                    class="w-full h-full object-contain"
                                    onerror="this.onerror=null; this.src='../img/plumbob.webp';">
                            </div>

                            <div class="flex flex-col items-start gap-4">
                                <span
                                    class="bg-[#33b842] text-white text-sm font-bold px-8 py-3 rounded-full shadow-[0px_2px_0px_1.5px_rgba(0,0,0,0.1)] border border-white/20 capitalize">
                                    <?php echo htmlspecialchars($game->getGameType()->value); ?>
                                </span>
                                <div class="w-full h-1 bg-[#33b842] rounded"></div>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <div class="flex-1 flex flex-col gap-4">
                                    <div
                                        class="bg-white px-6 py-3 rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] text-center">
                                        <span class="text-3xl font-bold text-[#3769a9]">
                                            <?php echo number_format($game->getPrice(), 2, ',', ' '); ?> €
                                        </span>
                                    </div>
                                    <div class="w-full h-1 bg-[#33b842] rounded"></div>
                                </div>

                                <div class="flex flex-col items-center">
                                    <button
                                        class="bg-white p-3 rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500"
                                            fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                    <span
                                        class="text-sm font-bold text-[#3769a9] mt-1"><?php echo $game->getFavoritesNumber(); ?></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-start gap-3 pt-2 flex-wrap">
                                <div class="bg-white p-2 rounded-lg shadow-md">
                                    <?php if ($pegiAgeImage !== null): ?>
                                        <img src="../img/pegi/age/<?php echo htmlspecialchars($pegiAgeImage); ?>"
                                            alt="PEGI <?php echo htmlspecialchars($pegiAgeValue); ?>"
                                            class="w-16 h-16 rounded"
                                            onerror="this.onerror=null; this.style.display='none';">
                                    <?php else: ?>
                                        <div
                                            class="w-16 h-16 bg-yellow-400 rounded flex items-center justify-center border-2 border-black">
                                            <span
                                                class="text-3xl font-bold text-black"><?php echo htmlspecialchars($pegiAgeValue); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php foreach ($pegiDescriptorImages as $label => $image): ?>
                                    <div class="bg-white p-2 rounded-lg shadow-md">
                                        <img src="../img/pegi/desc/<?php echo htmlspecialchars($image); ?>"
                                            alt="Descripteur PEGI: <?php echo htmlspecialchars($label); ?>"
                                            class="w-16 h-16 rounded object-cover"
                                            onerror="this.onerror=null; this.style.display='none';">
                                    </div>
                                <?php endforeach; ?>

                                <?php if (empty($pegiDescriptors)): ?>
                                    <span class="text-sm font-semibold text-[#3769a9]">Aucun descripteur PEGI</span>
                                <?php endif; ?>
                            </div>

                        </div>

                    </div>
                </div>

                <hr class="border-t-2 border-[#3769a9] opacity-20">

                <!--            CONTENT Section-->
                <div>
                    <div class="mb-6">
                        <h2 class="text-4xl font-bold text-[#3769a9] pb-2 inline-block border-b-4 border-[#33b842]">
                            Contenu :
                        </h2>
                    </div>

                    <div class="text-lg text-[#3769a9] leading-relaxed">
                        <p>
                            <?php echo htmlspecialchars($game->getGameDesc()); ?>
                        </p>
                    </div>
                </div>

                <hr class="border-t-2 border-[#3769a9] opacity-20">

                <!--            CAROUSEL Section-->
                <div>
                    <div class="mb-6">
                        <h2 class="text-4xl font-bold text-[#3769a9] pb-2 inline-block border-b-4 border-[#33b842]">
                            Carrousel :
                        </h2>
                    </div>

                    <?php if (empty($gameMedias)): ?>
                        <p class="text-[#3769a9] col-span-full">Aucun média disponible pour ce jeu.</p>
                    <?php else: ?>
                        <div id="carouselWrapper"
                            class="relative w-full overflow-hidden rounded-[2rem] bg-[#F0EEE9] p-4 shadow-inner">
                            <div id="carouselTrack"
                                class="flex overflow-x-auto gap-4 scroll-smooth hide-scrollbar w-full snap-x snap-mandatory">
                                <?php foreach ($gameMedias as $index => $media): ?>
                                    <div
                                        class="snap-center shrink-0 w-full md:w-96 h-64 relative overflow-hidden rounded-[1.5rem] shadow-lg border-[3px] border-transparent hover:border-[#33b842] transition duration-300">
                                        <img src="../<?php echo htmlspecialchars($media->getFilePath()); ?>"
                                            alt="Image galerie <?php echo $index + 1; ?>" class="w-full h-full object-cover"
                                            onerror="this.onerror=null; this.src='../img/plumbob.webp';">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const track = document.getElementById('carouselTrack');
                                if (!track) return;

                                let scrollSpeed = 1; // pixel per frame
                                let direction = 1;
                                let isHovered = false;
                                let animationFrameId;

                                track.addEventListener('mouseenter', () => isHovered = true);
                                track.addEventListener('mouseleave', () => isHovered = false);
                                track.addEventListener('touchstart', () => isHovered = true);
                                track.addEventListener('touchend', () => isHovered = false);

                                function autoScroll() {
                                    if (!isHovered) {
                                        track.scrollLeft += scrollSpeed * direction;

                                        // Bounce when reaching ends
                                        if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 1) {
                                            direction = -1; // Go left
                                        } else if (track.scrollLeft <= 0) {
                                            direction = 1; // Go right
                                        }
                                    }
                                    animationFrameId = requestAnimationFrame(autoScroll);
                                }

                                // Start animation if content overflows
                                if (track.scrollWidth > track.clientWidth) {
                                    animationFrameId = requestAnimationFrame(autoScroll);
                                }
                            });
                        </script>
                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

    <script src="../js/favoriteForm.js"></script>
</body>

</html>