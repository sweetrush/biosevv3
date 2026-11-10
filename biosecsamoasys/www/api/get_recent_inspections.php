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

    // Check for voyage_id filter
    $voyageId = isset($_GET['voyage_id']) ? (int)$_GET['voyage_id'] : null;

    if ($voyageId) {
        // Get inspections for a specific voyage
        $sql = "SELECT
                    pi.PassengerInspectionID,
                    pi.VoyageID,
                    pi.CommodityTypeID,
                    vd.VoyageNo,
                    vd.VesselID,
                    ct.CommodityType,
                    ct.CommodityGroupID,
                    l.location_name,
                    pi.LocationID,
                    pi.NoOfConsignments,
                    pi.NoOfNonCompliant,
                    pi.ModifiedBy,
                    pi.ModifiedDate,
                    pi.created_at
                FROM passenger_inspection pi
                LEFT JOIN voyage_details vd ON pi.VoyageID = vd.VoyageID
                LEFT JOIN commodity_types ct ON pi.CommodityTypeID = ct.CommodityTypeID
                LEFT JOIN locations l ON pi.LocationID = l.location_id
                WHERE pi.VoyageID = :VoyageID
                ORDER BY pi.PassengerInspectionID ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':VoyageID', $voyageId, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Get all recent inspections (limited to 20)
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
    }

    $inspections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $inspections;
    $response['count'] = count($inspections);

    if ($voyageId) {
        $response['voyage_id'] = $voyageId;
    }

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
