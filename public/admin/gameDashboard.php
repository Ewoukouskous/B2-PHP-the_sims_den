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
//if (!AuthMiddleware::is_admin($_SESSION)) {
//    header('Location: /index.php');
//    exit();
//}

// Initiate usefully variables
// All GameTypes
$gameTypes = GameType::cases();
// All PegiAge
$pegiAges = PegiAge::cases();
// All PegiDescriptor
$pegiDescriptors = new PegiDescriptorRepository()->findAll();
// Error msg
$error_msg = "";


// Check if it's a POST request that contain a 'action' field that contains 'addGame'
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action']) && $_POST['action'] === 'addGame' ) {

    // DEPENDENCIES TO CREATE GAME
    require_once $root_path . '/src/Model/Game.php';
    require_once $root_path . '/src/Repository/GameRepository.php.php';
    require_once $root_path . '/src/Model/GameMedia.php';
    require_once $root_path . '/src/Repository/GameMediaRepository.php';
    require_once $root_path . '/src/Model/GamePegiDescriptor.php';
    require_once $root_path . '/src/Repository/GamePegiDescriptorRepository.php';

    $gameRepo = new GameRepository();
    $authorizedExtensions = ['png','jpg','jpeg','webp'];

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
        $error_msg = "Le type de jeu selectionné est invalide";
    // check if the pegiAge enum value was valid
    } elseif ($pegiAge === null) {
        $error_msg = "La restriction PEGI selectionnée est invalide";
    // check if the obligatory images are uploaded and if the upload is successfully
    } elseif (!isset($_FILES['gameTitlePic']) || $_FILES['gameTitlePic']['error'] !== UPLOAD_ERR_OK ||
            !isset($_FILES['gameHeroPic']) || $_FILES['gameHeroPic']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = "Les images de titre et de hero sont obligatoires et doivent être valides";
    // check if the game is not already in DB
    } elseif ($gameRepo->findByName($gameTitle) !== null) {
        $error_msg = "Un jeu avec le même titre existe déjà";
    }

    if (empty($error_msg)) {
        // Handle the upload of the gameTitlePic and gameHeroPic
        $uploadDir = $root_path . '/public/img/games/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true);}

        // Generate random unique names to avoid conflicts with file that have the same name
        $titlePicExtension = strtolower(pathinfo($_FILES['gameTitlePic']['name'], PATHINFO_EXTENSION));
        $heroPicExtension = strtolower(pathinfo($_FILES['gameHeroPic']['name'], PATHINFO_EXTENSION));

        // Check if the images has an authorized extension
        if (!in_array($titlePicExtension, $authorizedExtensions) || !in_array($heroPicExtension, $authorizedExtensions)) {
            $error_msg = "Erreur, seul les fichiers avec les extensions (.png, .jpg, .webp) sont authorisés";
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
                        if ($fileCount > 6 ) {throw new Exception("Erreur lors de l'upload des images additionnelles. Veuillez réessayer.");}

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
                    header('Location: /public/admin/gameDashboard.php');
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
                $error_msg = "Erreur lors du téléchargement des images";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Ajouter un jeu</title>
</head>
<body>
    <h1>Ajouter un nouveau jeu</h1>

    <?php if (!empty($error_msg)): ?>
        <div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 20px;">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="addGame">

        <div>
            <label for="gameTitle">Titre du jeu *</label>
            <input type="text" id="gameTitle" name="gameTitle" required>
        </div>

        <div>
            <label for="gameDesc">Description *</label>
            <textarea id="gameDesc" name="gameDesc" rows="5" required></textarea>
        </div>

        <div>
            <label for="gamePrice">Prix *</label>
            <input type="number" id="gamePrice" name="gamePrice" step="0.01" min="0" required>
        </div>

        <div>
            <label for="gameType">Type de jeu *</label>
            <select id="gameType" name="gameType" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($gameTypes as $type): ?>
                    <option value="<?= $type->value ?>"><?= $type->value ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="gamePegiAge">Âge PEGI *</label>
            <select id="gamePegiAge" name="gamePegiAge" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($pegiAges as $age): ?>
                    <option value="<?= $age->value ?>"><?= $age->value ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Descripteurs PEGI (optionnel)</label>
            <?php foreach ($pegiDescriptors as $descriptor): ?>
                <div>
                    <input type="checkbox" id="desc_<?= $descriptor->getId() ?>" name="gamePegiDescriptors[]" value="<?= $descriptor->getId() ?>">
                    <label for="desc_<?= $descriptor->getId() ?>"><?= htmlspecialchars($descriptor->getLabel()) ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <div>
            <label for="gameTitlePic">Image de titre *</label>
            <input type="file" id="gameTitlePic" name="gameTitlePic" accept=".png,.jpg,.jpeg,.webp" required>
        </div>

        <div>
            <label for="gameHeroPic">Image hero *</label>
            <input type="file" id="gameHeroPic" name="gameHeroPic" accept=".png,.jpg,.jpeg,.webp" required>
        </div>

        <div>
            <label for="gamePictures">Images additionnelles (max 6)</label>
            <input type="file" id="gamePictures" name="gamePictures[]" accept=".png,.jpg,.jpeg,.webp" multiple>
            <small>Vous pouvez sélectionner jusqu'à 6 images</small>
        </div>

        <div>
            <button type="submit">Ajouter le jeu</button>
        </div>
    </form>
</body>
</html>
