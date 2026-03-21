<?php

if (session_status() === PHP_SESSION_NONE) {session_start();}

// SELECT the actual DIR (public/auth), and ask the go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

// DEPENDENCIES OF THE PAGE
require_once $root_path . '/src/Database/DatabaseConnection.php';
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Enum/GameType.php'; // To get alls game type
require_once $root_path . '/src/Enum/PegiAge.php'; // To get all pegi ages
require_once $root_path . '/src/Model/PegiDescriptor.php'; // To get all pegi descriptors
require_once $root_path . '/src/Repository/PegiDescriptorRepository.php';


// Check if the user is an admin, if not we redirect him to the index.php
if (!AuthMiddleware::is_admin($_SESSION)) {
    header('Location: /index.php');
    exit();
}

// Initiate usefully variables
// All GameTypes
$gameTypes = GameType::cases();
// All PegiAge
$pegiAges = PegiAge::cases();
// All PegiDescriptor
$pegiDescriptors = (new PegiDescriptorRepository())->findAll();
// Error msg
$error_msg = "";


// Check if it's a POST request that contain a 'action' field that contains 'addGame'
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action']) && $_POST['action'] === 'addGame' ) {

    // DEPENDENCIES TO CREATE GAME
    require_once $root_path . '/src/Model/Game.php';
    require_once $root_path . '/src/Repository/GameRepository.php';
    require_once $root_path . '/src/Model/GameMedia.php';
    require_once $root_path . '/src/Repository/GameMediaRepository.php';
    require_once $root_path . '/src/Model/GamePegiDescriptor.php';
    require_once $root_path . '/src/Repository/GamePegiDescriptorRepository.php';

    $gameRepo = new GameRepository();
    $authorizedExtensions = ['png','jpg','jpeg','webp'];
    $maxSizePerFile = 10 * 1024 * 1024; // 10MB in bytes
    $maxTotalSize = 50 * 1024 * 1024;   // 50MB in bytes
    $totalSize = 0;


    // Get the fields values (starting by the simplest ones)
    //1. Recuperation and cleaning of the text/number values
    $gameTitle = trim($_POST['gameTitle'] ?? '');
    $gameDesc = trim($_POST['gameDesc'] ?? '');
    // If not a float will return false
    $gamePrice = filter_var($_POST['gamePrice'] ?? 0, FILTER_VALIDATE_FLOAT);

    //2. Recuperation and validation of the enums values
    $gameTypeStr = $_POST['gameType'] ?? '';
    $pegiAgeStr = $_POST['gamePegiAge'] ?? '';
    // If not in the enum it will return null
    $gameType = GameType::tryFrom($gameTypeStr);
    $pegiAge = PegiAge::tryFrom($pegiAgeStr);

    //3. Recuperation of the optional array (pegi descriptors)
    $pegiDescriptorsIds = $_POST['gamePegiDescriptors'] ?? [];

    // Now we verify the validity of the values we collected previously
    // start checking if the basic fields are filled and valid
    if (empty($gameTitle) || empty($gameDesc) || $gamePrice === false) {
        $error_msg = "Veuillez remplir correctement les champs obligatoires (Titre, Description, Prix)";
        // check if the gameType enum value was valid
    } elseif ($gameType === null) {
        $error_msg = "Le type de jeu selectionn├® est invalide";
        // check if the pegiAge enum value was valid
    } elseif ($pegiAge === null) {
        $error_msg = "La restriction PEGI selectionn├®e est invalide";
        // check if the obligatory images are uploaded and if the upload is successfully
    } elseif (!isset($_FILES['gameTitlePic']) || $_FILES['gameTitlePic']['error'] !== UPLOAD_ERR_OK ||
        !isset($_FILES['gameHeroPic']) || $_FILES['gameHeroPic']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = "Les images de titre et de hero sont obligatoires et doivent ├¬tre valides";
        // check if the game is not already in DB
    } elseif ($gameRepo->findByName($gameTitle) !== null) {
        $error_msg = "Un jeu avec le m├¬me titre existe d├®j├á";
    }

    if (empty($error_msg)) {
        // Handle the upload of the gameTitlePic and gameHeroPic
        $uploadDir = $root_path . '/public/img/games/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true);}

        // Add the size of the gameTitlePic and gameHeroPic to $totalSize
        $totalSize += $_FILES['gameTitlePic']['size'] + $_FILES['gameHeroPic']['size'];

        // Generate random unique names to avoid conflicts with file that have the same name
        $titlePicExtension = strtolower(pathinfo($_FILES['gameTitlePic']['name'], PATHINFO_EXTENSION));
        $heroPicExtension = strtolower(pathinfo($_FILES['gameHeroPic']['name'], PATHINFO_EXTENSION));

        // Check the size of gameTitlePic and gameHeroPic
        if ($_FILES['gameTitlePic']['size'] > $maxSizePerFile || $_FILES['gameHeroPic']['size'] > $maxSizePerFile) {
            $error_msg = "L'image de titre ou hero d├®passe la limite de 10MB";
            // Check if the images has an authorized extension
        } elseif (!in_array($titlePicExtension, $authorizedExtensions) || !in_array($heroPicExtension, $authorizedExtensions)) {
            $error_msg = "Erreur, seul les fichiers avec les extensions (.png, .jpg, .webp) sont authoris├®s";
        }



        $titlePicName = uniqid('title_') . '.' . $titlePicExtension;
        $heroPicName = uniqid('hero_')  . '.' . $heroPicExtension;
        $titlePicDestination = $uploadDir . $titlePicName;
        $heroPicDestination = $uploadDir . $heroPicName;

        if (empty($error_msg)) {
            // Create an array that will contains the path of all uploaded files, this will allow us to unlink all file if something wrong happen
            $uploadedFiles = [];
            // Move the uploaded files to '/img/game' because php store it in his personal /tmp folder
            if (move_uploaded_file($_FILES['gameTitlePic']['tmp_name'], $titlePicDestination) &&
                move_uploaded_file($_FILES['gameHeroPic']['tmp_name'], $heroPicDestination)) {

                // Add the principals pictures to $uploadedFiles
                $uploadedFiles[] = $titlePicDestination;
                $uploadedFiles[] = $heroPicDestination;

                // Get the connection instance to the DB
                $pdo = DatabaseConnection::getInstance();


                // The path to save in the DB (based on /public)
                $titlePicDbPath = '/img/games/' . $titlePicName;
                $heroPicDbPath = '/img/games/' . $heroPicName;

                // Creation of the new game in the DB
                $newGame = new Game(
                    $gameTitle,
                    $gamePrice,
                    $gameType,
                    $gameDesc,
                    $heroPicDbPath,
                    $titlePicDbPath,
                    $pegiAge
                );

                // Try a "transaction" to be sure that if the 'pegiDecriptor' and 'gameMedia' steps crash we abort all
                try {
                    $pdo->beginTransaction();
                    // Insert the game
                    $gameRepo->insert($newGame);
                    // Start linking the pegi descriptors to the new game
                    if (!empty($pegiDescriptorsIds)) {
                        $gamePegiDescriptorRepo = new GamePegiDescriptorRepository();
                        foreach ($pegiDescriptorsIds as $descriptorId) {
                            // We don't check if the descriptor ID exist because the DB will send an error
                            // thanks to foreign keys that will block the insert if the Id doesn't exist
                            $descriptorLink = new GamePegiDescriptor($newGame->getId(), (int)$descriptorId);
                            $gamePegiDescriptorRepo->insert($descriptorLink);
                        }
                    }

                    // Now we handle the other pictures (game_media)
                    // we check that the name of the first list's element isn't empty, because when a user click on 'browse' and
                    // don't select anything it upload ['gamePictures']['name'][0] will contain empty string " "
                    if (isset($_FILES['gamePictures']) && !empty($_FILES['gamePictures']['name'][0])) {
                        // We make sure that the user doesn't sent more that 6 pictures
                        $fileCount = count($_FILES['gamePictures']['name']);
                        if ($fileCount > 6 ) {throw new Exception("Erreur lors de l'upload des images additionnelles. Veuillez r├®essayer.");}

                        // Now we check the size of each file
                        foreach($_FILES['gamePictures']['size'] as $index => $size) {
                            // Check if the upload as failed (file to big for the PHP config)
                            if ($_FILES['gamePictures']['error'][$index] === UPLOAD_ERR_INI_SIZE) {
                                throw new Exception("Le fichier " . ($index + 1) . " d├®passe la limite autoris├®e par PHP (10MB)");
                            }
                            // Check individual file size (in case the $maxSizePerFile change in the future and the PHP limit is higher than that)
                            if ($size > $maxSizePerFile) {
                                throw new Exception("Le fichier " . ($index + 1) . " d├®passe 10MB");
                            }
                            $totalSize += $size;
                        }
                        // Check total size (titlePic + heroPic + gamePictures)
                        if ($totalSize > $maxTotalSize) {
                            throw new Exception("La taille totale des fichiers d├®passe 50MB");
                        }


                        // Set the loop limit dynamically
                        $loopLimit = min($fileCount, 6);

                        $gameMediaRepo = new GameMediaRepository();

                        // Loop through the picture
                        for($i = 0 ; $i < $loopLimit ; $i++) {
                            if ($_FILES['gamePictures']['error'][$i] === UPLOAD_ERR_OK) {
                                // If no problem with the picture we define its name, extension and destination
                                $picExt = strtolower(pathinfo($_FILES['gamePictures']['name'][$i], PATHINFO_EXTENSION));
                                $picName = uniqid('media_') . '.' . $picExt;
                                $picDest = $uploadDir . $picName;

                                if (in_array($picExt, $authorizedExtensions) && move_uploaded_file($_FILES['gamePictures']['tmp_name'][$i], $picDest)) {
                                    // Add the file to $uploadedFiles in case of the insert crash
                                    $uploadedFiles[] = $picDest;
                                    // If the file success to move we insert it to the db
                                    $mediaDbPath = '/img/games/' . $picName;
                                    $newMedia = new GameMedia($mediaDbPath, $newGame->getId());
                                    $gameMediaRepo->insert($newMedia);
                                }
                            }
                        }

                    }
                    // And if every insert done is successfull (no error) we validate the transaction and redirect
                    $pdo->commit();
                    header('Location: /admin/gameDashboard.php');
                    exit();
                }
                catch (Exception $exception) {
                    // In case of an SQL error or an error we throw (ex: to much media files)
                    // we abort the transaction
                    $pdo->rollBack();
                    $error_msg = "Erreur lors de l'enregistrement dans la DB : " . $exception->getMessage();
                    // Remove orphans files that has been put in "/img/games" $uploadedFiles
                    foreach ($uploadedFiles as $filePath) {
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
            } else {
                $error_msg = "Erreur lors du t├®l├®chargement des images";
            }
        }
    }
}

// Check if it's a POST request that contain a 'action' field that contains 'deleteGame'
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action']) && $_POST['action'] === 'deleteGame') {

    // DEPENDENCIES TO REMOVE A GAME
    require_once $root_path . '/src/Model/Game.php';
    require_once $root_path . '/src/Repository/GameRepository.php';
    require_once $root_path . '/src/Model/GameMedia.php';
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
                header("Location: /admin/gameDashboard.php");
                exit();
            }
            catch (Exception $exception) {
                $error_msg = "Erreur lors de la suppression de '" . $game->getGameName() . "' :\"" .$exception->getMessage() . "\"";
            }
        } else {
            $error_msg = "Erreur, le jeu que vous souhait├® supprimer n'existe pas dans la base de donn├®es";
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
    $searchPlaceholder = 'Recherche :';
    $showLoginButton = false;
    $showProfilePic = true;
    include '../includes/header.php';
    ?>

    <div class="absolute top-28 w-full max-w-7xl px-4 pb-4">

        <div class="bg-[#F0EEE9] bg-opacity-95 rounded-[3rem] shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] overflow-hidden">

            <div class="px-5 pt-3 pb-2">
                <h1 class="text-2xl font-bold text-[#3769a9]">Ajout d'un jeu</h1>
            </div>
            <div class="h-1.5 bg-[#33b842]"></div>

            <form method="post" action="" enctype="multipart/form-data" class="p-5 space-y-3" id="addGameForm">
                <input type="hidden" name="action" value="addGame">

                <?php if (!empty($error_msg)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <p class="font-medium"><?php echo htmlspecialchars($error_msg); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_msg)): ?>
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
                                   value="<?php echo htmlspecialchars($_POST['gameTitle'] ?? ''); ?>"
                                   placeholder="Nom du jeu"
                                   class="w-full bg-transparent text-[#3769a9] text-sm border-b-2 border-[#3769a9] outline-none placeholder:text-[#3769a9]/40 pb-0.5">
                        </div>

                        <div>
                            <label for="gameDesc" class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-0.5">
                                Description<span class="text-red-500">*</span>
                            </label>
                            <textarea id="gameDesc"
                                      name="gameDesc"
                                      rows="3"
                                      required
                                      placeholder="Description du jeu"
                                      class="w-full resize-y bg-transparent text-[#3769a9] text-sm border-b-2 border-[#3769a9] outline-none placeholder:text-[#3769a9]/40 pb-0.5"><?php echo htmlspecialchars($_POST['gameDesc'] ?? ''); ?></textarea>
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
                                   value="<?php echo htmlspecialchars($_POST['gamePrice'] ?? ''); ?>"
                                   placeholder="0,00"
                                   class="w-full bg-transparent text-[#3769a9] text-sm border-b-2 border-[#3769a9] outline-none placeholder:text-[#3769a9]/40 pb-0.5">
                        </div>

                        <div>
                            <p class="block text-xl md:text-2xl lg:text-xl font-medium text-[#3769a9] mb-1">
                                Type<span class="text-red-500">*</span>
                            </p>
                            <div class="flex flex-wrap gap-1.5" id="gameTypeContainer">
                                <?php foreach ($gameTypes as $gameType): ?>
                                    <?php $typeValue = $gameType->value; ?>
                                    <?php $inputId = 'type-' . $typeValue; ?>
                                    <input type="radio"
                                           id="<?php echo htmlspecialchars($inputId); ?>"
                                           name="gameType"
                                           value="<?php echo htmlspecialchars($typeValue); ?>"
                                           class="hidden peer/<?php echo htmlspecialchars($inputId); ?>"
                                           <?php echo (($_POST['gameType'] ?? '') === $typeValue) ? 'checked' : ''; ?>>
                                    <label for="<?php echo htmlspecialchars($inputId); ?>"
                                           class="px-4 py-1 bg-[#F0EEE9] rounded-4xl text-[#33b842] text-sm font-bold border-2 border-[#3769a9] cursor-pointer transition duration-200 peer-checked/<?php echo htmlspecialchars($inputId); ?>:bg-[#33b842] peer-checked/<?php echo htmlspecialchars($inputId); ?>:text-white peer-checked/<?php echo htmlspecialchars($inputId); ?>:border-[#33b842]">
                                        <?php echo htmlspecialchars(ucfirst(strtolower($gameType->name))); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                            <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-32 flex flex-col items-center justify-center text-center cursor-pointer transition duration-200 hover:bg-white/40 overflow-hidden relative" id="dropHero">
                                <input type="file" name="gameHeroPic" id="gameHeroPic" accept=".png,.jpg,.jpeg,.webp" required class="hidden">
                                <div id="heroPlaceholder" class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-[#3769a9] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                        <circle cx="12" cy="12" r="3.5" stroke-width="1.8"></circle>
                                    </svg>
                                    <span class="text-sm md:text-base lg:text-sm font-medium text-[#3769a9]">Image Hero<span class="text-red-500">*</span></span>
                                </div>
                                <div id="heroPreviewContainer" class="hidden absolute inset-0 bg-[#F0EEE9] flex flex-col items-center justify-center p-1">
                                    <img id="heroPreview" class="h-16 w-auto object-contain rounded" src="" alt="preview">
                                    <span id="heroName" class="text-[10px] text-[#3769a9] truncate w-full px-2 font-semibold"></span>
                                </div>
                            </label>

                            <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-32 flex flex-col items-center justify-center text-center cursor-pointer transition duration-200 hover:bg-white/40 overflow-hidden relative" id="dropTitle">
                                <input type="file" name="gameTitlePic" id="gameTitlePic" accept=".png,.jpg,.jpeg,.webp" required class="hidden">
                                <div id="titlePlaceholder" class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-[#3769a9] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                        <circle cx="12" cy="12" r="3.5" stroke-width="1.8"></circle>
                                    </svg>
                                    <span class="text-sm md:text-base lg:text-sm font-medium text-[#3769a9]">Image Title<span class="text-red-500">*</span></span>
                                </div>
                                <div id="titlePreviewContainer" class="hidden absolute inset-0 bg-[#F0EEE9] flex flex-col items-center justify-center p-1">
                                    <img id="titlePreview" class="h-16 w-auto object-contain rounded" src="" alt="preview">
                                    <span id="titleName" class="text-[10px] text-[#3769a9] truncate w-full px-2 font-semibold"></span>
                                </div>
                            </label>

                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="group rounded-[2rem] border-4 border-dashed border-[#3769a9] p-3 h-28 flex flex-col items-center justify-center cursor-pointer transition duration-200 hover:bg-white/40">
                                <input type="file" name="gamePictures[]" id="gamePictures" accept=".png,.jpg,.jpeg,.webp" multiple class="hidden">
                                <div class="flex items-center gap-2" id="galleryPlaceholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#3769a9] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                        <circle cx="12" cy="12" r="3.5" stroke-width="1.8"></circle>
                                    </svg>
                                    <span class="text-[#3769a9] text-sm md:text-base lg:text-sm leading-tight font-medium">
                                        Ajoutez des photos (Max 6)
                                    </span>
                                </div>
                                <div id="galleryPreviewContainer" class="hidden w-full h-full flex items-center justify-center gap-2 overflow-x-auto hide-scrollbar">
                                </div>
                            </label>
                            <p id="galleryError" class="text-xs text-red-500 font-bold hidden text-center"></p>
                        </div>

                    </div>

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mt-4">

                    <div>
                        <p class="text-xl font-medium text-[#3769a9] mb-1.5">Choisir l'age requis<span class="text-red-500">*</span></p>
                        <div class="flex flex-wrap gap-1" id="pegiAgeContainer">
                            <?php foreach ($pegiAges as $pegiAge): ?>
                                <?php $inputId = 'pegiAge' . $pegiAge->value; ?>
                                <input type="radio"
                                       id="<?php echo htmlspecialchars($inputId); ?>"
                                       name="gamePegiAge"
                                       value="<?php echo htmlspecialchars($pegiAge->value); ?>"
                                       class="hidden peer/<?php echo htmlspecialchars($inputId); ?>"
                                       <?php echo (($_POST['gamePegiAge'] ?? '') === (string)$pegiAge->value) ? 'checked' : ''; ?>>
                                <label for="<?php echo htmlspecialchars($inputId); ?>"
                                       class="bg-white p-1 rounded-xl border-2 border-transparent shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] cursor-pointer transition duration-200 peer-checked/<?php echo htmlspecialchars($inputId); ?>:border-[#33b842] peer-checked/<?php echo htmlspecialchars($inputId); ?>:scale-105">
                                    <img src="../img/pegi/age/age-<?php echo htmlspecialchars($pegiAge->value); ?>.jpg"
                                         alt="PEGI <?php echo htmlspecialchars($pegiAge->value); ?>"
                                         class="w-10 h-10 rounded object-contain"
                                         onerror="this.onerror=null; this.style.display='none';">
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <p class="text-xl font-medium text-[#3769a9] mb-1.5">Choisir le descripteur de contenu</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1">
                            <?php foreach ($pegiDescriptors as $pegiDescriptor): ?>
                                <?php $inputId = 'descriptor' . $pegiDescriptor->getId(); ?>
                                <?php $isChecked = in_array((string)$pegiDescriptor->getId(), $_POST['gamePegiDescriptors'] ?? [], true); ?>
                                <input type="checkbox"
                                       id="<?php echo htmlspecialchars($inputId); ?>"
                                       name="gamePegiDescriptors[]"
                                       value="<?php echo htmlspecialchars((string)$pegiDescriptor->getId()); ?>"
                                       class="hidden peer/<?php echo htmlspecialchars($inputId); ?>"
                                       <?php echo $isChecked ? 'checked' : ''; ?>>
                                <label for="<?php echo htmlspecialchars($inputId); ?>"
                                       class="bg-white p-1 rounded-xl border-2 border-transparent shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] flex flex-col items-center gap-0.5 cursor-pointer transition duration-200 peer-checked/<?php echo htmlspecialchars($inputId); ?>:border-[#33b842] peer-checked/<?php echo htmlspecialchars($inputId); ?>:scale-105">
                                    <?php $imgName = strtolower(str_replace(' ', '-', $pegiDescriptor->getLabel())) . '.jpg'; ?>
                                    <img src="../img/pegi/desc/<?php echo htmlspecialchars($imgName); ?>"
                                         alt="<?php echo htmlspecialchars($pegiDescriptor->getLabel()); ?>"
                                         class="w-7 h-7 rounded object-contain"
                                         onerror="this.onerror=null; this.style.display='none';">
                                    <span class="text-[9px] text-center font-semibold text-[#3769a9] leading-tight"><?php echo htmlspecialchars($pegiDescriptor->getLabel()); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <div class="flex justify-end pt-0.5" id="submitBtnContainer">
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
