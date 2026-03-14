<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = dirname(__DIR__, 2);
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Database/DatabaseConnection.php';
require_once $root_path . '/src/Repository/PegiDescriptorRepository.php';
require_once $root_path . '/src/Enum/PegiAge.php';

$isConnected = AuthMiddleware::is_connected($_SESSION);

$pegiAges = [];
foreach (PegiAge::cases() as $pegiAge) {
    $pegiAges[] = [
        'value' => $pegiAge->value,
        'image' => 'age-' . $pegiAge->value . '.jpg'
    ];
}

$pegiDescriptorRepository = new PegiDescriptorRepository();
$pegiDescriptors = [];

foreach ($pegiDescriptorRepository->findAll() as $pegiDescriptor) {
    $label = $pegiDescriptor->getLabel();
    $normalizedLabel = strtolower(str_replace(' ', '-', $label));

    $pegiDescriptors[] = [
        'label' => $label,
        'image' => $normalizedLabel . '.jpg'
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajout d'un jeu - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
<div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center bg-[#3769a9]">
    <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover" onerror="this.style.display='none';">
</div>

<div id="content" class="relative flex justify-center min-h-screen p-4 pt-12">

    <?php
    $basePath = '../';
    $showLoginButton = !$isConnected;
    $showProfilePic = $isConnected;
    $searchPlaceholder = 'Recherche :';
    include '../includes/header.php';
    ?>

    <div class="absolute top-32 w-full max-w-7xl px-4 pb-4">

        <div class="bg-[#F0EEE9] bg-opacity-95 rounded-[3rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] overflow-hidden">

            <div class="px-5 pt-3 pb-2">
                <h1 class="text-2xl font-bold text-[#3769a9]">Ajout d'un jeu</h1>
            </div>
            <div class="h-1.5 bg-[#33b842]"></div>

            <form method="post" action="" class="p-5 space-y-3">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                    <div class="space-y-2">

                        <div>
                            <label for="gameName" class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-0.5">
                                Titre<span class="text-red-500">*</span>
                            </label>
                            <input id="gameName"
                                   name="gameName"
                                   type="text"
                                   placeholder="Nom du jeu"
                                   class="w-full bg-transparent text-[#3769a9] text-sm border-b-2 border-[#3769a9] outline-none placeholder:text-[#3769a9]/40 pb-0.5">
                        </div>

                        <div>
                            <label for="gameDesc" class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-0.5">
                                Description<span class="text-red-500">*</span>
                            </label>
                            <textarea id="gameDesc"
                                      name="gameDesc"
                                      rows="1"
                                      placeholder="Description du jeu"
                                      class="w-full resize-none bg-transparent text-[#3769a9] text-sm border-b-2 border-[#3769a9] outline-none placeholder:text-[#3769a9]/40 pb-0.5"></textarea>
                        </div>

                        <div>
                            <label for="price" class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-0.5">
                                Prix<span class="text-red-500">*</span>
                            </label>
                            <input id="price"
                                   name="price"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   placeholder="0,00"
                                   class="w-full bg-transparent text-[#3769a9] text-sm border-b-2 border-[#3769a9] outline-none placeholder:text-[#3769a9]/40 pb-0.5">
                        </div>

                        <div>
                            <p class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-1">
                                Type<span class="text-red-500">*</span>
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                <input type="radio" id="typePc" name="gameType" value="pc" class="hidden peer/typePc">
                                <label for="typePc" class="px-4 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] text-sm font-bold border-2 border-[#3769a9] cursor-pointer transition duration-200 peer-checked/typePc:bg-[#33b842] peer-checked/typePc:text-white peer-checked/typePc:border-[#33b842]">
                                    PC
                                </label>

                                <input type="radio" id="typeConsole" name="gameType" value="console" class="hidden peer/typeConsole">
                                <label for="typeConsole" class="px-4 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] text-sm font-bold border-2 border-[#3769a9] cursor-pointer transition duration-200 peer-checked/typeConsole:bg-[#33b842] peer-checked/typeConsole:text-white peer-checked/typeConsole:border-[#33b842]">
                                    Console
                                </label>

                                <input type="radio" id="typeSmartphone" name="gameType" value="smartphone" class="hidden peer/typeSmartphone">
                                <label for="typeSmartphone" class="px-4 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] text-sm font-bold border-2 border-[#3769a9] cursor-pointer transition duration-200 peer-checked/typeSmartphone:bg-[#33b842] peer-checked/typeSmartphone:text-white peer-checked/typeSmartphone:border-[#33b842]">
                                    Smartphone
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                            <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-28 flex flex-col items-center justify-center text-center cursor-pointer transition duration-200 hover:bg-white/40">
                                <input type="file" name="imageHero" class="hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-[#3769a9] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    <circle cx="12" cy="12" r="3.5" stroke-width="1.8"></circle>
                                </svg>
                                <span class="text-sm md:text-base lg:text-sm font-medium text-[#3769a9]">Photo principale<span class="text-red-500">*</span></span>
                            </label>

                            <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-28 flex flex-col items-center justify-center text-center cursor-pointer transition duration-200 hover:bg-white/40">
                                <input type="file" name="imageTitle" class="hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-[#3769a9] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    <circle cx="12" cy="12" r="3.5" stroke-width="1.8"></circle>
                                </svg>
                                <span class="text-sm md:text-base lg:text-sm font-medium text-[#3769a9]">Photo secondaire<span class="text-red-500">*</span></span>
                            </label>

                        </div>

                        <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-28 flex items-center gap-2 cursor-pointer transition duration-200 hover:bg-white/40">
                            <input type="file" name="galleryImages[]" multiple class="hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#3769a9] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                <circle cx="12" cy="12" r="3.5" stroke-width="1.8"></circle>
                            </svg>
                            <span class="text-[#3769a9] text-sm md:text-base lg:text-sm leading-tight font-medium">
                                Ajoutez des photos pour illustrer ce nouveau jeu
                            </span>
                        </label>

                    </div>

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                    <div>
                        <p class="text-xl font-medium text-[#3769a9] mb-1.5">Choisir l'age requis<span class="text-red-500">*</span></p>
                        <div class="flex flex-wrap gap-1">
                            <?php foreach ($pegiAges as $index => $pegiAge): ?>
                                <?php $inputId = 'pegiAge' . $index; ?>
                                <input type="radio"
                                       id="<?php echo htmlspecialchars($inputId); ?>"
                                       name="pegiAge"
                                       value="<?php echo htmlspecialchars($pegiAge['value']); ?>"
                                       class="hidden peer/<?php echo htmlspecialchars($inputId); ?>">
                                <label for="<?php echo htmlspecialchars($inputId); ?>"
                                       class="bg-white p-1 rounded-xl border-2 border-transparent shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] cursor-pointer transition duration-200 peer-checked/<?php echo htmlspecialchars($inputId); ?>:border-[#33b842] peer-checked/<?php echo htmlspecialchars($inputId); ?>:scale-105">
                                    <img src="../img/pegi/age/<?php echo htmlspecialchars($pegiAge['image']); ?>"
                                         alt="PEGI <?php echo htmlspecialchars($pegiAge['value']); ?>"
                                         class="w-10 h-10 rounded object-cover"
                                         onerror="this.onerror=null; this.style.display='none';">
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <p class="text-xl font-medium text-[#3769a9] mb-1.5">Choisir le descripteur de contenu</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1">
                            <?php foreach ($pegiDescriptors as $index => $pegiDescriptor): ?>
                                <?php $inputId = 'descriptor' . $index; ?>
                                <input type="checkbox"
                                       id="<?php echo htmlspecialchars($inputId); ?>"
                                       name="pegiDescriptors[]"
                                       value="<?php echo htmlspecialchars($pegiDescriptor['label']); ?>"
                                       class="hidden peer/<?php echo htmlspecialchars($inputId); ?>">
                                <label for="<?php echo htmlspecialchars($inputId); ?>"
                                       class="bg-white p-1 rounded-xl border-2 border-transparent shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] flex flex-col items-center gap-0.5 cursor-pointer transition duration-200 peer-checked/<?php echo htmlspecialchars($inputId); ?>:border-[#33b842] peer-checked/<?php echo htmlspecialchars($inputId); ?>:scale-105">
                                    <img src="../img/pegi/desc/<?php echo htmlspecialchars($pegiDescriptor['image']); ?>"
                                         alt="<?php echo htmlspecialchars($pegiDescriptor['label']); ?>"
                                         class="w-7 h-7 rounded object-cover"
                                         onerror="this.onerror=null; this.style.display='none';">
                                    <span class="text-[9px] text-center font-semibold text-[#3769a9] leading-tight"><?php echo htmlspecialchars($pegiDescriptor['label']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <div class="flex justify-end pt-0.5">
                    <button type="button"
                            class="px-5 py-1 bg-[#3769a9] hover:bg-[#2a5885] text-white text-sm font-bold rounded-full shadow-lg transition duration-200 ease-in-out transform hover:scale-105">
                        Ajouter le jeu
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>
</body>
</html>
