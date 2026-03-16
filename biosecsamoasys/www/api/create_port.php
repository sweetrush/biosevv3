<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if (!requireAuth('officer')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['port_name']) || !isset($data['country'])) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit;
}

$port_name = trim($data['port_name']);
$country = trim($data['country']);

if (empty($port_name) || empty($country)) {
    echo json_encode(['success' => false, 'message' => 'Port name and country are required']);
    exit;
}

try {
    $conn = getDBConnection();

    // Check for duplicate
    $stmt = $conn->prepare("SELECT port_id FROM ports WHERE port_name = ? AND country = ?");
    $stmt->execute([$port_name, $country]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A port with this name and country already exists']);
        exit;
    }

    // Insert new port
    $stmt = $conn->prepare("INSERT INTO ports (port_name, country) VALUES (?, ?)");
    $stmt->execute([$port_name, $country]);

    echo json_encode(['success' => true, 'message' => 'Port created successfully', 'port_id' => $conn->lastInsertId()]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
