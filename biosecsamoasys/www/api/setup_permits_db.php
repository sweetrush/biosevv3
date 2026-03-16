<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $pdo = getDBConnection();

    // Read the schema file
    $schema = file_get_contents('../../database/schema.sql');

    if ($schema === false) {
        throw new Exception('Could not read schema file');
    }

    // Split the schema into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    $executed = 0;
    $errors = [];

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty lines and comments
        }

        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Check if the error is due to table already existing
            if (strpos($e->getMessage(), 'already exists') !== false) {
                // This is expected for subsequent runs, skip
                continue;
            }
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        echo json_encode([
            'success' => true,
            'message' => "Database setup completed successfully. Executed $executed statements.",
            'executed' => $executed
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Some errors occurred',
            'details' => $errors
        ]);
    }

} catch (Exception $e) {
    error_log("Database setup error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
