<?php
require_once 'api/auth_check.php';
require_once 'api/config.php';

// Get permit ID from URL
$permitId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$permitId) {
    die('Invalid permit ID');
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT
        ip.*,
        o.officer_name as issuing_officer_name,
        o.officer_email as issuing_officer_email,
        o.officer_role as issuing_officer_role
    FROM import_permits ip
    LEFT JOIN officers o ON ip.issuing_officer_id = o.officer_id
    WHERE ip.permit_id = ?");
    $stmt->execute([$permitId]);
    $permit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$permit) {
        die('Permit not found');
    }
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

// Template type labels
$templateLabels = [
    'agricultural' => 'Agricultural Products',
    'animal' => 'Animal Products',
    'plant' => 'Plant Products',
    'processed' => 'Processed Foods',
    'machinery' => 'Machinery & Equipment'
];

$conveyanceLabels = [
    'air' => 'Air Freight',
    'sea' => 'Sea Freight',
    'land' => 'Land Transport',
    'personal' => 'Personal Effects',
    'mail' => 'Postal/Mail'
];

// Format dates
$issueDate = date('d F Y', strtotime($permit['issue_date']));
$validityDate = date('d F Y', strtotime($permit['permit_validity']));
$createdDate = date('d F Y', strtotime($permit['created_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Permit - <?php echo htmlspecialchars($permit['permit_number']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            background: #f5f5f5;
        }

        .permit-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 20mm;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .print-actions {
            text-align: center;
            padding: 20px;
            background: #333;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .print-actions button {
            padding: 12px 30px;
            margin: 0 10px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-print {
            background: #27ae60;
            color: white;
        }

        .btn-print:hover {
            background: #229954;
        }

        .btn-close {
            background: #e74c3c;
            color: white;
        }

        .btn-close:hover {
            background: #c0392b;
        }

        .permit-body {
            margin-top: 80px;
        }

        /* Header Section */
        .permit-header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .logo-section {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .emblem {
            font-size: 48px;
        }

        .header-text h1 {
            font-size: 18pt;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .header-text h2 {
            font-size: 14pt;
            color: #34495e;
            font-weight: normal;
        }

        .header-text h3 {
            font-size: 12pt;
            color: #7f8c8d;
            font-weight: normal;
            margin-top: 5px;
        }

        .permit-title {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 15px;
            margin: 25px 0;
            text-align: center;
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 3px;
            border-radius: 5px;
        }

        /* Permit Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background: #fafafa;
        }

        .info-box.full-width {
            grid-column: 1 / -1;
        }

        .info-box h4 {
            color: #2c3e50;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #3498db;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 140px;
        }

        .info-value {
            color: #333;
            flex: 1;
        }

        /* Status Badge */
        .status-section {
            text-align: center;
            margin: 25px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 10px 40px;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 30px;
            letter-spacing: 2px;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }

        .status-expired {
            background: #e2e3e5;
            color: #383d41;
            border: 2px solid #6c757d;
        }

        /* Commodity Section */
        .commodity-section {
            margin: 25px 0;
        }

        .commodity-section h4 {
            color: #2c3e50;
            font-size: 12pt;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #3498db;
        }

        .commodity-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
            white-space: pre-wrap;
            font-size: 10pt;
            line-height: 1.6;
        }

        /* Requirements Box */
        .requirements-box {
            background: #fff9e6;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }

        .requirements-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .requirements-content {
            white-space: pre-wrap;
            font-size: 10pt;
            line-height: 1.6;
        }

        /* Footer */
        .permit-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #2c3e50;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 10px;
        }

        .signature-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .signature-title {
            font-size: 9pt;
            color: #7f8c8d;
        }

        .permit-meta {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9pt;
            color: #7f8c8d;
        }

        .validity-warning {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            font-weight: 600;
            color: #856404;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(200, 200, 200, 0.2);
            pointer-events: none;
            z-index: -1;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
            }

            .permit-container {
                box-shadow: none;
                padding: 15mm;
            }

            .print-actions {
                display: none !important;
            }

            .permit-body {
                margin-top: 0;
            }

            .permit-container {
                max-width: 100%;
            }

            @page {
                size: A4;
                margin: 10mm;
            }
        }

        /* QR Code placeholder */
        .qr-section {
            text-align: center;
            margin-top: 20px;
        }

        .qr-code {
            width: 80px;
            height: 80px;
            background: #f0f0f0;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            color: #999;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="btn-print" onclick="window.print()">Print Permit</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    <div class="permit-body">
        <div class="permit-container">
            <!-- Watermark for non-approved permits -->
            <?php if ($permit['status'] !== 'approved'): ?>
                <div class="watermark"><?php echo strtoupper($permit['status']); ?></div>
            <?php endif; ?>

            <!-- Header -->
            <div class="permit-header">
                <div class="logo-section">
                    <div class="emblem">🌴</div>
                    <div class="header-text">
                        <h1>Samoa Biosecurity Service</h2>
                        <h2>Ministry of Agriculture and Fisheries</h2>
                        <h3>Independent State of Samoa</h3>
                    </div>
                    <div class="emblem">🛡️</div>
                </div>
            </div>

            <!-- Permit Title -->
            <div class="permit-title">
                Import Permit
            </div>

            <!-- Status Badge -->
            <div class="status-section">
                <span class="status-badge status-<?php echo $permit['status']; ?>">
                    <?php echo strtoupper($permit['status']); ?>
                </span>
            </div>

            <!-- Permit Information -->
            <div class="info-grid">
                <div class="info-box">
                    <h4>Permit Details</h4>
                    <div class="info-row">
                        <span class="info-label">Permit Number:</span>
                        <span class="info-value"><strong><?php echo htmlspecialchars($permit['permit_number']); ?></strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">IRA Reference:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['ira_reference']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Issue Date:</span>
                        <span class="info-value"><?php echo $issueDate; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Valid Until:</span>
                        <span class="info-value"><?php echo $validityDate; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Port of Entry:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['port_of_entry']); ?></span>
                    </div>
                </div>

                <div class="info-box">
                    <h4>Issuing Officer</h4>
                    <div class="info-row">
                        <span class="info-label">Officer Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['issuing_officer_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Position:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['issuing_officer_role'] ?? 'Biosecurity Officer'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['issuing_officer_email'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Parties Information -->
            <div class="info-grid">
                <div class="info-box">
                    <h4>Importer Details</h4>
                    <div class="info-row">
                        <span class="info-label">Importer:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['importer']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['importer_address']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Authorized Officer:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['authorized_officer']); ?></span>
                    </div>
                </div>

                <div class="info-box">
                    <h4>Exporter Details</h4>
                    <div class="info-row">
                        <span class="info-label">Exporter:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['exporter']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['exporter_address']); ?></span>
                    </div>
                </div>
            </div>

            <!-- End Use -->
            <div class="info-grid">
                <div class="info-box full-width">
                    <h4>End Use & Transportation</h4>
                    <div class="info-row">
                        <span class="info-label">End Use:</span>
                        <span class="info-value"><?php echo htmlspecialchars($permit['end_use']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Means of Conveyance:</span>
                        <span class="info-value"><?php echo $conveyanceLabels[$permit['means_of_conveyance']] ?? $permit['means_of_conveyance']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Permit Type:</span>
                        <span class="info-value"><?php echo $templateLabels[$permit['template_type']] ?? $permit['template_type']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Commodity Section -->
            <div class="commodity-section">
                <h4>Commodity Description</h4>
                <div class="commodity-content">
<?php echo htmlspecialchars($permit['commodity']); ?>
                </div>
            </div>

            <!-- Import Requirements -->
            <?php if (!empty($permit['import_requirements'])): ?>
            <div class="requirements-box">
                <h4>Import Requirements & Conditions</h4>
                <div class="requirements-content">
<?php echo htmlspecialchars($permit['import_requirements']); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Validity Warning -->
            <div class="validity-warning">
                This permit is valid from <?php echo $issueDate; ?> to <?php echo $validityDate; ?>.
                Any consignment arriving after the validity date will require a new permit.
            </div>

            <!-- Signature Section -->
            <div class="permit-footer">
                <div class="signature-section">
                    <div class="signature-box">
                        <div class="signature-line">
                            <div class="signature-name"><?php echo htmlspecialchars($permit['issuing_officer_name'] ?? 'Biosecurity Officer'); ?></div>
                            <div class="signature-title">Issuing Officer</div>
                            <div class="signature-title">Samoa Biosecurity Service</div>
                        </div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line">
                            <div class="signature-name">Chief Biosecurity Officer</div>
                            <div class="signature-title">Authorized Signatory</div>
                            <div class="signature-title">Ministry of Agriculture and Fisheries</div>
                        </div>
                    </div>
                </div>

                <!-- Meta Information -->
                <div class="permit-meta">
                    <p>Permit ID: <?php echo $permit['permit_id']; ?> | Generated on: <?php echo date('d F Y H:i'); ?></p>
                    <p>Samoa Biosecurity Service - Protecting Samoa's Biodiversity</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
