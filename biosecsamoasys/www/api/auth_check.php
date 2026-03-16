<?php
/**
 * Authentication Middleware
 * Include this file at the top of all protected pages
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Store the requested page for redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Optional: Check if session is still valid (extend session lifetime check)
// You can add additional checks here as needed
?>
