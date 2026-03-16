<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Check authentication
if (!requireAuth()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access - Please log in']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'CSRF token missing']);
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    $pdo = getDBConnection();

    $permitId = isset($_POST['permitId']) ? intval($_POST['permitId']) : 0;

    if ($permitId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid permit ID']);
        exit;
    }

    // Delete the permit
    $stmt = $pdo->prepare("DELETE FROM import_permits WHERE permit_id = ?");
    $stmt->execute([$permitId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Permit deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Permit not found']);
    }

} catch (PDOException $e) {
    error_log("Delete permit error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Delete permit error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
?>
