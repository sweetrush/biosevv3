<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if (!requireAuth('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$templateName = trim($_POST['template_name'] ?? '');
$iraReference = trim($_POST['ira_reference'] ?? '');
$inUse = isset($_POST['in_use']) && $_POST['in_use'] === '1' ? 1 : 0;
$commodity1 = trim($_POST['commodity_1'] ?? '');
$commodity2 = trim($_POST['commodity_2'] ?? '');
$importRequirements = trim($_POST['import_requirements'] ?? '');
$modifiedBy = $_SESSION['username'] ?? 'System';

$errors = [];

if (empty($templateName)) {
    $errors['template_name'] = 'Template name is required';
}

if (empty($iraReference)) {
    $errors['ira_reference'] = 'IRA reference is required';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Please correct the errors below', 'errors' => $errors]);
    exit;
}

try {
    $conn = getDBConnection();

    if ($id > 0) {
        // Update existing template
        $stmt = $conn->prepare("
            UPDATE import_permit_templates
            SET template_name = ?, ira_reference = ?, in_use = ?,
                commodity_1 = ?, commodity_2 = ?, import_requirements = ?,
                modified_date = NOW(), modified_by = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $templateName,
            $iraReference,
            $inUse,
            $commodity1,
            $commodity2,
            $importRequirements,
            $modifiedBy,
            $id
        ]);

        $stmt = $conn->prepare("
            INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
            VALUES (NULL, 'UPDATE_PERMIT_TEMPLATE', ?, ?)
        ");
        $stmt->execute([
            "Updated permit template '{$templateName}' (ID: {$id})",
            $_SESSION['user_id'] ?? 'System'
        ]);

        echo json_encode(['success' => true, 'message' => 'Template updated successfully']);
    } else {
        // Create new template
        $stmt = $conn->prepare("
            INSERT INTO import_permit_templates
                (template_name, ira_reference, in_use, commodity_1, commodity_2,
                 import_requirements, modified_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $templateName,
            $iraReference,
            $inUse,
            $commodity1,
            $commodity2,
            $importRequirements,
            $modifiedBy
        ]);

        $newId = $conn->lastInsertId();

        $stmt = $conn->prepare("
            INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
            VALUES (NULL, 'CREATE_PERMIT_TEMPLATE', ?, ?)
        ");
        $stmt->execute([
            "Created permit template '{$templateName}' (ID: {$newId})",
            $_SESSION['user_id'] ?? 'System'
        ]);

        echo json_encode(['success' => true, 'message' => 'Template created successfully', 'id' => $newId]);
    }
} catch (Exception $e) {
    error_log("Submit permit template error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save template: ' . $e->getMessage()
    ]);
}
?>
