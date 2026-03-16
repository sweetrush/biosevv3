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

    // Get recent cargo releases with joined data and item counts
    $sql = "SELECT
                cr.ReleaseID,
                cr.VoyageID,
                vd.VoyageNo,
                vd.VesselID,
                cr.ReleaseNo,
                cr.Importer,
                cr.ReleaseType,
                cr.ReleaseDate,
                cr.TotalCosts,
                cr.created_at,
                COUNT(ri.ReleaseItemID) as ItemCount
            FROM cargo_release cr
            LEFT JOIN voyage_details vd ON cr.VoyageID = vd.VoyageID
            LEFT JOIN release_items ri ON cr.ReleaseID = ri.ReleaseID
            GROUP BY cr.ReleaseID
            ORDER BY cr.ReleaseID DESC
            LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $releases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $releases;

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
