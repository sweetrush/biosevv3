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

    if ($method === 'GET') {
        // Get single location by ID
        if (isset($_GET['id'])) {
            $locationId = $_GET['id'];

            $sql = "SELECT location_id, location_name, region, location_type, is_active
                    FROM locations
                    WHERE location_id = :location_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':location_id', $locationId);
            $stmt->execute();

            $location = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($location) {
                $response['success'] = true;
                $response['data'] = $location;
            } else {
                $response['message'] = 'Location not found';
            }
        } else {
            $response['message'] = 'Location ID is required';
        }
    } elseif ($method === 'POST') {
        // Update location
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['location_id']) && isset($input['location_name']) &&
            isset($input['region']) && isset($input['location_type'])) {

            $locationId = $input['location_id'];
            $locationName = $input['location_name'];
            $region = $input['region'];
            $locationType = $input['location_type'];
            $isActive = isset($input['is_active']) ? $input['is_active'] : true;

            $sql = "UPDATE locations
                    SET location_name = :location_name,
                        region = :region,
                        location_type = :location_type,
                        is_active = :is_active
                    WHERE location_id = :location_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':location_id', $locationId);
            $stmt->bindParam(':location_name', $locationName);
            $stmt->bindParam(':region', $region);
            $stmt->bindParam(':location_type', $locationType);
            $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Location updated successfully';
            } else {
                $response['message'] = 'Failed to update location';
            }
        } else {
            $response['message'] = 'Missing required fields';
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