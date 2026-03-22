<?php
if (session_status() === PHP_SESSION_NONE) {session_start();}
// SELECT the actual DIR (public/actions), and ask the go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

// Define the response type that the 'page' will give (here a JSON)
header('Content-Type: application/json');

// AUTHMIDDLEWARE DEPENDENCIE
require_once $root_path . '/src/Security/AuthMiddleware.php';

// Check if the user is connected, if not respond an error with json
if (!AuthMiddleware::is_connected()){
    echo json_encode(['succeed' => false, 'error' => 'Utilisateur non connecté']);
    exit();
}

// Check that it's a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Try to unlock the success for the user
        require_once $root_path . '/src/Service/AchievementService.php';
        $achievementService = new AchievementService();
        $achievementService->unlockSpecificAchievement((int)$_SESSION['userId'], "Motherlode");
        // Return the json response
        echo json_encode(['succeed' => true, 'error' => null]);
        exit();

    } catch (Exception $exception) {
        // We use error_log() because the json 'response' is invisible for the user since the request is done by javascript
        error_log("Erreur lors du déverrouillage du succès 'Motherlode' : " . $exception->getMessage());
        echo json_encode(['succeed' => false, 'error' => 'Erreur côté serveur']);
        exit();
    }
} else {
    echo json_encode(['succeed' => false, 'error' => 'Méthode non autorisée']);
    exit();
}