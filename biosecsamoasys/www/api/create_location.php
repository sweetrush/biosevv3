<?php
header('Content-Type: application/json');

require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['location_name']) || !isset($data['region']) || !isset($data['location_type'])) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing: location_name, region, and location_type are required']);
    exit;
}

$locationName = trim($data['location_name']);
$region = trim($data['region']);
$locationType = trim($data['location_type']);
$isActive = isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1;

if (empty($locationName) || empty($region) || empty($locationType)) {
    echo json_encode(['success' => false, 'message' => 'Location name, region, and type must not be empty']);
    exit;
}

try {
    $conn = getDBConnection();

    // Check for duplicate
    $stmt = $conn->prepare("SELECT location_id FROM locations WHERE location_name = ? AND region = ?");
    $stmt->execute([$locationName, $region]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A location with this name and region already exists']);
        exit;
    }

    // Auto-generate location ID
    $stmt = $conn->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING(location_id, 4) AS UNSIGNED)), 0) as max_id FROM locations");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $newId = 'LOC' . str_pad((int)$result['max_id'] + 1, 3, '0', STR_PAD_LEFT);

    // Insert new location
    $stmt = $conn->prepare("INSERT INTO locations (location_id, location_name, region, location_type, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$newId, $locationName, $region, $locationType, $isActive]);

    echo json_encode(['success' => true, 'message' => 'Location created successfully', 'location_id' => $newId]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
