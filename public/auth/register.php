<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SELECT the actual DIR (public/auth), and ask the go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

// Check if the user is already login (already have a userId), we redirect him to the index.php
if (!empty($_SESSION['userId'])) {
    header('Location: /index.php');
    exit();
}

$error_msg = '';

// If it's a POST request we start the registration process
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    // DEPENDENCIES
    require_once $root_path . '/src/Database/DatabaseConnection.php';
    require_once $root_path . '/src/Model/UserAccount.php';
    require_once $root_path . '/src/Repository/UserAccountRepository.php';
    require_once $root_path . '/src/Enum/UserRole.php';
    require_once $root_path . '/src/Model/ProfilePic.php';
    require_once $root_path . '/src/Repository/ProfilePicRepository.php';

    $userRepository = new UserAccountRepository();

    // Now we get each parameter before validating them
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];

    // Fields validation :
    // first we check that all fields are filled and then check specifications for each field
    if ($email === '' || $username === '' || $password === '' || $confirm_password === '') {
        $error_msg = 'ÉCHEC : Assurez vous que tous les champs sont remplis';
    // check if the email is valid
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'ÉCHEC : Champ Email non valide';
    // check that the username size is between 5 and 30 chars
    } elseif (strlen($username) < 5 || strlen($username) > 30) {
        $error_msg = 'ÉCHEC : Le nom d\'utilisateur doit faire entre 5 et 30 caractères';
    // check if the password is long enough (the complexity is checked by the JS)
    } elseif (strlen($password) < 8) {
        $error_msg = 'ÉCHEC : Le mot de passe doit faire un minimum de 8 caractères';
    // check if both password are the same
    } elseif ($confirm_password !== $password) {
        $error_msg = 'ÉCHEC : Les deux mot de passe ne correspondent pas';
    // check that the email is not already taken
    } elseif ($userRepository->findByEmail($email) !== null) {
        $error_msg = 'ÉCHEC : Cet email est déjà utilisé';
    // check that the username is not already taken
    } elseif ($userRepository->findByUsername($username) !== null) {
        $error_msg = 'ÉCHEC : Ce nom d\'utilisateur est déjà utilisé';
    } else {
        // If everything is valid, we create the new UserAccount object and then call the UserAccountRepository->insert()
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $userAccount = new UserAccount($username, $email, $hashed_password);
        $userRepository->insert($userAccount);

        // Check if an ID is now associated to the UserAccount object. If yes, the user_account has been created
        if ($userAccount->getId() !== null) {

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

            // The user_account creation is done, now we redirect to the homepage
            header("Location: /index.php");
            exit();
        } else {
            $error_msg = "Échec : Une erreur est survenue lors du processus d'inscription";
        }
    }
}
?>

