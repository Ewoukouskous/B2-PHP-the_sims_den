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



if ($_SERVER['REQUEST_METHOD'] === "POST" && $_POST['addGame']) {

    // DEPENDENCIES TO CREATE GAME
//    require_once $root_path . '/src/Model/Game.php';
//    require_once $root_path . '/src/Model/GameMedia.php';
//    require_once $root_path . '/src/Repository/GameMediaRepository.php';

    // Get the fields values
//    $gameTitle = trim($_POST['gameTitle']);
//    $gameDesc = trim($_POST['gameDesc']);
//    $gamePrice = filter_var($_POST['gamePrice'], FILTER_VALIDATE_FLOAT); // Return false if not a float
//    $gameType = GameType::tryFrom($_POST['gameType']); // Return null if not in GameType enum
//    $gamePegiAge = PegiAge::tryFrom($_POST['gamePegiAge']); // Return null if not a PegiAge enum
//    $gamePegiDescriptors = $_POST['gamePegiDescriptors']; // Need to check if its a list/array of valid PegiDescriptors
//    $heroPicture = $_POST['heroPicture']; // Need to check if its a picture
//    $titlePicture = $_POST['titlePicture']; // Need to check if its a picture
//    $descPictures = $_POST['descPictures']; // Need to check if its a list/array of pictures
}