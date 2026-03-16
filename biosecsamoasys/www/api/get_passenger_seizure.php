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
        if (isset($_GET['id'])) {
            $seizureId = $_GET['id'];

            // Get passenger seizure details
            $sql = "SELECT * FROM passenger_seizure WHERE PassengerSeizureID = :PassengerSeizureID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':PassengerSeizureID', $seizureId, PDO::PARAM_INT);
            $stmt->execute();

            $seizure = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($seizure) {
                $response['success'] = true;
                $response['data'] = $seizure;
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