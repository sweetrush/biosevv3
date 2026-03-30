<?php
header('Content-Type: application/json');

// Start session for CSRF validation
session_start();

// Helper functions for type conversion
function toIntOrNull($value) {
    if ($value === '' || $value === null) return null;
    return (int)$value;
}

function toFloatOrNull($value) {
    if ($value === '' || $value === null) return null;
    return (float)$value;
}

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

        // CSRF token validation
        if (empty($data['csrf_token']) || empty($_SESSION['csrf_token'])) {
            $response['message'] = 'CSRF token missing. Please refresh the page and try again.';
            echo json_encode($response);
            exit;
        }

        if (!hash_equals($_SESSION['csrf_token'], $data['csrf_token'])) {
            $response['message'] = 'Invalid CSRF token. Please refresh the page and try again.';
            echo json_encode($response);
            exit;
        }

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
            $stmt->bindValue(':TotalCosts', toFloatOrNull($data['TotalCosts'] ?? null), PDO::PARAM_STR);
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
                    $itemStmt->bindValue(':Quantity', toFloatOrNull($item['Quantity'] ?? null), PDO::PARAM_STR);
                    $itemStmt->bindParam(':Unit', $item['Unit'], PDO::PARAM_STR);
                    $itemStmt->bindValue(':Weight', toFloatOrNull($item['Weight'] ?? null), PDO::PARAM_STR);
                    $itemStmt->bindParam(':Action', $item['Action'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':CommodityType', $item['CommodityType'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':ItemCondition', $item['ItemCondition'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':PermitNo', $item['PermitNo'], PDO::PARAM_STR);
                    $itemStmt->bindParam(':CertificateNo', $item['CertificateNo'], PDO::PARAM_STR);
                    $itemStmt->bindValue(':CountryOfOrigin', toIntOrNull($item['CountryOfOrigin'] ?? null), PDO::PARAM_INT);
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
                // Use logged-in username from session
                $performedBy = $_SESSION['username'] ?? 'Bio Officer';

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
                $statusResult = file_get_contents('http://lighttpd/api/voyage_status.php', false, $context);
                if ($statusResult === false) {
                    error_log('Failed to update voyage status for VoyageID: ' . $data['VoyageID']);
                    $response['warning'] = 'Cargo release saved but voyage status update failed.';
                }
            } catch (Exception $e) {
                error_log('Failed to update voyage status: ' . $e->getMessage());
                $response['warning'] = 'Cargo release saved but voyage status update failed: ' . $e->getMessage();
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
