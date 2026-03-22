<?php

if (session_status() === PHP_SESSION_NONE) {session_start();}

// SELECT the actual DIR (public/views), and ask the go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

require_once $root_path . '/src/Security/AuthMiddleware.php';

// Check if the user is connected (if not we redirect him to the login page
if (!AuthMiddleware::is_connected()) {
    header('Location: /auth/login.php');
    exit();
}

// Initialize the error handling variables
$errors = [];
$success_msg = '';

// If the request is a POST we start the script
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User account dependencies
    require_once $root_path . '/src/Model/UserAccount.php';
    require_once $root_path . '/src/Repository/UserAccountRepository.php';

    $userRepo = new UserAccountRepository();
    $hasModified = false;
    try {
        // Get the user to modify him after (if he exist)
        $user = $userRepo->findById((int)$_SESSION['userId']);

        if ($user) {
            // Now we check what we are asked to modify
            // 1. Modify Username
            if (isset($_POST['newUsername']) && $_POST['newUsername'] != '') {
                // Get the value and normalize it
                $newUsername = trim($_POST['newUsername']);
                // Check that the username isn't the actual username and not already taken
                if ($user->getUsername() === $newUsername) {
                    $errors[] = "Le nouveau nom d'utilisateur est le même que l'ancien";
                } elseif (strlen($newUsername) < 5 || strlen($newUsername) > 30) {
                    $errors[] = "Le nom d'utilisateur doit faire entre 5 et 30 caractères";
                } elseif ($userRepo->findByUsername($newUsername) !== null ) {
                    $errors[] = "Le nom d'utilisateur est déjà utilisé";
                } else {
                    $user->setUsername($newUsername);
                    $hasModified = true;
                }
            }
            // 2. Modify Email
            if (isset($_POST['newEmail']) && $_POST['newEmail'] != '') {
                // Get the value and normalize it
                $newEmail = trim($_POST['newEmail']);
                // Check that the email isn't the actual email and not already taken
                if ($user->getEmail() === $newEmail) {
                    $errors[] = "Le nouveau courriel est le même que l'ancien";
                } elseif (filter_var($newEmail, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[] = "Le courriel fourni n'est pas valide";
                } elseif ($userRepo->findByEmail($newEmail) !== null) {
                    $errors[] = "Le courriel est déjà associé à un autre compte utilisateur";
                } else {
                    $user->setEmail($newEmail);
                    $hasModified = true;
                }
            }
            // 3. Modify Password
            if (isset($_POST['newPassword']) && $_POST['newPassword'] != '' &&
                isset($_POST['confirmNewPassword']) && $_POST['confirmNewPassword'] != '') {
                // Get the values
                $newPassword = $_POST['newPassword'];
                $passwordConfirmation = $_POST['confirmNewPassword'];
                // Check that the password and confirmation are the same
                if ($newPassword !== $passwordConfirmation) {
                    $errors[] = "Les deux mots de passe ne correspondent pas";
                } elseif (strlen($newPassword) < 8) {
                    $errors[] = "Le mot de passe doit faire un minimum de 8 caractères";
                } else {
                    $user->setPasswordHash(password_hash($newPassword, PASSWORD_BCRYPT));
                    $hasModified = true;
                }
            }
            // 4. Modify Profile Pic
            if (isset($_POST['newProfilePicId']) && $_POST['newProfilePicId'] != '') {
                require_once $root_path . '/src/Model/ProfilePic.php';
                require_once $root_path . '/src/Repository/ProfilePicRepository.php';
                $profilePicRepo = new ProfilePicRepository();
                // Get the value and normalize it
                $newProfilePicId = filter_var($_POST['newProfilePicId'], FILTER_VALIDATE_INT);

                if ($newProfilePicId === false || $newProfilePicId <= 0) {
                    $errors[] = "La photo de profil sélectionnée est invalide";
                } elseif ($newProfilePicId === $user->getIdProfilePic()) {
                    $errors[] = "La photo de profil est la même que l'ancienne";
                } else {
                    $profilePic = $profilePicRepo->findById($newProfilePicId);
                    if ($profilePic === null) {
                        $errors[] = "La photo de profil sélectionnée est inexistante";
                    } else {
                        $user->setIdProfilePic($profilePic->getId());
                        $hasModified = true;
                    }
                }
            }
            // 5. Update in DB
            if (empty($errors) && $hasModified) {
                $userRepo->update($user);
                // Refresh the session information using is_connected()
                AuthMiddleware::is_connected();
                $success_msg = "La modification du profil a été appliquée avec succès !";

            }
        }
    } catch (Exception $exception) {
        $errors[] = "Une erreur inattendue est survenue : " . $exception->getMessage();
    }
}


