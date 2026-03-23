<?php

class AuthMiddleware {
    // CONSTRUCTOR (private because this class don't need to be instanciate)
    private function __construct() {}

    // METHODS

    // Simply check if the user is connected (no need to check if Id is valid)
    // if the user is connected you logically have access to those attributes :
    //  - userId -userRole -username -profilePicPath
    public static function is_connected() : bool {
        // If not userId the user isn't connected
        if (!isset($_SESSION['userId'])) {
            return false;
        }

        // Now we force update the session (to be sure the Session keep the right information)
        // without that we expose ourselves to security failure
        // (ex: a user that isn't an admin anymore keeps being an admin by the eyes of his session if we don't update it)
        require_once __DIR__ . '/../Model/UserAccount.php';
        require_once __DIR__ . '/../Repository/UserAccountRepository.php';
        require_once __DIR__ . '/../Model/ProfilePic.php';
        require_once __DIR__ . '/../Repository/ProfilePicRepository.php';

        $userRepo = new UserAccountRepository();
        $user = $userRepo->findById((int)$_SESSION['userId']);
        // If the user is not found in the DB we destroy his session
        if ($user === null) {
            session_unset();
            session_destroy();
            return false;
        }
        // Refresh his session's information
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['userRole'] = $user->getUserRole()->value;
        // Get the profile pic path
        $profilePicRepo = new ProfilePicRepository();
        $profilePic = $profilePicRepo->findById($user->getIdProfilePic());
        if ($profilePic) {
            $_SESSION['profilePicPath'] = $profilePic->getPicturePath();
        }

        return true;
    }

    // Check if the user is connected and has 'admin' as user_role
    public static function is_admin() : bool {
        if (self::is_connected() && isset($_SESSION['userRole'])) {
            return $_SESSION['userRole'] === 'admin';
        }
        return false;
    }

}