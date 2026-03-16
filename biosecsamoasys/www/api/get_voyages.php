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

    // Get all voyages with key information
    $sql = "SELECT VoyageID, VoyageNo, VesselID, ArrivalDate, PortOfArrivalID
            FROM voyage_details
            ORDER BY VoyageID DESC
            LIMIT 100";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $voyages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $voyages;

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
