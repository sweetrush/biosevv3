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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            $seizureId = $_GET['id'];

            // Get cargo seizure details
            $sql = "SELECT * FROM cargo_seizure WHERE CargoSeizureID = :CargoSeizureID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':CargoSeizureID', $seizureId, PDO::PARAM_INT);
            $stmt->execute();

            $seizure = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($seizure) {
                $response['success'] = true;
                $response['data'] = $seizure;
            } else {
                $response['message'] = 'Cargo seizure not found';
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