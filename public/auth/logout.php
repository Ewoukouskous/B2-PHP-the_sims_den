<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// We reset the actual session of the user
// As the $_SESSION variable is an array we can erase its content like an actual array
$_SESSION = [];

// Force the user's cookie to expire (for maximal security)
if (isset($_COOKIE[session_name()])) {
    $userCookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 1,
        $userCookieParams['path'],
        $userCookieParams['domain'],
        $userCookieParams['secure'],
        $userCookieParams['httponly']
    );
}


// Then by security we use session_destroy()
session_destroy();
// After all that we redirect to the homepage
header('Location: ../index.php');
exit();