<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SELECT the actual DIR (public/views), and ask to go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

// DEPENDENCIES
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Database/DatabaseConnection.php';
require_once $root_path . '/src/Model/Game.php';
require_once $root_path . '/src/Model/GameMedia.php';
require_once $root_path . '/src/Model/GamePegiDescriptor.php';
require_once $root_path . '/src/Repository/GameRepository.php';
require_once $root_path . '/src/Repository/GameMediaRepository.php';
require_once $root_path . '/src/Repository/GamePegiDescriptorRepository.php';
require_once $root_path . '/src/Repository/PegiDescriptorRepository.php';
require_once $root_path . '/src/Enum/GameType.php';
require_once $root_path . '/src/Enum/PegiAge.php';

function normalizePegiLabelToFileName(string $label): string {
    $normalized = trim($label);
    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);

    if ($transliterated !== false) {
        $normalized = $transliterated;
    }

    $normalized = strtolower($normalized);
    $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized);
    $normalized = trim((string)$normalized, '-');

    return ($normalized === '' ? 'pegi' : $normalized) . '.jpg';
}

function getGameTypeLabel(GameType $gameType): string {
    return match ($gameType) {
        GameType::PC => 'PC',
        GameType::CONSOLE => 'Console',
        GameType::SMARTPHONE => 'Smartphone'
    };
}

function mapUploadErrorMessage(int $errorCode): string {
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "Le fichier depasse la taille autorisee par le serveur.",
        UPLOAD_ERR_PARTIAL => "Le fichier n'a ete envoye que partiellement.",
        UPLOAD_ERR_NO_TMP_DIR => "Le dossier temporaire est introuvable.",
        UPLOAD_ERR_CANT_WRITE => "Impossible d'ecrire le fichier sur le disque.",
        UPLOAD_ERR_EXTENSION => "Un module PHP a bloque l'upload du fichier.",
        default => "Erreur inconnue pendant l'upload."
    };
}

function normalizeMultipleUploadField(?array $filesField): array {
    if (!is_array($filesField) || !isset($filesField['name']) || !is_array($filesField['name'])) {
        return [];
    }

    $normalized = [];
    $count = count($filesField['name']);

    for ($i = 0; $i < $count; $i++) {
        $normalized[] = [
            'name' => (string)($filesField['name'][$i] ?? ''),
            'type' => (string)($filesField['type'][$i] ?? ''),
            'tmp_name' => (string)($filesField['tmp_name'][$i] ?? ''),
            'error' => (int)($filesField['error'][$i] ?? UPLOAD_ERR_NO_FILE),
            'size' => (int)($filesField['size'][$i] ?? 0)
        ];
    }

    return $normalized;
}

function isUploadedFileMeaningful(?array $file): bool {
    if (!is_array($file)) {
        return false;
    }

    $name = trim((string)($file['name'] ?? ''));
    $size = (int)($file['size'] ?? 0);
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

    return $error !== UPLOAD_ERR_NO_FILE && $name !== '' && $size > 0;
}

function moveUploadedImage(array $file, string $prefix, string $uploadAbsoluteDir, string $uploadRelativeDir): string {
    $extension = strtolower((string)pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $uniqueName = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(5)) . '.' . $extension;
    $destinationAbsolutePath = $uploadAbsoluteDir . DIRECTORY_SEPARATOR . $uniqueName;

    if (!move_uploaded_file((string)$file['tmp_name'], $destinationAbsolutePath)) {
        throw new RuntimeException("Impossible de deplacer un fichier uploade.");
    }

    return $uploadRelativeDir . '/' . $uniqueName;
}

// Check if the user is connected
$isConnected = AuthMiddleware::is_connected($_SESSION);

// DYNAMIC CHOICES
$gameTypes = GameType::cases();
$pegiAges = [];
foreach (PegiAge::cases() as $pegiAge) {
    $pegiAges[] = [
        'value' => $pegiAge->value,
        'image' => 'age-' . $pegiAge->value . '.jpg'
    ];
}

$pegiDescriptorRepository = new PegiDescriptorRepository();
$pegiDescriptors = [];
$allowedDescriptorIds = [];

foreach ($pegiDescriptorRepository->findAll() as $pegiDescriptor) {
    $descriptorId = $pegiDescriptor->getId();
    if ($descriptorId === null) {
        continue;
    }

    $label = $pegiDescriptor->getLabel();
    $pegiDescriptors[] = [
        'id' => $descriptorId,
        'label' => $label,
        'image' => normalizePegiLabelToFileName($label)
    ];
    $allowedDescriptorIds[$descriptorId] = true;
}

$error_msgs = [];
$success_msg = '';
$form_values = [
    'gameTitle' => '',
    'gameDesc' => '',
    'gamePrice' => '',
    'gameType' => '',
    'gamePegiAge' => '',
    'gamePegiDescriptors' => []
];

$allowedExtensions = ['png', 'jpg', 'jpeg', 'webp'];
$maxSizePerFile = 10 * 1024 * 1024;
$maxTotalUploadSize = 50 * 1024 * 1024;

// If the request is a POST we start the game creation process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'addGame') {
    $form_values['gameTitle'] = trim((string)($_POST['gameTitle'] ?? ''));
    $form_values['gameDesc'] = trim((string)($_POST['gameDesc'] ?? ''));
    $form_values['gamePrice'] = trim((string)($_POST['gamePrice'] ?? ''));
    $form_values['gameType'] = trim((string)($_POST['gameType'] ?? ''));
    $form_values['gamePegiAge'] = trim((string)($_POST['gamePegiAge'] ?? ''));

    $rawDescriptorIds = $_POST['gamePegiDescriptors'] ?? [];
    $rawDescriptorIds = is_array($rawDescriptorIds) ? $rawDescriptorIds : [];

    if ($form_values['gameTitle'] === '') {
        $error_msgs[] = "Echec : Le titre est obligatoire.";
    }

    if ($form_values['gameDesc'] === '') {
        $error_msgs[] = "Echec : La description est obligatoire.";
    }

    if ($form_values['gamePrice'] === '' || !is_numeric($form_values['gamePrice']) || (float)$form_values['gamePrice'] < 0) {
        $error_msgs[] = "Echec : Le prix doit etre un nombre superieur ou egal a 0.";
    }

    $selectedGameType = null;
    try {
        $selectedGameType = GameType::from($form_values['gameType']);
    } catch (ValueError) {
        $error_msgs[] = "Echec : Le type de jeu selectionne est invalide.";
    }

    $selectedPegiAge = null;
    try {
        $selectedPegiAge = PegiAge::from($form_values['gamePegiAge']);
    } catch (ValueError) {
        $error_msgs[] = "Echec : L'age PEGI selectionne est invalide.";
    }

    $selectedDescriptorIds = [];
    foreach ($rawDescriptorIds as $descriptorIdRaw) {
        $descriptorIdString = trim((string)$descriptorIdRaw);
        if ($descriptorIdString === '' || !ctype_digit($descriptorIdString)) {
            $error_msgs[] = "Echec : Un descripteur PEGI selectionne est invalide.";
            continue;
        }

        $descriptorId = (int)$descriptorIdString;
        if (!isset($allowedDescriptorIds[$descriptorId])) {
            $error_msgs[] = "Echec : Un descripteur PEGI selectionne n'existe pas.";
            continue;
        }

        $selectedDescriptorIds[$descriptorId] = $descriptorId;
    }

    $selectedDescriptorIds = array_values($selectedDescriptorIds);
    $form_values['gamePegiDescriptors'] = [];
    foreach ($selectedDescriptorIds as $descriptorId) {
        $form_values['gamePegiDescriptors'][] = (string)$descriptorId;
    }

    $titlePic = $_FILES['gameTitlePic'] ?? null;
    $heroPic = $_FILES['gameHeroPic'] ?? null;
    $galleryPicturesRaw = normalizeMultipleUploadField($_FILES['gamePictures'] ?? null);

    $galleryPictures = [];
    foreach ($galleryPicturesRaw as $picture) {
        if (isUploadedFileMeaningful($picture)) {
            $galleryPictures[] = $picture;
        }
    }

    if (!isUploadedFileMeaningful($titlePic)) {
        $error_msgs[] = "Echec : La photo secondaire (gameTitlePic) est obligatoire.";
    }

    if (!isUploadedFileMeaningful($heroPic)) {
        $error_msgs[] = "Echec : La photo principale (gameHeroPic) est obligatoire.";
    }

    if (count($galleryPictures) > 6) {
        $error_msgs[] = "Echec : Vous pouvez envoyer au maximum 6 images dans gamePictures.";
    }

    $filesToValidate = [];
    if (isUploadedFileMeaningful($titlePic)) {
        $filesToValidate['Photo secondaire'] = $titlePic;
    }
    if (isUploadedFileMeaningful($heroPic)) {
        $filesToValidate['Photo principale'] = $heroPic;
    }

    foreach ($galleryPictures as $index => $galleryPicture) {
        $filesToValidate['Image galerie #' . ($index + 1)] = $galleryPicture;
    }

    $totalUploadSize = 0;
    foreach ($filesToValidate as $fileLabel => $file) {
        $errorCode = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            $error_msgs[] = $fileLabel . ' : ' . mapUploadErrorMessage($errorCode);
            continue;
        }

        $size = (int)($file['size'] ?? 0);
        if ($size <= 0) {
            $error_msgs[] = $fileLabel . " : le fichier est vide.";
            continue;
        }

        if ($size > $maxSizePerFile) {
            $error_msgs[] = $fileLabel . " : taille maximale depassee (10MB).";
        }

        $totalUploadSize += $size;

        $extension = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            $error_msgs[] = $fileLabel . " : format non autorise. Formats acceptes : png, jpg, jpeg, webp.";
        }
    }

    if ($totalUploadSize > $maxTotalUploadSize) {
        $error_msgs[] = "Echec : La taille totale des uploads depasse 50MB.";
    }

    // If no errors, proceed with the upload and database insertion
    if (empty($error_msgs)) {
        $uploadAbsoluteDir = $root_path . '/public/img/games';
        $uploadRelativeDir = 'img/games';

        // Ensure the upload directory exists
        if (!is_dir($uploadAbsoluteDir) && !mkdir($uploadAbsoluteDir, 0775, true) && !is_dir($uploadAbsoluteDir)) {
            $error_msgs[] = "Impossible de preparer le dossier de destination des images.";
        } else {
            $uploadedRelativePaths = [];

            try {
                // MOVE UPLOADED FILES TO DESTINATION
                $titlePicPath = moveUploadedImage($titlePic, 'title', $uploadAbsoluteDir, $uploadRelativeDir);
                $uploadedRelativePaths[] = $titlePicPath;

                $heroPicPath = moveUploadedImage($heroPic, 'hero', $uploadAbsoluteDir, $uploadRelativeDir);
                $uploadedRelativePaths[] = $heroPicPath;

                $galleryPaths = [];
                foreach ($galleryPictures as $galleryPicture) {
                    $galleryPath = moveUploadedImage($galleryPicture, 'media', $uploadAbsoluteDir, $uploadRelativeDir);
                    $galleryPaths[] = $galleryPath;
                    $uploadedRelativePaths[] = $galleryPath;
                }

                // DATABASE INSERTION
                $pdo = DatabaseConnection::getInstance();
                $pdo->beginTransaction();

                $gameRepository = new GameRepository();
                $gameMediaRepository = new GameMediaRepository();
                $gamePegiDescriptorRepository = new GamePegiDescriptorRepository();

                // INSERT GAME DATA
                $game = new Game(
                    $form_values['gameTitle'],
                    (float)$form_values['gamePrice'],
                    $selectedGameType,
                    $form_values['gameDesc'],
                    $heroPicPath,
                    $titlePicPath,
                    $selectedPegiAge
                );
                $gameRepository->insert($game);

                $gameId = $game->getId();
                if ($gameId === null) {
                    throw new RuntimeException("Creation du jeu impossible.");
                }

                // INSERT MEDIA DATA
                foreach ($galleryPaths as $galleryPath) {
                    $gameMediaRepository->insert(new GameMedia($galleryPath, $gameId));
                }

                // INSERT PEGI DESCRIPTORS
                foreach ($selectedDescriptorIds as $descriptorId) {
                    $gamePegiDescriptorRepository->insert(new GamePegiDescriptor($gameId, $descriptorId));
                }

                $pdo->commit();

                $success_msg = "Le jeu a ete ajoute avec succes.";
                $form_values = [
                    'gameTitle' => '',
                    'gameDesc' => '',
                    'gamePrice' => '',
                    'gameType' => '',
                    'gamePegiAge' => '',
                    'gamePegiDescriptors' => []
                ];
            } catch (Throwable) {
                $pdo = DatabaseConnection::getInstance();
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                // ROLLBACK: DELETE UPLOADED FILES ON ERROR
                foreach ($uploadedRelativePaths as $uploadedRelativePath) {
                    $absolutePath = $root_path . '/public/' . $uploadedRelativePath;
                    if (is_file($absolutePath)) {
                        @unlink($absolutePath);
                    }
                }

                $error_msgs[] = "Echec lors de l'ajout du jeu. Merci de reessayer.";
            }
        }
    }
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

    <div class="absolute top-28 w-full max-w-7xl px-4 pb-4">

        <div class="bg-[#F0EEE9] bg-opacity-95 rounded-[3rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] overflow-hidden">

            <div class="px-5 pt-3 pb-2">
                <h1 class="text-2xl font-bold text-[#3769a9]">Ajout d'un jeu</h1>
            </div>
            <div class="h-1.5 bg-[#33b842]"></div>

            <form method="post" action="" enctype="multipart/form-data" class="p-5 space-y-3" data-add-game-form>
                <input type="hidden" name="action" value="addGame">

                <?php if (!empty($error_msgs)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <?php foreach ($error_msgs as $error): ?>
                            <p class="font-medium"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_msg !== ''): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="status">
                        <p class="font-medium"><?php echo htmlspecialchars($success_msg); ?></p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                    <div class="space-y-2">

                        <div>
                            <label for="gameTitle" class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-0.5">
                                Titre<span class="text-red-500">*</span>
                            </label>
                            <input id="gameTitle"
                                   name="gameTitle"
                                   type="text"
                                   required
                                   value="<?php echo htmlspecialchars($form_values['gameTitle']); ?>"
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
                                      required
                                      placeholder="Description du jeu"
                                      class="w-full resize-none bg-transparent text-[#3769a9] text-sm border-b-2 border-[#3769a9] outline-none placeholder:text-[#3769a9]/40 pb-0.5"><?php echo htmlspecialchars($form_values['gameDesc']); ?></textarea>
                        </div>

                        <div>
                            <label for="gamePrice" class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-0.5">
                                Prix<span class="text-red-500">*</span>
                            </label>
                            <input id="gamePrice"
                                   name="gamePrice"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   required
                                   value="<?php echo htmlspecialchars($form_values['gamePrice']); ?>"
                                   placeholder="0,00"
                                   class="w-full bg-transparent text-[#3769a9] text-sm border-b-2 border-[#3769a9] outline-none placeholder:text-[#3769a9]/40 pb-0.5">
                        </div>

                        <div>
                            <p class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-1">
                                Type<span class="text-red-500">*</span>
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($gameTypes as $gameType): ?>
                                    <?php $typeValue = $gameType->value; ?>
                                    <?php $inputId = 'type-' . $typeValue; ?>
                                    <input type="radio"
                                           id="<?php echo htmlspecialchars($inputId); ?>"
                                           name="gameType"
                                           value="<?php echo htmlspecialchars($typeValue); ?>"
                                           class="hidden peer/<?php echo htmlspecialchars($inputId); ?>"
                                           <?php echo $form_values['gameType'] === $typeValue ? 'checked' : ''; ?>>
                                    <label for="<?php echo htmlspecialchars($inputId); ?>"
                                           class="px-4 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] text-sm font-bold border-2 border-[#3769a9] cursor-pointer transition duration-200 peer-checked/<?php echo htmlspecialchars($inputId); ?>:bg-[#33b842] peer-checked/<?php echo htmlspecialchars($inputId); ?>:text-white peer-checked/<?php echo htmlspecialchars($inputId); ?>:border-[#33b842]">
                                        <?php echo htmlspecialchars(getGameTypeLabel($gameType)); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>

                    <div class="space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                            <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-28 flex flex-col items-center justify-center text-center cursor-pointer transition duration-200 hover:bg-white/40">
                                <input type="file" name="gameHeroPic" id="gameHeroPic" accept=".png,.jpg,.jpeg,.webp" required class="hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-[#3769a9] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    <circle cx="12" cy="12" r="3.5" stroke-width="1.8"></circle>
                                </svg>
                                <span class="text-sm md:text-base lg:text-sm font-medium text-[#3769a9]">Photo principale<span class="text-red-500">*</span></span>
                            </label>

                            <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-28 flex flex-col items-center justify-center text-center cursor-pointer transition duration-200 hover:bg-white/40">
                                <input type="file" name="gameTitlePic" id="gameTitlePic" accept=".png,.jpg,.jpeg,.webp" required class="hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-[#3769a9] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                    <circle cx="12" cy="12" r="3.5" stroke-width="1.8"></circle>
                                </svg>
                                <span class="text-sm md:text-base lg:text-sm font-medium text-[#3769a9]">Photo secondaire<span class="text-red-500">*</span></span>
                            </label>

                        </div>

                        <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-28 flex items-center gap-2 cursor-pointer transition duration-200 hover:bg-white/40">
                            <input type="file" name="gamePictures[]" id="gamePictures" accept=".png,.jpg,.jpeg,.webp" multiple class="hidden">
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
                                       name="gamePegiAge"
                                       value="<?php echo htmlspecialchars($pegiAge['value']); ?>"
                                       class="hidden peer/<?php echo htmlspecialchars($inputId); ?>"
                                       <?php echo $form_values['gamePegiAge'] === $pegiAge['value'] ? 'checked' : ''; ?>>
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
                            <?php foreach ($pegiDescriptors as $pegiDescriptor): ?>
                                <?php $inputId = 'descriptor' . $pegiDescriptor['id']; ?>
                                <?php $isChecked = in_array((string)$pegiDescriptor['id'], $form_values['gamePegiDescriptors'], true); ?>
                                <input type="checkbox"
                                       id="<?php echo htmlspecialchars($inputId); ?>"
                                       name="gamePegiDescriptors[]"
                                       value="<?php echo htmlspecialchars((string)$pegiDescriptor['id']); ?>"
                                       class="hidden peer/<?php echo htmlspecialchars($inputId); ?>"
                                       <?php echo $isChecked ? 'checked' : ''; ?>>
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
                    <button type="submit"
                            class="px-5 py-1 bg-[#3769a9] hover:bg-[#2a5885] text-white text-sm font-bold rounded-full shadow-lg transition duration-200 ease-in-out transform hover:scale-105">
                        Ajouter le jeu
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>
<script src="../js/createGameValidation.js"></script>
</body>
</html>
