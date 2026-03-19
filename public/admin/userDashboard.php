<?php

if (session_status() === PHP_SESSION_NONE) {session_start();}

// SELECT the actual DIR (public/auth), and ask the go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

// DEPENDENCIES OF THE PAGE
require_once $root_path . '/src/Security/AuthMiddleware.php';
require_once $root_path . '/src/Enum/UserRole.php';
require_once $root_path . '/src/Model/UserAccount.php';
require_once $root_path . '/src/Repository/UserAccountRepository.php';

// If the user isn't an admin, we redirect him to the index.php
if (!AuthMiddleware::is_admin($_SESSION)) {
    header("Location: /index.php");
    exit();
}

// Initiate usefully variables
$userRepository = new UserAccountRepository();
$error_msg = "";

// Check if it's a POST request that contain a 'action' field that contains 'changeRole' and 'userId'
if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action']) && $_POST['action'] === 'changeRole' && isset($_POST['userId'])) {
    // Check that the request isn't for the user that initiate it (so you can't demote yourself)
    if ((int)$_POST['userId'] !== (int)$_SESSION['userId']) {
        // Check that the userId correspond to an actual user in the database
        $user = $userRepository->findById((int)$_POST['userId']);
        if(!is_null($user)) {
            try {
                // We change the user role (admin/user)
                $newRole = $user->getUserRole() === UserRole::USER ? UserRole::ADMIN : UserRole::USER;
                $user->setUserRole($newRole);
                // Then update the user in DB
                $userRepository->update($user);
                // Now we redirect
                header("Location: /admin/userDashboard.php");
                exit();
            } catch (Exception $exception) {
                $error_msg = "Erreur lors de la mise à jour du rôle : " . $exception->getMessage();
            }

        } else {
            $error_msg = "Erreur, l'utilisateur que vous essayez de promouvoir / révoquer n'existe pas";
        }
    } else {
        $error_msg = "Erreur, il est impossible de promouvoir / révoquer votre propre compte";
    }
}

// Check if it's a POST request that contain a 'action' field that contains 'deleteUser' and 'userId'
if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action']) && $_POST['action'] === 'deleteUser' && isset($_POST['userId'])) {
    // Check that the request isn't for the user that initiate it (so you can't delete yourself)
    if ((int)$_POST['userId'] !== (int)$_SESSION['userId']) {
        // Check that the userId correspond to an actual user in the database
        $user = $userRepository->findById((int)$_POST['userId']);
        if(!is_null($user)) {
            try {
                // We delete the user
                $userRepository->delete($user->getId());
                // Try to unlock the related Achievement
                require_once $root_path . '/src/Service/AchievementService.php';
                $achievementService = new AchievementService();
                $achievementService->unlockSpecificAchievement((int)$_SESSION['userId'], "Faucheuse");
                // Now we redirect
                header("Location: /admin/userDashboard.php");
                exit();
            } catch (Exception $exception) {
                $error_msg = "Erreur lors de la suppression de l'utilisateur : " . $exception->getMessage();
            }

        } else {
            $error_msg = "Erreur, l'utilisateur que vous essayé de supprimer n'existe pas";
        }
    } else {
        $error_msg = "Erreur, il est impossible de supprimer votre propre compte";
    }
}

// Get all users for the dashboard
$users = $userRepository->findAll();
