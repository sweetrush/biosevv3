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

        // Bind parameters
        $stmt->bindParam(':VoyageID', $_POST['VoyageID'], PDO::PARAM_INT);
        $stmt->bindParam(':CommodityTypeID', $_POST['CommodityTypeID'], PDO::PARAM_STR);
        $stmt->bindParam(':LocationID', $_POST['LocationID'], PDO::PARAM_STR);
        $stmt->bindParam(':NoOfConsignments', $_POST['NoOfConsignments'], PDO::PARAM_STR);
        $stmt->bindParam(':NoOfNonCompliant', $_POST['NoOfNonCompliant'], PDO::PARAM_STR);
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
                @file_get_contents('http://localhost/api/voyage_status.php', false, $context);
            } catch (Exception $e) {
                // Log error but don't fail the main request
                error_log('Failed to update voyage status: ' . $e->getMessage());
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
