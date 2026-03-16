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

    $response = array('success' => false, 'message' => '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get JSON data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Validate required fields
        if (empty($data['VoyageID']) || empty($data['ReleaseDate'])) {
            $response['message'] = 'VoyageID and ReleaseDate are required';
            echo json_encode($response);
            exit;
        }

        // Begin transaction
        $conn->beginTransaction();

        try {
            // Insert cargo release
            $sql = "INSERT INTO cargo_release
                    (VoyageID, ReleaseNo, Importer, ReleaseType, ReleaseDate, TotalCosts, Comments)
                    VALUES
                    (:VoyageID, :ReleaseNo, :Importer, :ReleaseType, :ReleaseDate, :TotalCosts, :Comments)";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':VoyageID', $data['VoyageID'], PDO::PARAM_INT);
            $stmt->bindParam(':ReleaseNo', $data['ReleaseNo'], PDO::PARAM_STR);
            $stmt->bindParam(':Importer', $data['Importer'], PDO::PARAM_STR);
            $stmt->bindParam(':ReleaseType', $data['ReleaseType'], PDO::PARAM_STR);
            $stmt->bindParam(':ReleaseDate', $data['ReleaseDate'], PDO::PARAM_STR);
            $stmt->bindParam(':TotalCosts', $data['TotalCosts'], PDO::PARAM_STR);
            $stmt->bindParam(':Comments', $data['Comments'], PDO::PARAM_STR);

            $stmt->execute();
            $releaseID = $conn->lastInsertId();

            // Insert release items if provided
            if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
                $itemSql = "INSERT INTO release_items
                            (ReleaseID, ContainerBLNo, Description, Quantity, Unit, Weight, Action,
                             CommodityType, ItemCondition, PermitNo, CertificateNo, CountryOfOrigin, TransferDepot)
                            VALUES
                            (:ReleaseID, :ContainerBLNo, :Description, :Quantity, :Unit, :Weight, :Action,
                             :CommodityType, :ItemCondition, :PermitNo, :CertificateNo, :CountryOfOrigin, :TransferDepot)";

                $itemStmt = $conn->prepare($itemSql);

                foreach ($data['items'] as $item) {
                    $itemStmt->bindParam(':ReleaseID', $releaseID, PDO::PARAM_INT);
                    $itemStmt->bindParam(':ContainerBLNo', $item['ContainerBLNo'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':Description', $item['Description'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':Quantity', $item['Quantity'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':Unit', $item['Unit'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':Weight', $item['Weight'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':Action', $item['Action'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':CommodityType', $item['CommodityType'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':ItemCondition', $item['ItemCondition'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':PermitNo', $item['PermitNo'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':CertificateNo', $item['CertificateNo'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':CountryOfOrigin', $item['CountryOfOrigin'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':TransferDepot', $item['TransferDepot'], PDO::PARAM_STR);
                    $itemStmt->execute();
                }
            }

            // Commit transaction
            $conn->commit();

            $response['success'] = true;
            $response['message'] = 'Cargo release saved successfully!';
            $response['release_id'] = $releaseID;

            // Mark cargo_release step as complete
            try {
                // Determine the officer who performed the action
                $performedBy = !empty($data['ReleaseOfficer']) ? $data['ReleaseOfficer'] :
                              (!empty($data['InspectorName']) ? $data['InspectorName'] : 'Bio Officer');

                $statusData = array(
                    'VoyageID' => $data['VoyageID'],
                    'action' => 'complete_step',
                    'step' => 'cargo_release',
                    'performed_by' => $performedBy
                );

                $context = stream_context_create(array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => http_build_query($statusData)
                    )
                ));

                // Call the voyage_status.php API to mark step complete
                @file_get_contents('http://localhost/api/voyage_status.php', false, $context);
            } catch (Exception $e) {
                // Log error but don't fail the main request
                error_log('Failed to update voyage status: ' . $e->getMessage());
            }

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            throw $e;
        }
    } else {
        $response['message'] = 'Invalid request method';
    }

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
