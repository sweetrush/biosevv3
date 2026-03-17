<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
$db_host = 'mysql';
$db_name = 'biosecurity_db';
$db_user = 'biosec_user';
$db_pass = 'biosec_pass';

// Response array
$response = array('success' => false, 'data' => array());

try {
    // Create database connection
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all container types
    $sql = "SELECT container_type_code, container_type_name, description FROM container_types ORDER BY container_type_code";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $containerTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $containerTypes;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Close connection
$conn = null;

// Return JSON response
echo json_encode($response);
?>
