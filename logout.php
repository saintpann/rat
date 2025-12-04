<?php
require_once __DIR__ . '/config.php';

// Unset all of the session variables
$_SESSION = [];

// Delete the session cookie to fully kill the session on the client side
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

header("Location: index.php");
exit;
?>