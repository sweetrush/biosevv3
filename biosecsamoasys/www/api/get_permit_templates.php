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

    $search = $_GET['search'] ?? '';
    $statusFilter = $_GET['in_use'] ?? '';

    $sql = "SELECT * FROM import_permit_templates WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (template_name LIKE ? OR ira_reference LIKE ? OR commodity_1 LIKE ? OR commodity_2 LIKE ?)";
        $likeSearch = "%{$search}%";
        $params = [$likeSearch, $likeSearch, $likeSearch, $likeSearch];
    }

    if ($statusFilter !== '') {
        $sql .= " AND in_use = ?";
        $params[] = intval($statusFilter);
    }

    $sql .= " ORDER BY modified_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $templates = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $templates]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
