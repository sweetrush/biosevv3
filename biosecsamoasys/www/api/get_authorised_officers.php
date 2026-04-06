<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if (!requireAuth('officer')) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("
        SELECT user_id, username, first_name, last_name, department
        FROM users
        WHERE access_level = 'authorising_officer'
          AND is_active = 1
        ORDER BY first_name, last_name
    ");
    $stmt->execute();
    $officers = $stmt->fetchAll();

    $result = [];
    foreach ($officers as $officer) {
        $result[] = [
            'user_id' => $officer['user_id'],
            'name' => trim($officer['first_name'] . ' ' . $officer['last_name']),
            'department' => $officer['department'],
        ];
    }

    echo json_encode(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
