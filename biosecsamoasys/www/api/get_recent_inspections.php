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

    // Get recent inspections with joined data
    $sql = "SELECT
                pi.PassengerInspectionID,
                pi.VoyageID,
                vd.VoyageNo,
                vd.VesselID,
                ct.CommodityType,
                ct.CommodityGroupID,
                pi.LocationID,
                pi.NoOfConsignments,
                pi.NoOfNonCompliant,
                pi.ModifiedBy,
                pi.ModifiedDate,
                pi.created_at
            FROM passenger_inspection pi
            LEFT JOIN voyage_details vd ON pi.VoyageID = vd.VoyageID
            LEFT JOIN commodity_types ct ON pi.CommodityTypeID = ct.CommodityTypeID
            ORDER BY pi.PassengerInspectionID DESC
            LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $inspections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $inspections;

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
