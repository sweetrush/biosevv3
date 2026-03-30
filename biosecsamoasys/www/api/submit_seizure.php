<?php
header('Content-Type: application/json');

// Start session for CSRF validation
session_start();

// Helper function to convert empty strings to NULL for INT fields
function toIntOrNull($value) {
    if ($value === '' || $value === null) return null;
    return (int)$value;
}

// Helper function to convert empty strings to NULL for DECIMAL fields
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
        // CSRF token validation
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
            $response['message'] = 'CSRF token missing. Please refresh the page and try again.';
            echo json_encode($response);
            exit;
        }

        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $response['message'] = 'Invalid CSRF token. Please refresh the page and try again.';
            echo json_encode($response);
            exit;
        }

        // Validate required fields
        if (empty($_POST['VoyageID']) || empty($_POST['SeizureDate'])) {
            $response['message'] = 'VoyageID and SeizureDate are required';
            echo json_encode($response);
            exit;
        }

        // Prepare INSERT statement
        $sql = "INSERT INTO passenger_seizure
                (VoyageID, SeizureDate, SeizureNo, Importer, DetectionMethod, PortOfEntry, GoodsDeclared,
                 CountryOfOrigin, CommodityType, Description, Quantity, Unit, Volume,
                 OfficerName, ActionTaken, ActionOfficer, DateActionCompleted, Comments)
                VALUES
                (:VoyageID, :SeizureDate, :SeizureNo, :Importer, :DetectionMethod, :PortOfEntry, :GoodsDeclared,
                 :CountryOfOrigin, :CommodityType, :Description, :Quantity, :Unit, :Volume,
                 :OfficerName, :ActionTaken, :ActionOfficer, :DateActionCompleted, :Comments)";

        $stmt = $conn->prepare($sql);

        // Bind parameters - convert numeric fields to INT
        $stmt->bindParam(':VoyageID', $_POST['VoyageID'], PDO::PARAM_INT);
        $stmt->bindParam(':SeizureDate', $_POST['SeizureDate'], PDO::PARAM_STR);
        $stmt->bindParam(':SeizureNo', $_POST['SeizureNo'], PDO::PARAM_STR);
        $stmt->bindParam(':Importer', $_POST['Importer'], PDO::PARAM_STR);
        $stmt->bindParam(':DetectionMethod', $_POST['DetectionMethod'], PDO::PARAM_STR);
        $stmt->bindValue(':PortOfEntry', toIntOrNull($_POST['PortOfEntry'] ?? null), PDO::PARAM_INT);
        $stmt->bindParam(':GoodsDeclared', $_POST['GoodsDeclared'], PDO::PARAM_STR);
        $stmt->bindValue(':CountryOfOrigin', toIntOrNull($_POST['CountryOfOrigin'] ?? null), PDO::PARAM_INT);
        $stmt->bindParam(':CommodityType', $_POST['CommodityType'], PDO::PARAM_STR);
        $stmt->bindParam(':Description', $_POST['Description'], PDO::PARAM_STR);
        $stmt->bindValue(':Quantity', toFloatOrNull($_POST['Quantity'] ?? null), PDO::PARAM_STR);
        $stmt->bindParam(':Unit', $_POST['Unit'], PDO::PARAM_STR);
        $stmt->bindValue(':Volume', toFloatOrNull($_POST['Volume'] ?? null), PDO::PARAM_STR);
        $stmt->bindParam(':OfficerName', $_POST['OfficerName'], PDO::PARAM_STR);
        $stmt->bindParam(':ActionTaken', $_POST['ActionTaken'], PDO::PARAM_STR);
        $stmt->bindParam(':ActionOfficer', $_POST['ActionOfficer'], PDO::PARAM_STR);
        $stmt->bindParam(':DateActionCompleted', $_POST['DateActionCompleted'], PDO::PARAM_STR);
        $stmt->bindParam(':Comments', $_POST['Comments'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            $seizureID = $conn->lastInsertId();
            $response['success'] = true;
            $response['message'] = 'Passenger seizure record saved successfully!';
            $response['seizure_id'] = $seizureID;

            // Mark passenger_seizure step as complete
            try {
                // Use OfficerName or ActionOfficer from the form, or fall back to session username
                $performedBy = !empty($_POST['OfficerName']) ? $_POST['OfficerName'] :
                              (!empty($_POST['ActionOfficer']) ? $_POST['ActionOfficer'] : ($_SESSION['username'] ?? 'Bio Officer'));

                $statusData = array(
                    'VoyageID' => $_POST['VoyageID'],
                    'action' => 'complete_step',
                    'step' => 'passenger_seizure',
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
                    error_log('Failed to update voyage status for VoyageID: ' . $_POST['VoyageID']);
                    $response['warning'] = 'Passenger seizure saved but voyage status update failed.';
                }
            } catch (Exception $e) {
                error_log('Failed to update voyage status: ' . $e->getMessage());
                $response['warning'] = 'Passenger seizure saved but voyage status update failed: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Failed to save seizure record';
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
