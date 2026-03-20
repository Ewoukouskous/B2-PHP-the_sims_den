<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_path = dirname(__DIR__, 2);
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Database/DatabaseConnection.php';
require_once $root_path . '/src/Model/UserFavorite.php';
require_once $root_path . '/src/Repository/GameRepository.php';
require_once $root_path . '/src/Repository/UserFavoriteRepository.php';

function favoriteRedirectTarget(): string {
    $fallback = '../index.php';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    return is_string($referer) && $referer !== '' ? $referer : $fallback;
}

if (!AuthMiddleware::is_connected($_SESSION)) {
    header('Location: ../auth/login.php');
    exit();
}

$userId = isset($_SESSION['userId']) && is_numeric($_SESSION['userId']) ? (int)$_SESSION['userId'] : null;
if ($userId === null) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . favoriteRedirectTarget());
    exit();
}

$action = trim((string)($_POST['action'] ?? ''));
$gameIdRaw = trim((string)($_POST['gameId'] ?? ''));

if (($action !== 'add' && $action !== 'delete') || $gameIdRaw === '' || !ctype_digit($gameIdRaw)) {
    header('Location: ' . favoriteRedirectTarget());
    exit();
}

$gameId = (int)$gameIdRaw;
$gameRepository = new GameRepository();
$userFavoriteRepository = new UserFavoriteRepository();
$game = $gameRepository->findById($gameId);

if ($game === null) {
    header('Location: ' . favoriteRedirectTarget());
    exit();
}

$existingFavorite = $userFavoriteRepository->findByIdPair($userId, $gameId);
$pdo = DatabaseConnection::getInstance();

try {
    $pdo->beginTransaction();

    if ($action === 'add' && $existingFavorite === null) {
        $playtimeHoursRaw = trim((string)($_POST['playtimeHours'] ?? ''));
        if ($playtimeHoursRaw === '' || !ctype_digit($playtimeHoursRaw)) {
            throw new InvalidArgumentException('playtimeHours is required for add action.');
        }

        $playtimeHours = (int)$playtimeHoursRaw;
        $userFavoriteRepository->insert(new UserFavorite($userId, $gameId, $playtimeHours));
        $game->setFavoriteNumber($game->getFavoritesNumber() + 1);
        $gameRepository->update($game);
    }

    if ($action === 'delete' && $existingFavorite !== null) {
        $userFavoriteRepository->delete($userId, $gameId);
        $game->setFavoriteNumber(max(0, $game->getFavoritesNumber() - 1));
        $gameRepository->update($game);
    }

    $pdo->commit();
} catch (Throwable) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

header('Location: ' . favoriteRedirectTarget());
exit();

