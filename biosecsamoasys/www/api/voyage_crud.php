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

    $response = array('success' => false, 'message' => '', 'data' => null);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get all voyages with status information
        $sql = "SELECT vd.*, vs.current_step, vs.status,
                       vs.voyage_details_complete, vs.passenger_inspection_complete,
                       vs.passenger_seizure_complete, vs.cargo_seizure_complete, vs.cargo_release_complete
                FROM voyage_details vd
                LEFT JOIN voyage_status vs ON vd.VoyageID = vs.VoyageID
                ORDER BY vd.created_at DESC";

        $stmt = $conn->query($sql);
        $voyages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = $voyages;

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create new voyage with status tracking
        $conn->beginTransaction();

        try {
            // Insert voyage details
            $sql = "INSERT INTO voyage_details
                    (VoyageNo, PortOfLoadingID, LastPortID, PortOfArrivalID, LocationID, ArrivalDate,
                     Pax, Crew, CrewSearched, TotalDischarged, VesselID, RoomSealed, AirOfSea,
                     NoXRated, BondedAnimals, BondedAnimalsDescription, AnimalHealthCertificate,
                     NumberContainers, NumberCargosDischarged, PortAuthority, ModifiedBy, ModifiedDate)
                    VALUES
                    (:VoyageNo, :PortOfLoadingID, :LastPortID, :PortOfArrivalID, :LocationID, :ArrivalDate,
                     :Pax, :Crew, :CrewSearched, :TotalDischarged, :VesselID, :RoomSealed, :AirOfSea,
                     :NoXRated, :BondedAnimals, :BondedAnimalsDescription, :AnimalHealthCertificate,
                     :NumberContainers, :NumberCargosDischarged, :PortAuthority, :ModifiedBy, :ModifiedDate)";

            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':VoyageNo', $_POST['VoyageNo']);
            $stmt->bindParam(':PortOfLoadingID', $_POST['PortOfLoadingID']);
            $stmt->bindParam(':LastPortID', $_POST['LastPortID']);
            $stmt->bindParam(':PortOfArrivalID', $_POST['PortOfArrivalID']);
            $stmt->bindParam(':LocationID', $_POST['LocationID']);
            $stmt->bindParam(':ArrivalDate', $_POST['ArrivalDate']);
            $stmt->bindParam(':Pax', $_POST['Pax']);
            $stmt->bindParam(':Crew', $_POST['Crew']);
            $stmt->bindParam(':CrewSearched', $_POST['CrewSearched']);
            $stmt->bindParam(':TotalDischarged', $_POST['TotalDischarged']);
            $stmt->bindParam(':VesselID', $_POST['VesselID']);
            $stmt->bindParam(':RoomSealed', $_POST['RoomSealed']);
            $stmt->bindParam(':AirOfSea', $_POST['AirOfSea']);
            $stmt->bindParam(':NoXRated', $_POST['NoXRated']);
            $stmt->bindParam(':BondedAnimals', $_POST['BondedAnimals']);
            $stmt->bindParam(':BondedAnimalsDescription', $_POST['BondedAnimalsDescription']);
            $stmt->bindParam(':AnimalHealthCertificate', $_POST['AnimalHealthCertificate']);
            $stmt->bindParam(':NumberContainers', $_POST['NumberContainers']);
            $stmt->bindParam(':NumberCargosDischarged', $_POST['NumberCargosDischarged']);
            $stmt->bindParam(':PortAuthority', $_POST['PortAuthority']);
            $stmt->bindParam(':ModifiedBy', $_POST['ModifiedBy']);
            $stmt->bindParam(':ModifiedDate', $_POST['ModifiedDate']);

            $stmt->execute();
            $voyageId = $conn->lastInsertId();

            // Create initial status record
            $sql = "INSERT INTO voyage_status (VoyageID, status, current_step)
                    VALUES (:VoyageID, 'draft', 'voyage_details')";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->execute();

            // Create audit trail entry
            $sql = "INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
                    VALUES (:VoyageID, 'CREATE', 'New voyage created', :ModifiedBy)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->bindParam(':ModifiedBy', $_POST['ModifiedBy']);
            $stmt->execute();

            $conn->commit();

            $response['success'] = true;
            $response['message'] = 'Voyage created successfully!';
            $response['voyage_id'] = $voyageId;

        } catch (Exception $e) {
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