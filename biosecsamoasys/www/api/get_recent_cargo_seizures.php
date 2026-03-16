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

    // Get recent cargo seizures with joined data
    $sql = "SELECT
                cs.CargoSeizureID,
                cs.VoyageID,
                vd.VoyageNo,
                vd.VesselID,
                cs.SeizureDate,
                cs.SeizureNo,
                cs.Importer,
                cs.CommodityType,
                cs.Quantity,
                cs.Unit,
                cs.ActionTaken,
                cs.SeizingOfficerName,
                cs.created_at
            FROM cargo_seizure cs
            LEFT JOIN voyage_details vd ON cs.VoyageID = vd.VoyageID
            ORDER BY cs.CargoSeizureID DESC
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
