<?php

if (session_status() === PHP_SESSION_NONE) {session_start();}

// SELECT the actual DIR (public/auth), and ask the go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

require_once $root_path . '/src/Database/DatabaseConnection.php';
require_once $root_path . '/src/Security/AuthMiddleware.php';

// Check if the user is already login, we redirect him to the index.php
if (AuthMiddleware::is_connected($_SESSION)) {
    header('Location: /index.php');
    exit();
}

$error_msg = '';

// If the request is a POST we start the login process
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // DEPENDENCIES
    require_once $root_path . '/src/Model/UserAccount.php';
    require_once $root_path . '/src/Repository/UserAccountRepository.php';
    require_once $root_path . '/src/Enum/UserRole.php';
    require_once $root_path . '/src/Model/ProfilePic.php';
    require_once $root_path . '/src/Repository/ProfilePicRepository.php';

    $userRepository = new UserAccountRepository();

    // We get the fields values
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if all the field are filled before checking if the user is valid and connect him
    if ($username === '' || $password === '') {
        $error_msg = 'ÉCHEC : Assurez vous que tous les champs soient remplis';
    } else {
        // Get the UserAccount object from the database
        $userByUsername = $userRepository->findByUsername($username);
        $userAccount = $userByUsername !== null ? $userByUsername : $userRepository->findByEmail($username);

        // If the user account is found and the password hash correspond to the given password we create his PHPSESSION
        if ($userAccount && password_verify($password, $userAccount->getPasswordHash())) {
            // Initialize the new PHPSESSID
            session_regenerate_id(true);
            $_SESSION['userId'] = $userAccount->getId();
            $_SESSION['userRole'] = $userAccount->getUserRole()->value;
            $_SESSION['username'] = $userAccount->getUsername();

            // Get the ProfilePic to get the path
            $profilePicRepository = new ProfilePicRepository();
            $profilePic = $profilePicRepository->findById($userAccount->getIdProfilePic());

            if ($profilePic) {
                $_SESSION['profilePicPath'] = $profilePic->getPicturePath();
            } else {
                $_SESSION['profilePicPath'] = $profilePicRepository->findById(1)->getPicturePath();
            }

            // The PHPSESSION creation is done, now we redirect to the homepage
            header("Location: /index.php");
            exit();
        } else {
            $error_msg = "Nom d'utilisateur / Email ou le mot de passe est incorrect";
        }
    }
}
?>