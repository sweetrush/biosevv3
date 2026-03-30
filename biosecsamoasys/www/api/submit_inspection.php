<?php
header('Content-Type: application/json');

// Start session for CSRF validation
session_start();

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token missing']);
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Helper function to convert empty strings to NULL for INT fields
function toIntOrNull($value) {
    if ($value === '' || $value === null) return null;
    return (int)$value;
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
        // Validate required fields
        if (empty($_POST['VoyageID']) || empty($_POST['CommodityTypeID'])) {
            $response['message'] = 'VoyageID and CommodityTypeID are required';
            echo json_encode($response);
            exit;
        }

        // Prepare INSERT statement
        $sql = "INSERT INTO passenger_inspection
                (VoyageID, CommodityTypeID, LocationID, NoOfConsignments, NoOfNonCompliant, ModifiedBy, ModifiedDate)
                VALUES
                (:VoyageID, :CommodityTypeID, :LocationID, :NoOfConsignments, :NoOfNonCompliant, :ModifiedBy, :ModifiedDate)";

        $stmt = $conn->prepare($sql);

        // Bind parameters - convert numeric fields to INT
        $stmt->bindParam(':VoyageID', $_POST['VoyageID'], PDO::PARAM_INT);
        $stmt->bindValue(':CommodityTypeID', toIntOrNull($_POST['CommodityTypeID'] ?? null), PDO::PARAM_INT);
        $stmt->bindValue(':LocationID', toIntOrNull($_POST['LocationID'] ?? null), PDO::PARAM_INT);
        $stmt->bindValue(':NoOfConsignments', toIntOrNull($_POST['NoOfConsignments'] ?? null), PDO::PARAM_INT);
        $stmt->bindValue(':NoOfNonCompliant', toIntOrNull($_POST['NoOfNonCompliant'] ?? null), PDO::PARAM_INT);
        $stmt->bindParam(':ModifiedBy', $_POST['ModifiedBy'], PDO::PARAM_STR);
        $stmt->bindParam(':ModifiedDate', $_POST['ModifiedDate'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            $inspectionID = $conn->lastInsertId();
            $response['success'] = true;
            $response['message'] = 'Passenger inspection record saved successfully!';
            $response['inspection_id'] = $inspectionID;

            // Mark passenger_inspection step as complete
            try {
                $statusData = array(
                    'VoyageID' => $_POST['VoyageID'],
                    'action' => 'complete_step',
                    'step' => 'passenger_inspection',
                    'performed_by' => $_POST['ModifiedBy']
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
                    error_log('Failed to update voyage status for VoyageID: ' . $_POST['VoyageID']);
                    $response['warning'] = 'Passenger inspection saved but voyage status update failed.';
                }
            } catch (Exception $e) {
                error_log('Failed to update voyage status: ' . $e->getMessage());
                $response['warning'] = 'Passenger inspection saved but voyage status update failed: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Failed to save inspection record';
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
