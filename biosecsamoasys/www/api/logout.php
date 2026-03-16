<?php
session_start();

require_once 'config.php';

// Clear all session data
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with a message
session_start();
$_SESSION['flash_message'] = 'You have been logged out successfully.';

header('Location: ../login.php');
exit;
?>