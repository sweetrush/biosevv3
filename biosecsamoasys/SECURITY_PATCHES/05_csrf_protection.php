<?php
/**
 * SECURITY PATCH 5: Add CSRF Protection to All State-Changing Endpoints
 * Priority: HIGH
 * Files to patch: All mutation endpoints
 */

echo "=== SECURITY PATCH 5: CSRF PROTECTION ===\n\n";

$endpoints_needing_csrf = [
    'www/api/submit_cargo_seizure.php',
    'www/api/submit_cargo_release.php',
    'www/api/edit_location.php',
    'www/api/delete_location.php',
    'www/api/voyage_crud.php',
    'www/api/update_cargo_seizure.php',
    'www/api/update_passenger_seizure.php',
];

echo "ENDPOINTS NEEDING CSRF PROTECTION:\n";
foreach ($endpoints_needing_csrf as $endpoint) {
    echo "  - $endpoint\n";
}

echo "\n\nEXAMPLE PATCH FOR submit_cargo_seizure.php:\n";
echo "--------\n";
echo "ADD this code at line 4 (after session_start()):\n\n";
?>
<?php echo htmlspecialchars('<?php
// submit_cargo_seizure.php - ADD THIS CODE

// Validate CSRF token
if (!isset($_POST[\'csrf_token\']) || !isset($_SESSION[\'csrf_token\'])) {
    http_response_code(403);
    echo json_encode([\'success\' => false, \'error\' => \'CSRF token missing\']);
    exit;
}

if (!hash_equals($_SESSION[\'csrf_token\'], $_POST[\'csrf_token\'])) {
    http_response_code(403);
    echo json_encode([\'success\' => false, \'error\' => \'Invalid CSRF token\']);
    exit;
}
?>')?>

<?php
echo "\n\nEXAMPLE PATCH FOR delete_location.php:\n";
echo "--------\n";
echo "ADD this code at line 4 (after session_start()):\n\n";
?>
<?php echo htmlspecialchars('<?php
// delete_location.php - ADD THIS CODE

// Validate CSRF token for DELETE requests
if ($_SERVER[\'REQUEST_METHOD\'] === \'DELETE\' || $_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
    // Get token from header or form data
    $csrfToken = $_SERVER[\'HTTP_X_CSRF_TOKEN\'] ?? $_POST[\'csrf_token\'] ?? null;

    if (!$csrfToken || !isset($_SESSION[\'csrf_token\']) || !hash_equals($_SESSION[\'csrf_token\'], $csrfToken)) {
        http_response_code(403);
        echo json_encode([\'success\' => false, \'error\' => \'Invalid CSRF token\']);
        exit;
    }
}
?>')?>

<?php
echo "\n\nFRONTEND JAVASCRIPT: Add to all forms sending POST/PUT/DELETE\n";
echo "--------\n";
?>
<?php echo htmlspecialchars('<script>
// Add this to your JavaScript before form submission
function addCSRFToken(form) {
    // Check if CSRF token exists in session
    const csrfToken = \'<?php echo $_SESSION[\'csrf_token\"] ?? ""; ?>\';\n\n    // Add token to form if not already present\n    let csrfInput = form.querySelector(\'input[name="csrf_token"]\');\n    if (!csrfInput) {\n        csrfInput = document.createElement(\'input\');\n        csrfInput.type = \'hidden\';\n        csrfInput.name = \'csrf_token\';\n        csrfInput.value = csrfToken;\n        form.appendChild(csrfInput);\n    } else {\n        csrfInput.value = csrfToken;\n    }\n}\n\n// Example usage with fetch API\nasync function submitForm(url, formData) {\n    const csrfToken = \'<?php echo $_SESSION[\'csrf_token\"]; ?>\';\n\n    const response = await fetch(url, {\n        method: \'POST\',\n        headers: {\n            \'Content-Type\': \'application/x-www-form-urlencoded\',\n            \'X-CSRF-Token\': csrfToken\n        },\n        body: new URLSearchParams(formData)\n    });\n\n    return response.json();\n}\n\n// Example usage with XMLHttpRequest\nfunction submitFormXHR(url, formData, callback) {\n    const csrfToken = \'<?php echo $_SESSION[\'csrf_token\"]; ?>\';\n    formData.append(\'csrf_token\', csrfToken);\n\n    const xhr = new XMLHttpRequest();\n    xhr.open(\'POST\', url);\n    xhr.onload = function() {\n        callback(JSON.parse(xhr.responseText));\n    };\n    xhr.send(formData);\n}\n</script>')?>

<?php
echo "\n\nAPPLY TO ALL FORMS:\n";
echo "--------\n";
echo "Each HTML form should include:\n\n";
echo '<input type="hidden" name="csrf_token" value="<?php echo $_SESSION[\'csrf_token\']; ?>">' . "\n\n";

echo "Or use the JavaScript function addCSRFToken(form) before submission.\n";

echo "\n\nNOTES:\n";
echo "- CSRF token is already generated in voyagement.php and user_management.php\n";
echo "- Ensure ALL state-changing operations validate CSRF token\n";
echo "- Use hash_equals() for comparison to prevent timing attacks\n";
echo "- Regenerate CSRF token periodically (e.g., every 30 minutes)\n";

?>