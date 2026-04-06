<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if (!requireAuth('officer')) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $conn = getDBConnection();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // Get single template by ID
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Template ID required']);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM import_permit_templates WHERE id = ?");
        $stmt->execute([$id]);
        $template = $stmt->fetch();

        if ($template) {
            echo json_encode(['success' => true, 'data' => $template]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Template not found']);
        }
    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Template ID required']);
            exit;
        }

        $stmt = $conn->prepare("SELECT template_name FROM import_permit_templates WHERE id = ?");
        $stmt->execute([$id]);
        $template = $stmt->fetch();

        if (!$template) {
            echo json_encode(['success' => false, 'error' => 'Template not found']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM import_permit_templates WHERE id = ?");
        $stmt->execute([$id]);

        $stmt = $conn->prepare("
            INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
            VALUES (NULL, 'DELETE_PERMIT_TEMPLATE', ?, ?)
        ");
        $stmt->execute([
            "Deleted permit template '{$template['template_name']}' (ID: {$id})",
            $_SESSION['user_id'] ?? 'System'
        ]);

        echo json_encode(['success' => true, 'message' => 'Template deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
