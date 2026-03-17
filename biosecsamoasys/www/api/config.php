<?php
// Database configuration
define('DB_HOST', 'mysql');
define('DB_NAME', 'biosecurity_db');
define('DB_USER', 'biosec_user');
define('DB_PASS', 'biosec_pass');

// Function to get database connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch(PDOException $e) {
        throw new Exception("Connection failed: " . $e->getMessage());
    }
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'first_name' => $_SESSION['first_name'] ?? null,
        'last_name' => $_SESSION['last_name'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'access_level' => $_SESSION['access_level'] ?? null,
        'department' => $_SESSION['department'] ?? null
    ];
}

function requireAuth($required_role = null) {
    if (!isLoggedIn()) {
        return false;
    }

    if ($required_role !== null) {
        $user_role = $_SESSION['access_level'] ?? '';
        $role_hierarchy = ['viewer' => 0, 'officer' => 1, 'admin' => 2];

        if (!isset($role_hierarchy[$user_role]) ||
            $role_hierarchy[$user_role] < $role_hierarchy[$required_role]) {
            return false;
        }
    }

    return true;
}

function logout() {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}
?>
