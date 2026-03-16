<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if (!requireAuth('officer')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['port_id'])) {
    echo json_encode(['success' => false, 'message' => 'Port ID is required']);
    exit;
}

$port_id = intval($data['port_id']);

if ($port_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid port ID']);
    exit;
}

try {
    $conn = getDBConnection();

    // Check if port exists
    $stmt = $conn->prepare("SELECT port_id FROM ports WHERE port_id = ?");
    $stmt->execute([$port_id]);

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Port not found']);
        exit;
    }

    // Check for foreign key references
    $total_references = 0;
    $reference_fields = ['PortOfLoadingID', 'LastPortID', 'PortOfArrivalID'];

    foreach ($reference_fields as $field) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM voyage_details WHERE $field = ?");
        $stmt->execute([$port_id]);
        $result = $stmt->fetch();
        $total_references += $result['count'];
    }

    if ($total_references > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete port: it is referenced by ' . $total_references . ' voyage(s)']);
        exit;
    }

    // Delete port
    $stmt = $conn->prepare("DELETE FROM ports WHERE port_id = ?");
    $stmt->execute([$port_id]);

    echo json_encode(['success' => true, 'message' => 'Port deleted successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
