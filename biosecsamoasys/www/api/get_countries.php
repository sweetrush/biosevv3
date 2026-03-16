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

    // Get all countries ordered by name
    $sql = "SELECT CountryID, CountryName, ModifiedBy, ModifiedDate
            FROM countries
            WHERE CountryName != '' AND CountryName IS NOT NULL
            ORDER BY CountryName";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $countries;
    $response['count'] = count($countries);

    echo json_encode($response);

} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    echo json_encode($response);
}
?>