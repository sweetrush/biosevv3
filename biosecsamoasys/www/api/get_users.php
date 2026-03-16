<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

// Check authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check admin access
if ($_SESSION['access_level'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

try {
    $conn = getDBConnection();

    // Get all users
    $stmt = $conn->query("
        SELECT
            user_id,
            username,
            full_name,
            email,
            access_level,
            department,
            is_active,
            created_at
        FROM users
        ORDER BY created_at DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process users to split full_name for display
    foreach ($users as &$user) {
        $names = explode(' ', trim($user['full_name']), 2);
        $user['first_name'] = $names[0] ?? '';
        $user['last_name'] = $names[1] ?? '';
    }

    echo json_encode([
        'success' => true,
        'data' => $users
    ]);

} catch (Exception $e) {
    error_log("Get users error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load users data'
    ]);
}
?>