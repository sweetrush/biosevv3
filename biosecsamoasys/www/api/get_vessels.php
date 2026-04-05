<?php
header('Content-Type: application/json');

// Database connection
$host = 'mysql';
$dbname = 'biosecurity_db';
$username = 'biosec_user';
$password = 'biosec_pass';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $response = array('success' => false);

    // Get all vessels
    // Note: vessels table uses snake_case columns, aliased to camelCase for frontend compatibility
    $sql = "SELECT vessel_id AS VesselID, vessel_name AS VesselName, vessel_type AS VesselType
            FROM vessels
            ORDER BY vessel_name ASC";

    $stmt = $conn->query($sql);
    $vessels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $vessels;

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
