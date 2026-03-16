<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if (!requireAuth('officer')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['port_id']) || !isset($data['port_name']) || !isset($data['country'])) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit;
}

$port_id = intval($data['port_id']);
$port_name = trim($data['port_name']);
$country = trim($data['country']);

if ($port_id <= 0 || empty($port_name) || empty($country)) {
    echo json_encode(['success' => false, 'message' => 'Invalid port ID or empty required fields']);
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

    // Check for duplicate excluding current
    $stmt = $conn->prepare("SELECT port_id FROM ports WHERE port_name = ? AND country = ? AND port_id != ?");
    $stmt->execute([$port_name, $country, $port_id]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A port with this name and country already exists (other than this port)']);
        exit;
    }

    // Update port
    $stmt = $conn->prepare("UPDATE ports SET port_name = ?, country = ? WHERE port_id = ?");
    $stmt->execute([$port_name, $country, $port_id]);

    echo json_encode(['success' => true, 'message' => 'Port updated successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
