<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

// Check authentication and admin access
if (!requireAuth('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$userId = intval($_POST['user_id'] ?? 0);

// Validation
if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Prevent self-deletion
if ($userId == ($_SESSION['user_id'] ?? 0)) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit;
}

try {
    $conn = getDBConnection();

    // Check if user exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);

    $user = $stmt->fetch();
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Check if user has any associated data (voyages, inspections, etc.)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM voyage_details WHERE ModifiedByOfficerID = ?
        UNION ALL
        SELECT COUNT(*) as count FROM passenger_inspection WHERE ModifiedByOfficerID = ?
        UNION ALL
        SELECT COUNT(*) as count FROM passenger_seizure WHERE ModifiedByOfficerID = ?
        UNION ALL
        SELECT COUNT(*) as count FROM cargo_seizure WHERE ModifiedByOfficerID = ?
        UNION ALL
        SELECT COUNT(*) as count FROM cargo_release WHERE ModifiedByOfficerID = ?
    ");
    $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
    $counts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $totalReferences = array_sum($counts);

    if ($totalReferences > 0) {
        // Soft delete - deactivate instead of delete
        $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);

        $action = 'DEACTIVATE_USER';
        $details = "Deactivated user '{$user['username']}' (ID: {$userId}) - {$totalReferences} associated records found";
    } else {
        // Hard delete - no associated data
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);

        $action = 'DELETE_USER';
        $details = "Deleted user '{$user['username']}' (ID: {$userId})";
    }

    // Log the action
    $stmt = $conn->prepare("
        INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
        VALUES (NULL, ?, ?, ?)
    ");
    $stmt->execute([$action, $details, $_SESSION['user_id'] ?? 'System']);

    $message = $totalReferences > 0
        ? 'User deactivated successfully (user has associated data)'
        : 'User deleted successfully';

    echo json_encode([
        'success' => true,
        'message' => $message,
        'action' => $action
    ]);

} catch (Exception $e) {
    error_log("Delete user error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete user: ' . $e->getMessage()
    ]);
}
?>