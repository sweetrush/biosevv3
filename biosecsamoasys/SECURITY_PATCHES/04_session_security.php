<?php
/**
 * SECURITY PATCH 4: Session Security Configuration
 * Priority: HIGH
 * Files to modify: All PHP files using sessions
 */

echo "=== SECURITY PATCH 4: SESSION SECURITY ===\n\n";

echo "FILE: www/api/config.php (add to existing file)\n";
echo "Add AFTER session_start() if present, or at beginning of functions that use sessions:\n";
echo "--------\n";
?>
<?php echo htmlspecialchars('<?php
// Session security configuration
function configureSessionSecurity() {
    // Set secure session parameters
    ini_set(\'session.cookie_httponly\', 1);          // Prevent JavaScript access to session ID
    ini_set(\'session.use_only_cookies\', 1);         // Only use cookies, not URL parameters
    ini_set(\'session.cookie_samesite\', \'Strict\'); // Prevent CSRF via cross-site requests

    // Set session lifetime (30 minutes idle timeout)
    $sessionLifetime = 1800; // 30 minutes
    ini_set(\'session.gc_maxlifetime\', $sessionLifetime);
    ini_set(\'session.cookie_lifetime\', 0); // Session cookie expires when browser closes

    // Enable session ID regeneration for security
    ini_set(\'session.regenerate_id\', 1);

    // Only use HTTPS for session cookies (enable this when HTTPS is available)
    // ini_set(\'session.cookie_secure\', 1);
}

// Call configuration when session starts
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    configureSessionSecurity();
}

// Session timeout check
function checkSessionTimeout($timeout = 1800) {
    if (isset($_SESSION[\'last_activity\']) &&
        (time() - $_SESSION[\'last_activity\'] > $timeout)) {
        // Session expired
        session_unset();
        session_destroy();
        session_start();
        $_SESSION[\'flash_message\'] = \'Session expired. Please log in again.\';
        header(\'Location: ../login.php\');
        exit;
    }
    $_SESSION[\'last_activity\'] = time();
}

// Regenerate session ID periodically
function regenerateSessionIdIfNeeded() {
    if (!isset($_SESSION[\'created\'])) {
        $_SESSION[\'created\'] = time();
        session_regenerate_id(true);
        return;
    }

    // Regenerate session ID every 15 minutes (900 seconds)
    if (time() - $_SESSION[\'created\'] > 900) {
        session_regenerate_id(true);
        $_SESSION[\'created\'] = time();
    }
}
?>')?>

<?php
echo "\n\nFILE: www/login.php (update login code)\n";
echo "Replace the session_regenerate_id() section:\n";
echo "--------\n";
?>
<?php echo htmlspecialchars('<?php
// Line 40-42 (existing code)
if ($user && password_verify($password, $user[\'password_hash\'])) {
    // Login successful
    $_SESSION[\'user_id\'] = $user[\'user_id\'];
    $_SESSION[\'username\'] = $user[\'username\'];
    $_SESSION[\'first_name\'] = $user[\'first_name\'];
    $_SESSION[\'last_name\'] = $user[\'last_name\'];
    $_SESSION[\'email\'] = $user[\'email\'];
    $_SESSION[\'access_level\'] = $user[\'access_level\'];
    $_SESSION[\'department\'] = $user[\'department\'];

    // Initialize session security variables
    $_SESSION[\'last_activity\'] = time();
    $_SESSION[\'created\'] = time();

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);

    // Redirect to dashboard
    header(\'Location: index.php\');
    exit;
}
?>')?>

<?php
echo "\n\nUSAGE in all authenticated pages:\n";
echo "--------\n";
echo "Add this code at the beginning of each protected PHP file (after session_start()):\n\n";
echo "<?php\n";
echo "session_start();\n";
echo "require_once 'api/config.php';\n\n";
echo "// Check authentication\n";
echo "if (!isLoggedIn()) {\n";
echo "    header('Location: login.php');\n";
echo "    exit;\n";
echo "}\n\n";
echo "// Check session timeout\n";
echo "checkSessionTimeout();\n\n";
echo "// Regenerate session ID if needed\n";
echo "regenerateSessionIdIfNeeded();\n";
echo "?>\n";

echo "\n\nNOTES:\n";
echo "- Uncomment 'session.cookie_secure' line when HTTPS is enabled\n";
echo "- Adjust session timeout (1800 = 30 minutes) as needed\n";
echo "- Always call checkSessionTimeout() before sensitive operations\n";

?>