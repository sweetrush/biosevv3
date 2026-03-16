<?php
/**
 * SECURITY PATCH 2: Apply Authentication to All Vulnerable API Endpoints
 * This file demonstrates how to patch each endpoint
 * Priority: CRITICAL
 * Files to patch: All API endpoints lacking authentication
 */

echo "=== SECURITY PATCH 2: APPLYING AUTHENTICATION ===\n\n";

$files_to_patch = [
    // READ-ONLY endpoints (require authentication)
    'www/api/get_voyages.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_ports.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_locations.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_vessels.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/voyage_crud.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/voyage_status.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_recent_cargo_seizures.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_cargo_seizure.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_officers.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_commodities.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_recent_seizures.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_recent_inspections.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_recent_releases.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_passenger_seizure.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_container_types.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_countries.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    'www/api/get_permits.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n"
    ],

    // MUTATION endpoints (require authentication + CSRF)
    'www/api/edit_location.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n\n// Validate CSRF token for mutations\nif (!isset(\$_POST['csrf_token']) && \$_SERVER['REQUEST_METHOD'] === 'POST') {\n    http_response_code(403);\n    echo json_encode(['success' => false, 'error' => 'CSRF token missing']);\n    exit;\n}\n\nif (!empty(\$_POST['csrf_token']) && (!isset(\$_SESSION['csrf_token']) || !hash_equals(\$_SESSION['csrf_token'], \$_POST['csrf_token']))) {\n    http_response_code(403);\n    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);\n    exit;\n}\n"
    ],

    'www/api/delete_location.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n\n// Validate CSRF token for mutations\nif (!empty(\$_POST['csrf_token']) || \$_SERVER['REQUEST_METHOD'] === 'DELETE') {\n    if (empty(\$_POST['csrf_token']) || !isset(\$_SESSION['csrf_token']) || !hash_equals(\$_SESSION['csrf_token'], \$_POST['csrf_token'])) {\n        http_response_code(403);\n        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);\n        exit;\n    }\n}\n"
    ],

    'www/api/update_passenger_seizure.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n\n// Validate CSRF token\nif (empty(\$_POST['csrf_token']) || !isset(\$_SESSION['csrf_token']) || !hash_equals(\$_SESSION['csrf_token'], \$_POST['csrf_token'])) {\n    http_response_code(403);\n    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);\n    exit;\n}\n"
    ],

    'www/api/update_cargo_seizure.php' => [
        'insert_after_line' => 1,
        'code' => "session_start();\nrequire_once 'config.php';\n\nif (!isLoggedIn()) {\n    http_response_code(401);\n    echo json_encode(['success' => false, 'error' => 'Unauthorized']);\n    exit;\n}\n\n// Validate CSRF token\nif (empty(\$_POST['csrf_token']) || !isset(\$_SESSION['csrf_token']) || !hash_equals(\$_SESSION['csrf_token'], \$_POST['csrf_token'])) {\n    http_response_code(403);\n    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);\n    exit;\n}\n"
    ],
];

echo "Files to patch:\n";
foreach ($files_to_patch as $file => $config) {
    echo "  - $file\n";
}

echo "\nExample patch for get_voyages.php:\n";
echo "--- a/www/api/get_voyages.php\n";
echo "+++ b/www/api/get_voyages.php\n";
echo "@@ -1,3 +1,9 @@\n";
echo "+session_start();\n";
echo "+require_once 'config.php';\n";
echo "+\n";
echo "+if (!isLoggedIn()) {\n";
echo "+    http_response_code(401);\n";
echo '+    echo json_encode([\'success\' => false, \'error\' => \'Unauthorized\']);' . "\n";
echo "+    exit;\n";
echo "+}\n";
echo " header('Content-Type: application/json');\n";
echo "\n";

echo "\nApply these patches manually to each file or use the apply_patches.php script.\n";
?>