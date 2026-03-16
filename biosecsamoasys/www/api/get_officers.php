<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config.php';

// Check authentication
if (!requireAuth()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access - Please log in']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Return officer list for dropdown
    $sql = "SELECT officer_id, officer_name, officer_email, officer_role, department FROM officers WHERE is_active = 1 ORDER BY officer_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $officers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Officers loaded successfully',
        'data' => $officers,
        'count' => count($officers)
    ]);

} catch(PDOException $e) {
    error_log("Get officers error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} catch(Exception $e) {
    error_log("Get officers error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred'
    ]);
}
?>