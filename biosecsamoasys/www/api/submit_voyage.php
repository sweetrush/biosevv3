<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session for CSRF validation
session_start();

// Database configuration
$db_host = 'mysql';
$db_name = 'biosecurity_db';
$db_user = 'biosec_user';
$db_pass = 'biosec_pass';

// Response array
$response = array('success' => false, 'message' => '');

try {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        throw new Exception('CSRF token missing.');
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token.');
    }

    // Create database connection
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Only POST is allowed.');
    }

    // Validate required fields
    $required_fields = ['VoyageNo', 'PortOfArrivalID', 'ArrivalDate', 'VesselID'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Required field '$field' is missing.");
        }
    }

    // Prepare SQL statement (VoyageID is auto-increment, so not included)
    $sql = "INSERT INTO voyage_details (
        VoyageNo,
        PortOfLoadingID,
        LastPortID,
        PortOfArrivalID,
        LocationID,
        ArrivalDate,
        Pax,
        Crew,
        CrewSearched,
        TotalDischarged,
        VesselID,
        RoomSealed,
        AirOfSea,
        NoXRated,
        BondedAnimals,
        BondedAnimalsDescription,
        AnimalHealthCertificate,
        NumberContainers,
        NumberCargosDischarged,
        PortAuthority,
        ModifiedBy,
        ModifiedDate
    ) VALUES (
        :VoyageNo,
        :PortOfLoadingID,
        :LastPortID,
        :PortOfArrivalID,
        :LocationID,
        :ArrivalDate,
        :Pax,
        :Crew,
        :CrewSearched,
        :TotalDischarged,
        :VesselID,
        :RoomSealed,
        :AirOfSea,
        :NoXRated,
        :BondedAnimals,
        :BondedAnimalsDescription,
        :AnimalHealthCertificate,
        :NumberContainers,
        :NumberCargosDischarged,
        :PortAuthority,
        :ModifiedBy,
        :ModifiedDate
    )";

    $stmt = $conn->prepare($sql);

    // Bind parameters (all as strings as per requirements)
    $stmt->bindParam(':VoyageNo', $_POST['VoyageNo'], PDO::PARAM_STR);
    $stmt->bindParam(':PortOfLoadingID', $_POST['PortOfLoadingID'], PDO::PARAM_STR);
    $stmt->bindParam(':LastPortID', $_POST['LastPortID'], PDO::PARAM_STR);
    $stmt->bindParam(':PortOfArrivalID', $_POST['PortOfArrivalID'], PDO::PARAM_STR);
    $stmt->bindParam(':LocationID', $_POST['LocationID'], PDO::PARAM_STR);
    $stmt->bindParam(':ArrivalDate', $_POST['ArrivalDate'], PDO::PARAM_STR);
    $stmt->bindParam(':Pax', $_POST['Pax'], PDO::PARAM_STR);
    $stmt->bindParam(':Crew', $_POST['Crew'], PDO::PARAM_STR);
    $stmt->bindParam(':CrewSearched', $_POST['CrewSearched'], PDO::PARAM_STR);
    $stmt->bindParam(':TotalDischarged', $_POST['TotalDischarged'], PDO::PARAM_STR);
    $stmt->bindParam(':VesselID', $_POST['VesselID'], PDO::PARAM_STR);
    $stmt->bindParam(':RoomSealed', $_POST['RoomSealed'], PDO::PARAM_STR);
    $stmt->bindParam(':AirOfSea', $_POST['AirOfSea'], PDO::PARAM_STR);
    $stmt->bindParam(':NoXRated', $_POST['NoXRated'], PDO::PARAM_STR);
    $stmt->bindParam(':BondedAnimals', $_POST['BondedAnimals'], PDO::PARAM_STR);
    $stmt->bindParam(':BondedAnimalsDescription', $_POST['BondedAnimalsDescription'], PDO::PARAM_STR);
    $stmt->bindParam(':AnimalHealthCertificate', $_POST['AnimalHealthCertificate'], PDO::PARAM_STR);
    $stmt->bindParam(':NumberContainers', $_POST['NumberContainers'], PDO::PARAM_STR);
    $stmt->bindParam(':NumberCargosDischarged', $_POST['NumberCargosDischarged'], PDO::PARAM_STR);
    $stmt->bindParam(':PortAuthority', $_POST['PortAuthority'], PDO::PARAM_STR);
    $stmt->bindParam(':ModifiedBy', $_POST['ModifiedBy'], PDO::PARAM_STR);
    $stmt->bindParam(':ModifiedDate', $_POST['ModifiedDate'], PDO::PARAM_STR);

    // Execute the statement
    if ($stmt->execute()) {
        $voyageID = $conn->lastInsertId();

        // Insert container type counts if provided
        $containerInserted = 0;
        foreach ($_POST as $key => $value) {
            // Check if the key starts with 'container_' and has a value
            if (strpos($key, 'container_') === 0 && !empty($value) && is_numeric($value) && $value > 0) {
                $containerCode = str_replace('container_', '', $key);

                // Insert into voyage_container_counts
                $containerSql = "INSERT INTO voyage_container_counts (VoyageID, container_type_code, count)
                                 VALUES (:VoyageID, :container_type_code, :count)";
                $containerStmt = $conn->prepare($containerSql);
                $containerStmt->bindParam(':VoyageID', $voyageID, PDO::PARAM_INT);
                $containerStmt->bindParam(':container_type_code', $containerCode, PDO::PARAM_STR);
                $containerStmt->bindParam(':count', $value, PDO::PARAM_STR);

                if ($containerStmt->execute()) {
                    $containerInserted++;
                }
            }
        }

        $response['success'] = true;
        $response['message'] = 'Voyage details submitted successfully!';
        $response['voyage_id'] = $voyageID;
        $response['containers_inserted'] = $containerInserted;

        // Mark voyage_details step as complete
        try {
            $statusData = array(
                'VoyageID' => $voyageID,
                'action' => 'complete_step',
                'step' => 'voyage_details',
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
            $statusResult = file_get_contents('http://localhost/api/voyage_status.php', false, $context);
            if ($statusResult === false) {
                error_log('Failed to update voyage status for VoyageID: ' . $voyageID);
                $response['warning'] = 'Voyage details saved but voyage status update failed.';
            }
        } catch (Exception $e) {
            error_log('Failed to update voyage status: ' . $e->getMessage());
            $response['warning'] = 'Voyage details saved but voyage status update failed: ' . $e->getMessage();
        }
    } else {
        throw new Exception('Failed to insert voyage details.');
    }

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Close connection
$conn = null;

// Return JSON response
echo json_encode($response);
?>
