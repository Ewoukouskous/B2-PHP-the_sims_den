<?php

class AuthMiddleware {
    // CONSTRUCTOR (private because this class don't need to be instanciate)
    private function __construct() {}
    // METHODS
    // Simply check if the user is connected (no need to check if Id is valid)
    public static function is_connected(array $session) : bool {
        return isset($session['userId']);
    }

    // Check if the user is connected and has 'admin' as user_role
    public static function is_admin(array $session) : bool {
        if (self::is_connected($session) && isset($session['userRole'])) {
            return $session['userRole'] === 'admin';
        }
        return false;
    }

}