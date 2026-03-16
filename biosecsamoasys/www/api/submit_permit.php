<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Check authentication
if (!requireAuth()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access - Please log in']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'CSRF token missing']);
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Check if it's an update or create
    $permitId = isset($_POST['permitId']) ? intval($_POST['permitId']) : 0;
    $isUpdate = $permitId > 0;

    // Prepare the data
    $permitNumber = trim($_POST['permitNumber'] ?? '');
    $iraReference = trim($_POST['iraReference'] ?? '');
    $issuingOfficer = trim($_POST['issuingOfficer'] ?? '');
    $issueDate = trim($_POST['issueDate'] ?? '');
    $permitValidity = trim($_POST['permitValidity'] ?? '');
    $portOfEntry = trim($_POST['portOfEntry'] ?? '');
    $importer = trim($_POST['importer'] ?? '');
    $importerAddress = trim($_POST['importerAddress'] ?? '');
    $exporter = trim($_POST['exporter'] ?? '');
    $exporterAddress = trim($_POST['exporterAddress'] ?? '');
    $authorizedOfficer = trim($_POST['authorizedOfficer'] ?? '');
    $endUse = trim($_POST['endUse'] ?? '');
    $meansOfConveyance = trim($_POST['meansOfConveyance'] ?? '');
    $template = trim($_POST['template'] ?? '');
    $commodity = trim($_POST['commodity'] ?? '');
    $importRequirements = trim($_POST['importRequirements'] ?? '');

    // Validate required fields
    $requiredFields = [
        'permitNumber' => $permitNumber,
        'iraReference' => $iraReference,
        'issuingOfficer' => $issuingOfficer,
        'issueDate' => $issueDate,
        'permitValidity' => $permitValidity,
        'portOfEntry' => $portOfEntry,
        'importer' => $importer,
        'importerAddress' => $importerAddress,
        'exporter' => $exporter,
        'exporterAddress' => $exporterAddress,
        'authorizedOfficer' => $authorizedOfficer,
        'endUse' => $endUse,
        'meansOfConveyance' => $meansOfConveyance,
        'template' => $template,
        'commodity' => $commodity
    ];

    foreach ($requiredFields as $field => $value) {
        if (empty($value)) {
            echo json_encode(['success' => false, 'error' => "$field is required"]);
            exit;
        }
    }

    // Get officer ID from officer name
    $officerStmt = $pdo->prepare("SELECT officer_id FROM officers WHERE officer_name = ? AND is_active = 1");
    $officerStmt->execute([$issuingOfficer]);
    $officer = $officerStmt->fetch();

    if (!$officer) {
        echo json_encode(['success' => false, 'error' => 'Invalid issuing officer']);
        exit;
    }

    $issuingOfficerId = $officer['officer_id'];

    if ($isUpdate) {
        // Update existing permit
        $stmt = $pdo->prepare("
            UPDATE import_permits SET
                ira_reference = ?,
                issuing_officer_id = ?,
                issue_date = ?,
                permit_validity = ?,
                port_of_entry = ?,
                importer = ?,
                importer_address = ?,
                exporter = ?,
                exporter_address = ?,
                authorized_officer = ?,
                end_use = ?,
                means_of_conveyance = ?,
                template_type = ?,
                commodity = ?,
                import_requirements = ?,
                modified_date = CURRENT_TIMESTAMP
            WHERE permit_id = ?
        ");

        $stmt->execute([
            $iraReference,
            $issuingOfficerId,
            $issueDate,
            $permitValidity,
            $portOfEntry,
            $importer,
            $importerAddress,
            $exporter,
            $exporterAddress,
            $authorizedOfficer,
            $endUse,
            $meansOfConveyance,
            $template,
            $commodity,
            $importRequirements,
            $permitId
        ]);

        echo json_encode(['success' => true, 'message' => 'Permit updated successfully', 'permitId' => $permitId]);
    } else {
        // Check if permit number already exists
        $checkStmt = $pdo->prepare("SELECT permit_id FROM import_permits WHERE permit_number = ?");
        $checkStmt->execute([$permitNumber]);
        if ($checkStmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Permit number already exists']);
            exit;
        }

        // Create new permit
        $stmt = $pdo->prepare("
            INSERT INTO import_permits (
                permit_number, ira_reference, issuing_officer_id, issue_date,
                permit_validity, port_of_entry, importer, importer_address,
                exporter, exporter_address, authorized_officer, end_use,
                means_of_conveyance, template_type, commodity, import_requirements
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $permitNumber,
            $iraReference,
            $issuingOfficerId,
            $issueDate,
            $permitValidity,
            $portOfEntry,
            $importer,
            $importerAddress,
            $exporter,
            $exporterAddress,
            $authorizedOfficer,
            $endUse,
            $meansOfConveyance,
            $template,
            $commodity,
            $importRequirements
        ]);

        $permitId = $pdo->lastInsertId();

        echo json_encode(['success' => true, 'message' => 'Permit saved successfully', 'permitId' => $permitId]);
    }

} catch (PDOException $e) {
    error_log("Permit save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Permit save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
?>
