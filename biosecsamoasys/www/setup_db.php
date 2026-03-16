<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Allow setup for everyone (since we need to create admin first)
require_once 'api/config.php';

try {
    // Test database connection
    $pdo = getDBConnection();

    // Read and execute the SQL files
    $sql_files = [
        'sql/init.sql',
        'sql/add_voyage_management_tables.sql'
    ];

    $executed_files = 0;
    $errors = [];

    foreach ($sql_files as $sql_file) {
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);

            if ($sql !== false) {
                // Split into individual statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));

                foreach ($statements as $statement) {
                    if (empty($statement) || strpos($statement, '--') === 0) {
                        continue; // Skip empty lines and comments
                    }

                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Check if the error is due to table already existing or similar
                        if (strpos($e->getMessage(), 'already exists') !== false ||
                            strpos($e->getMessage(), 'Duplicate entry') !== false) {
                            // This is expected, continue
                            continue;
                        }
                        $errors[] = "File: $sql_file, Error: " . $e->getMessage();
                    }
                }
                $executed_files++;
            }
        } else {
            $errors[] = "SQL file not found: $sql_file";
        }
    }

    if (empty($errors)) {
        echo json_encode([
            'success' => true,
            'message' => "Database setup completed successfully. Executed $executed_files files.",
            'executed_files' => $executed_files
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Some errors occurred during setup',
            'details' => $errors
        ]);
    }

} catch (Exception $e) {
    error_log("Database setup error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>