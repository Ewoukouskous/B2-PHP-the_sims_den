<?php

if (session_status() === PHP_SESSION_NONE) {session_start();}

// SELECT the actual DIR (public/auth), and ask the go up to the root (2 levels)
$root_path = dirname(__DIR__, 2);

require_once $root_path . '/src/Database/DatabaseConnection.php';
require_once $root_path . '/src/Security/AuthMiddleware.php';

// Check if the user is already login, we redirect him to the index.php
if (AuthMiddleware::is_connected($_SESSION)) {
    header('Location: ../index.php');
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
            header("Location: ../index.php");
            exit();
        } else {
            $error_msg = "Nom d'utilisateur / Email ou le mot de passe est incorrect";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Connexion - The Sims Den</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="h-full m-0">
<div id="background" class="fixed top-0 left-0 w-full h-full bg-cover bg-center">
    <img src="../img/bg.png" alt="Background Image" class="w-full h-full object-cover">
</div>

<div id="content" class="relative flex flex-col items-center h-full p-3 pt-8">

    <!--        NAVBAR Section-->

    <?php
    $basePath = '../';
    $showLoginButton = true;
    $showProfilePic = false;
    $searchPlaceholder = 'Recherche :';
    include '../includes/header.php';
    ?>

    <!--        LOGIN FORM Section-->

    <div id="login-container" class="relative w-full max-w-6xl mt-12">

        <!--            TITLE (positioned above the container)-->
        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 z-10">
            <h1 class="text-4xl font-bold text-[#3769a9] bg-[#F0EEE9] inline-block px-12 py-3 rounded-full shadow-[0px_2px_0px_1.5px_rgba(51,184,66,1)] whitespace-nowrap">
                Connexion
            </h1>
        </div>

        <div class="bg-[#F0EEE9] bg-opacity-95 rounded-[3rem] pt-24 px-8 pb-8 shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)]">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

                <!--                LEFT SIDE - ILLUSTRATION-->
                <div class="hidden lg:flex justify-center items-center">
                    <div class="relative">
                        <img src="../img/sims_babyfoot.png"
                             alt="Sims jouant au baby-foot"
                             class="w-full max-w-md object-contain drop-shadow-2xl"
                             onerror="this.style.display='none'">
                    </div>
                </div>

                <!--                RIGHT SIDE - FORM-->
                <div class="space-y-4">

                    <?php if (!empty($error_msg)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                            <p class="font-medium"><?php echo htmlspecialchars($error_msg); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="" class="space-y-4">

                        <!--                        USERNAME/EMAIL FIELD-->
                        <div>
                            <label for="username" class="flex block text-lg font-medium text-[#3769a9] mt-18">
                                Pseudonyme / Courriel<span class="text-red-500">*</span> :
                            </label>
                            <input type="text"
                                   id="username"
                                   name="username"
                                   placeholder="SulSul23400"
                                   required
                                   class="w-full px-5 py-2 bg-white rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] placeholder:opacity-50 text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out">
                        </div>

                        <!--                        PASSWORD FIELD-->
                        <div>
                            <label for="password" class="block text-lg font-medium text-[#3769a9] mb-1">
                                Mot de passe<span class="text-red-500">*</span> :
                            </label>
                            <div class="relative">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       placeholder="****************"
                                       required
                                       class="w-full px-5 py-2 pr-12 bg-white rounded-full shadow-[0px_2px_0px_1.5px_rgba(158,158,158,1)] placeholder:text-[#3769a9] placeholder:opacity-50 text-[#3769a9] font-medium outline-none focus:ring-2 focus:ring-[#3769a9] focus:ring-opacity-50 transition duration-200 ease-in-out">
                                <button type="button"
                                        id="togglePassword"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-[#3769a9] hover:text-[#2a5885] focus:outline-none transition duration-200"
                                        aria-label="Afficher/Masquer le mot de passe">
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!--                        REMEMBER ME CHECKBOX-->
                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox"
                                   id="remember"
                                   name="remember"
                                   class="w-4 h-4 rounded border-[#3769a9] text-[#3769a9] focus:ring-[#3769a9] cursor-pointer">
                            <label for="remember" class="text-[#3769a9] font-medium cursor-pointer select-none">
                                Se souvenir de moi
                            </label>
                        </div>

                        <!--                        SUBMIT BUTTON-->
                        <div class="flex justify-center">
                            <button type="submit"
                                    name="login"
                                    class="px-14 py-2.5 bg-[#3769a9] hover:bg-[#2a5885] text-white text-lg font-bold rounded-full shadow-lg transition duration-200 ease-in-out transform hover:scale-105">
                                Se connecter
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>
<script src="../js/togglePassword.js"></script>
</body>
</html>

