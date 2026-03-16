<?php
header('Content-Type: application/json');

// Start session for CSRF validation
session_start();

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
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $response['message'] = 'CSRF token missing or invalid. Please refresh the page and try again.';
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
        $sql = "INSERT INTO cargo_seizure
                (VoyageID, ContainerCargoRefNo, Importer, CargoDescription, DepotName, DetectionMethod,
                 PortOfEntry, SeizureDate, SeizureNo, CountryOfOrigin, CommodityType, Description,
                 Quantity, Unit, VolumeKg, SeizingOfficerName, ActionTaken, ActionOfficer,
                 DateActionCompleted, Comments)
                VALUES
                (:VoyageID, :ContainerCargoRefNo, :Importer, :CargoDescription, :DepotName, :DetectionMethod,
                 :PortOfEntry, :SeizureDate, :SeizureNo, :CountryOfOrigin, :CommodityType, :Description,
                 :Quantity, :Unit, :VolumeKg, :SeizingOfficerName, :ActionTaken, :ActionOfficer,
                 :DateActionCompleted, :Comments)";

        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':VoyageID', $_POST['VoyageID'], PDO::PARAM_INT);
        $stmt->bindParam(':ContainerCargoRefNo', $_POST['ContainerCargoRefNo'], PDO::PARAM_STR);
        $stmt->bindParam(':Importer', $_POST['Importer'], PDO::PARAM_STR);
        $stmt->bindParam(':CargoDescription', $_POST['CargoDescription'], PDO::PARAM_STR);
        $stmt->bindParam(':DepotName', $_POST['DepotName'], PDO::PARAM_STR);
        $stmt->bindParam(':DetectionMethod', $_POST['DetectionMethod'], PDO::PARAM_STR);
        $stmt->bindParam(':PortOfEntry', $_POST['PortOfEntry'], PDO::PARAM_STR);
        $stmt->bindParam(':SeizureDate', $_POST['SeizureDate'], PDO::PARAM_STR);
        $stmt->bindParam(':SeizureNo', $_POST['SeizureNo'], PDO::PARAM_STR);
        $stmt->bindParam(':CountryOfOrigin', $_POST['CountryOfOrigin'], PDO::PARAM_STR);
        $stmt->bindParam(':CommodityType', $_POST['CommodityType'], PDO::PARAM_STR);
        $stmt->bindParam(':Description', $_POST['Description'], PDO::PARAM_STR);
        $stmt->bindParam(':Quantity', $_POST['Quantity'], PDO::PARAM_STR);
        $stmt->bindParam(':Unit', $_POST['Unit'], PDO::PARAM_STR);
        $stmt->bindParam(':VolumeKg', $_POST['VolumeKg'], PDO::PARAM_STR);
        $stmt->bindParam(':SeizingOfficerName', $_POST['SeizingOfficerName'], PDO::PARAM_STR);
        $stmt->bindParam(':ActionTaken', $_POST['ActionTaken'], PDO::PARAM_STR);
        $stmt->bindParam(':ActionOfficer', $_POST['ActionOfficer'], PDO::PARAM_STR);
        $stmt->bindParam(':DateActionCompleted', $_POST['DateActionCompleted'], PDO::PARAM_STR);
        $stmt->bindParam(':Comments', $_POST['Comments'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            $seizureID = $conn->lastInsertId();
            $response['success'] = true;
            $response['message'] = 'Cargo seizure record saved successfully!';
            $response['seizure_id'] = $seizureID;

            // Mark cargo_seizure step as complete
            try {
                // Use SeizingOfficerName or ActionOfficer from the form, or fall back to a default
                $performedBy = !empty($_POST['SeizingOfficerName']) ? $_POST['SeizingOfficerName'] :
                              (!empty($_POST['ActionOfficer']) ? $_POST['ActionOfficer'] : 'Bio Officer');

                $statusData = array(
                    'VoyageID' => $_POST['VoyageID'],
                    'action' => 'complete_step',
                    'step' => 'cargo_seizure',
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
        } else {
            $response['message'] = 'Failed to save cargo seizure record';
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
