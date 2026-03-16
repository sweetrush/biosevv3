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

    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (isset($_GET['id'])) {
            $seizureId = $_GET['id'];

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            // Get current values for audit trail
            $sql = "SELECT * FROM passenger_seizure WHERE PassengerSeizureID = :PassengerSeizureID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':PassengerSeizureID', $seizureId, PDO::PARAM_INT);
            $stmt->execute();
            $currentValues = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($currentValues) {
                $conn->beginTransaction();

                try {
                    // Update passenger seizure
                    $sql = "UPDATE passenger_seizure SET
                            SeizureDate = :SeizureDate,
                            SeizureNo = :SeizureNo,
                            Importer = :Importer,
                            DetectionMethod = :DetectionMethod,
                            PortOfEntry = :PortOfEntry,
                            GoodsDeclared = :GoodsDeclared,
                            CountryOfOrigin = :CountryOfOrigin,
                            CommodityType = :CommodityType,
                            Description = :Description,
                            Quantity = :Quantity,
                            Unit = :Unit,
                            Volume = :Volume,
                            OfficerName = :OfficerName,
                            ActionTaken = :ActionTaken,
                            ActionOfficer = :ActionOfficer,
                            DateActionCompleted = :DateActionCompleted,
                            Comments = :Comments
                            WHERE PassengerSeizureID = :PassengerSeizureID";

                    $stmt = $conn->prepare($sql);

                    // Bind parameters (handle missing fields)
                    $stmt->bindParam(':PassengerSeizureID', $seizureId, PDO::PARAM_INT);
                    $stmt->bindParam(':SeizureDate', $input['SeizureDate']);
                    $stmt->bindParam(':SeizureNo', $input['SeizureNo']);
                    $stmt->bindParam(':Importer', $input['Importer']);
                    $stmt->bindParam(':DetectionMethod', $input['DetectionMethod'] ?? null);
                    $stmt->bindParam(':PortOfEntry', $input['PortOfEntry'] ?? null);
                    $stmt->bindParam(':GoodsDeclared', $input['GoodsDeclared'] ?? null);
                    $stmt->bindParam(':CountryOfOrigin', $input['CountryOfOrigin'] ?? null);
                    $stmt->bindParam(':CommodityType', $input['CommodityType']);
                    $stmt->bindParam(':Description', $input['Description'] ?? null);
                    $stmt->bindParam(':Quantity', $input['Quantity'] ?? null);
                    $stmt->bindParam(':Unit', $input['Unit'] ?? null);
                    $stmt->bindParam(':Volume', $input['Volume'] ?? null);
                    $stmt->bindParam(':OfficerName', $input['OfficerName'] ?? null);
                    $stmt->bindParam(':ActionTaken', $input['ActionTaken'] ?? null);
                    $stmt->bindParam(':ActionOfficer', $input['ActionOfficer'] ?? null);
                    $stmt->bindParam(':DateActionCompleted', $input['DateActionCompleted'] ?? null);
                    $stmt->bindParam(':Comments', $input['Comments'] ?? null);

                    $stmt->execute();

                    $conn->commit();

                    $response['success'] = true;
                    $response['message'] = 'Passenger seizure updated successfully!';

                } catch (Exception $e) {
                    $conn->rollBack();
                    throw $e;
                }
            } else {
                $response['message'] = 'Passenger seizure not found';
            }
        } else {
            $response['message'] = 'Seizure ID is required';
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