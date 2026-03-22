<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = dirname(__DIR__, 2);
require_once $root_path . '/src/Security/AuthMiddleware.php';

// Verify that the user is logically connected
if (!AuthMiddleware::is_connected()) {
    http_response_code(403);
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

// We check if the cheatcode is in POST
$data = json_decode(file_get_contents('php://input'), true);
$cheat = $data['cheat'] ?? '';

if (strtolower($cheat) === 'motherlode') {
    require_once $root_path . '/src/Service/AchievementService.php';
    $achievementService = new AchievementService();
    $userId = (int)$_SESSION['userId'];
    
    // Unlock the achievement 
    $achievementService->unlockSpecificAchievement($userId, "Motherlode");
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Wrong code !']);
}
