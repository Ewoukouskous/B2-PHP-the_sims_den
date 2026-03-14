<?php

if (session_status() === PHP_SESSION_NONE) {session_start();}
// SELECT the actual DIR (public/actions), and ask the go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

// AUTHMIDDLEWARE DEPENDENCIE
require_once $root_path . '/src/Security/AuthMiddleware.php';

// Check if the user is connected, if not redirect to /public/auth/login.php
if (!AuthMiddleware::is_connected()){
    header('Location: /auth/login.php');
    exit();
}

// Check that it's a POST request that contain an 'action' and a 'gameId'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['gameId'])) {

    // Get the 'action' and 'gameId' values and normalize them
    $action = trim(strtolower($_POST['action']));
    $gameId = filter_var($_POST['gameId'], FILTER_VALIDATE_INT) === false ? 0 : (int)$_POST['gameId'];

    // GET THE DEPENDENCIES
    require_once $root_path . '/src/Model/Game.php';
    require_once $root_path . '/src/Repository/GameRepository.php';
    require_once $root_path . '/src/Model/UserFavorite.php';
    require_once $root_path . '/src/Repository/UserFavoriteRepository.php';

    $gameRepo = new GameRepository();
    $favoriteRepo = new UserFavoriteRepository();

    // Check that the selected game exist
    $game = $gameRepo->findById($gameId);

    if ($game !== null) {
        $userId = (int)$_SESSION['userId'];

        try {
            // Search if there is a 'user_favorite' associated to the gameId
            $existingFavorite = $favoriteRepo->findByIdPair($userId, $gameId);

            // Case 1 : The user want to add a favorite and he DOES NOT already have it has favorite
            if ($action === 'add' && $existingFavorite === null) {
                // Get the 'playtimeHours' field value
                $playtimeHours = filter_var($_POST['playtimeHours'] ?? 0, FILTER_VALIDATE_INT);
                // If it's not an INT or is negative we set playtimeHours to 0
                if ($playtimeHours === false || $playtimeHours < 0 ) {$playtimeHours = 0;}

                // Create the UserFavorite and insert it in DB
                $userFavorite = new UserFavorite($userId, $gameId, $playtimeHours);
                $favoriteRepo->insert($userFavorite);
                // Increment the 'favoritesNumber' attribute of the game and update it in DB
                $game->setFavoriteNumber($game->getFavoritesNumber() + 1);
                $gameRepo->update($game);

                // Case 2 : The user want to delete a favorite and he DOES have the game has favorite
            } elseif ($action === 'delete' && $existingFavorite !== null) {
                // Remove the UserFavorite from the DB
                $favoriteRepo->delete($userId, $gameId);
                // Decrement the 'favoritesNumber' attribute of the game and update it in DB
                // we ensure to not get with a negative number using max()
                $game->setFavoriteNumber(max(0, $game->getFavoritesNumber() - 1));
                $gameRepo->update($game);
            }
        } catch (Exception $exception) {
            error_log("Erreur d'ajout de favoris : " . $exception->getMessage());

            // In case of an exception (principally PDO) we redirect
            $refererPage = $_SERVER['HTTP_REFERER'] ?? '/index.php';
            header('Location: ' . $refererPage);
            exit();
        }

    }
}

// Redirect the user to where he's from
$refererPage = $_SERVER['HTTP_REFERER'] ?? '/index.php';
header('Location: ' . $refererPage);
exit();