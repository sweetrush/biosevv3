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

    // Get recent seizures with joined data
    $sql = "SELECT
                ps.PassengerSeizureID,
                ps.VoyageID,
                vd.VoyageNo,
                vd.VesselID,
                ps.SeizureDate,
                ps.SeizureNo,
                ps.Importer,
                ps.CommodityType,
                ps.Quantity,
                ps.Unit,
                ps.ActionTaken,
                ps.OfficerName,
                ps.created_at
            FROM passenger_seizure ps
            LEFT JOIN voyage_details vd ON ps.VoyageID = vd.VoyageID
            ORDER BY ps.PassengerSeizureID DESC
            LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $seizures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $seizures;

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
