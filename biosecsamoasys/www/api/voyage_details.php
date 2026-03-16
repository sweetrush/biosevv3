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
        // Get specific voyage details
        if (isset($_GET['id'])) {
            $voyageId = $_GET['id'];

            // Get voyage details
            $sql = "SELECT vd.*, vs.current_step, vs.status,
                           vs.voyage_details_complete, vs.passenger_inspection_complete,
                           vs.passenger_seizure_complete, vs.cargo_seizure_complete, vs.cargo_release_complete
                    FROM voyage_details vd
                    LEFT JOIN voyage_status vs ON vd.VoyageID = vs.VoyageID
                    WHERE vd.VoyageID = :VoyageID";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->execute();
            $voyage = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($voyage) {
                // Get container counts
                $sql = "SELECT * FROM voyage_container_counts WHERE VoyageID = :VoyageID";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->execute();
                $containerCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $voyage['container_counts'] = $containerCounts;

                // Get passenger inspections
                $sql = "SELECT pi.*, ct.CommodityType
                        FROM passenger_inspection pi
                        LEFT JOIN commodity_types ct ON pi.CommodityTypeID = ct.CommodityTypeID
                        WHERE pi.VoyageID = :VoyageID";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->execute();
                $passengerInspections = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $voyage['passenger_inspections'] = $passengerInspections;

                // Get passenger seizures
                $sql = "SELECT * FROM passenger_seizure WHERE VoyageID = :VoyageID";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->execute();
                $passengerSeizures = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $voyage['passenger_seizures'] = $passengerSeizures;

                // Get cargo seizures
                $sql = "SELECT * FROM cargo_seizure WHERE VoyageID = :VoyageID";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->execute();
                $cargoSeizures = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $voyage['cargo_seizures'] = $cargoSeizures;

                // Get cargo releases
                $sql = "SELECT * FROM cargo_release WHERE VoyageID = :VoyageID";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->execute();
                $cargoReleases = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $voyage['cargo_releases'] = $cargoReleases;

                $response['success'] = true;
                $response['data'] = $voyage;
            } else {
                $response['message'] = 'Voyage not found';
            }
        } else {
            $response['message'] = 'Voyage ID is required';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Update voyage details
        parse_str(file_get_contents("php://input"), $_PUT);

        if (isset($_GET['id'])) {
            $voyageId = $_GET['id'];

            // Get current values for audit trail
            $sql = "SELECT * FROM voyage_details WHERE VoyageID = :VoyageID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->execute();
            $currentValues = $stmt->fetch(PDO::FETCH_ASSOC);

            $conn->beginTransaction();

            try {
                // Update voyage details
                $sql = "UPDATE voyage_details SET
                        VoyageNo = :VoyageNo,
                        PortOfLoadingID = :PortOfLoadingID,
                        LastPortID = :LastPortID,
                        PortOfArrivalID = :PortOfArrivalID,
                        LocationID = :LocationID,
                        ArrivalDate = :ArrivalDate,
                        Pax = :Pax,
                        Crew = :Crew,
                        CrewSearched = :CrewSearched,
                        TotalDischarged = :TotalDischarged,
                        VesselID = :VesselID,
                        RoomSealed = :RoomSealed,
                        AirOfSea = :AirOfSea,
                        NoXRated = :NoXRated,
                        BondedAnimals = :BondedAnimals,
                        BondedAnimalsDescription = :BondedAnimalsDescription,
                        AnimalHealthCertificate = :AnimalHealthCertificate,
                        NumberContainers = :NumberContainers,
                        NumberCargosDischarged = :NumberCargosDischarged,
                        PortAuthority = :PortAuthority,
                        ModifiedBy = :ModifiedBy,
                        ModifiedDate = :ModifiedDate
                        WHERE VoyageID = :VoyageID";

                $stmt = $conn->prepare($sql);

                // Bind parameters
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->bindParam(':VoyageNo', $_PUT['VoyageNo']);
                $stmt->bindParam(':PortOfLoadingID', $_PUT['PortOfLoadingID']);
                $stmt->bindParam(':LastPortID', $_PUT['LastPortID']);
                $stmt->bindParam(':PortOfArrivalID', $_PUT['PortOfArrivalID']);
                $stmt->bindParam(':LocationID', $_PUT['LocationID']);
                $stmt->bindParam(':ArrivalDate', $_PUT['ArrivalDate']);
                $stmt->bindParam(':Pax', $_PUT['Pax']);
                $stmt->bindParam(':Crew', $_PUT['Crew']);
                $stmt->bindParam(':CrewSearched', $_PUT['CrewSearched']);
                $stmt->bindParam(':TotalDischarged', $_PUT['TotalDischarged']);
                $stmt->bindParam(':VesselID', $_PUT['VesselID']);
                $stmt->bindParam(':RoomSealed', $_PUT['RoomSealed']);
                $stmt->bindParam(':AirOfSea', $_PUT['AirOfSea']);
                $stmt->bindParam(':NoXRated', $_PUT['NoXRated']);
                $stmt->bindParam(':BondedAnimals', $_PUT['BondedAnimals']);
                $stmt->bindParam(':BondedAnimalsDescription', $_PUT['BondedAnimalsDescription']);
                $stmt->bindParam(':AnimalHealthCertificate', $_PUT['AnimalHealthCertificate']);
                $stmt->bindParam(':NumberContainers', $_PUT['NumberContainers']);
                $stmt->bindParam(':NumberCargosDischarged', $_PUT['NumberCargosDischarged']);
                $stmt->bindParam(':PortAuthority', $_PUT['PortAuthority']);
                $stmt->bindParam(':ModifiedBy', $_PUT['ModifiedBy']);
                $stmt->bindParam(':ModifiedDate', $_PUT['ModifiedDate']);

                $stmt->execute();

                // Create audit trail entry
                $sql = "INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by, previous_values, new_values)
                        VALUES (:VoyageID, 'UPDATE', 'Voyage details updated', :ModifiedBy, :PreviousValues, :NewValues)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':VoyageID', $voyageId);
                $stmt->bindParam(':ModifiedBy', $_PUT['ModifiedBy']);
                $stmt->bindParam(':PreviousValues', json_encode($currentValues));
                $stmt->bindParam(':NewValues', json_encode($_PUT));
                $stmt->execute();

                $conn->commit();

                $response['success'] = true;
                $response['message'] = 'Voyage updated successfully!';

            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
        } else {
            $response['message'] = 'Voyage ID is required';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Delete voyage
        if (isset($_GET['id'])) {
            $voyageId = $_GET['id'];

            // Get current values for audit trail
            $sql = "SELECT * FROM voyage_details WHERE VoyageID = :VoyageID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->execute();
            $currentValues = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($currentValues) {
                $conn->beginTransaction();

                try {
                    // Create audit trail entry
                    $sql = "INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by, previous_values)
                            VALUES (:VoyageID, 'DELETE', 'Voyage deleted', 'System', :PreviousValues)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':VoyageID', $voyageId);
                    $stmt->bindParam(':PreviousValues', json_encode($currentValues));
                    $stmt->execute();

                    // Delete voyage (cascade will handle related records)
                    $sql = "DELETE FROM voyage_details WHERE VoyageID = :VoyageID";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':VoyageID', $voyageId);
                    $stmt->execute();

                    $conn->commit();

                    $response['success'] = true;
                    $response['message'] = 'Voyage deleted successfully!';
                } catch (Exception $e) {
                    $conn->rollBack();
                    throw $e;
                }
            } else {
                $response['message'] = 'Voyage not found';
            }
        } else {
            $response['message'] = 'Voyage ID is required';
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