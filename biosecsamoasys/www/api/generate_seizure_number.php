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

    // Get the seizure type from query parameter
    $type = isset($_GET['type']) ? $_GET['type'] : 'passenger';

    if ($type === 'cargo') {
        // Generate cargo seizure number (CS-YYYYMM-NNNN)
        // Get the count of cargo seizures for the current year
        $year = date('Y');
        $sql = "SELECT COUNT(*) as count FROM cargo_seizure WHERE YEAR(created_at) = :year";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = ($result['count'] ?? 0) + 1;

        // Format: CS-YYYY-NNNN (e.g., CS-2025-0001)
        $seizureNo = 'CS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    } else {
        // Generate passenger seizure number (SZ-YYYYMM-NNNN)
        // Get the count of passenger seizures for the current year
        $year = date('Y');
        $sql = "SELECT COUNT(*) as count FROM passenger_seizure WHERE YEAR(created_at) = :year";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = ($result['count'] ?? 0) + 1;

        // Format: SZ-YYYY-NNNN (e.g., SZ-2025-0001)
        $seizureNo = 'SZ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    $response['success'] = true;
    $response['seizure_no'] = $seizureNo;
    echo json_encode($response);

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>
