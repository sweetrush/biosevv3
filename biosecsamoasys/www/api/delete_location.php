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

    $response = array('success' => false);

    // Get HTTP method
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'DELETE' || $method === 'POST') {
        // Get location ID from query parameters or request body
        $locationId = null;

        if ($method === 'DELETE') {
            $locationId = $_GET['id'] ?? null;
        } else {
            // For POST method, check both form data and JSON
            if (isset($_POST['id'])) {
                $locationId = $_POST['id'];
            } else {
                $input = json_decode(file_get_contents('php://input'), true);
                $locationId = $input['id'] ?? null;
            }
        }

        if ($locationId) {
            // First check if location exists
            $checkSql = "SELECT location_id, location_name FROM locations WHERE location_id = :location_id";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(':location_id', $locationId);
            $checkStmt->execute();
            $location = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$location) {
                $response['message'] = 'Location not found';
            } else {
                // Check if location is referenced in other tables
                $referenceChecks = [
                    'voyage_details' => 'PortOfLoadingID',
                    'voyage_details' => 'LastPortID',
                    'voyage_details' => 'PortOfArrivalID',
                    'voyage_details' => 'LocationID'
                ];

                $hasReferences = false;
                $references = [];

                foreach ($referenceChecks as $table => $column) {
                    $refSql = "SELECT COUNT(*) as count FROM $table WHERE $column = :location_id";
                    $refStmt = $conn->prepare($refSql);
                    $refStmt->bindParam(':location_id', $locationId);
                    $refStmt->execute();
                    $count = $refStmt->fetch(PDO::FETCH_ASSOC)['count'];

                    if ($count > 0) {
                        $hasReferences = true;
                        $references[] = "$table ($column): $count record(s)";
                    }
                }

                if ($hasReferences) {
                    $response['message'] = 'Cannot delete location. It is referenced by other records: ' . implode(', ', $references);
                    $response['has_references'] = true;
                    $response['references'] = $references;
                } else {
                    // Delete the location
                    $deleteSql = "DELETE FROM locations WHERE location_id = :location_id";
                    $deleteStmt = $conn->prepare($deleteSql);
                    $deleteStmt->bindParam(':location_id', $locationId);

                    if ($deleteStmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = "Location '{$location['location_name']}' ({$locationId}) has been successfully deleted.";
                        $response['deleted_location'] = $location;
                    } else {
                        $response['message'] = 'Failed to delete location';
                    }
                }
            }
        } else {
            $response['message'] = 'Location ID is required';
        }
    } else {
        $response['message'] = 'Invalid request method. Use DELETE or POST.';
    }

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>