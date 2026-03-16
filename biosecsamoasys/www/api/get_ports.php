<?php
header('Content-Type: application/json');

require_once 'config.php';

try {
    $conn = getDBConnection();

    $response = array('success' => false);

    // Get all ports
    $sql = "SELECT port_id, port_name, country
            FROM ports
            ORDER BY port_name";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $ports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $ports;

    echo json_encode($response);

} catch(Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
