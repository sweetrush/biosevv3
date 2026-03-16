<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Check authentication
if (!requireAuth()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access - Please log in']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Get query parameters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    // Build the query
    $whereConditions = [];
    $params = [];

    if (!empty($search)) {
        $whereConditions[] = "(permit_number LIKE ? OR ira_reference LIKE ? OR importer LIKE ? OR authorized_officer LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM import_permits ip
                 LEFT JOIN officers o ON ip.issuing_officer_id = o.officer_id
                 $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get permits with officer information
    $sql = "SELECT
                ip.permit_id,
                ip.permit_number,
                ip.ira_reference,
                ip.issue_date,
                ip.permit_validity,
                ip.port_of_entry,
                ip.importer,
                ip.importer_address,
                ip.exporter,
                ip.exporter_address,
                ip.authorized_officer,
                ip.end_use,
                ip.means_of_conveyance,
                ip.template_type,
                ip.commodity,
                ip.import_requirements,
                ip.status,
                o.officer_name as issuing_officer_name,
                o.officer_email as issuing_officer_email,
                ip.created_date,
                ip.modified_date
            FROM import_permits ip
            LEFT JOIN officers o ON ip.issuing_officer_id = o.officer_id
            $whereClause
            ORDER BY ip.created_date DESC
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    // Bind all params first
    $paramIndex = 1;
    foreach ($params as $key => $value) {
        if ($key === count($params) - 2 || $key === count($params) - 1) {
            // These are LIMIT and OFFSET - bind as integers
            $stmt->bindValue($paramIndex, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($paramIndex, $value, PDO::PARAM_STR);
        }
        $paramIndex++;
    }
    $stmt->execute();
    $permits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data to match the frontend structure
    $formattedPermits = array_map(function($permit) {
        return [
            'id' => $permit['permit_id'],
            'permitNumber' => $permit['permit_number'],
            'iraReference' => $permit['ira_reference'],
            'issuingOfficer' => $permit['issuing_officer_name'] ?: 'Unknown Officer',
            'issueDate' => $permit['issue_date'],
            'permitValidity' => $permit['permit_validity'],
            'portOfEntry' => $permit['port_of_entry'],
            'importer' => $permit['importer'],
            'importerAddress' => $permit['importer_address'],
            'exporter' => $permit['exporter'],
            'exporterAddress' => $permit['exporter_address'],
            'authorizedOfficer' => $permit['authorized_officer'],
            'endUse' => $permit['end_use'],
            'meansOfConveyance' => $permit['means_of_conveyance'],
            'template' => $permit['template_type'],
            'commodity' => $permit['commodity'],
            'importRequirements' => $permit['import_requirements'],
            'status' => $permit['status'],
            'createdDate' => $permit['created_date'],
            'modifiedDate' => $permit['modified_date']
        ];
    }, $permits);

    echo json_encode([
        'success' => true,
        'data' => $formattedPermits,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);

} catch (PDOException $e) {
    error_log("Get permits error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Get permits error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
?>
