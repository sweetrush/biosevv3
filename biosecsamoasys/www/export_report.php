<?php
require_once 'api/auth_check.php';
require_once 'api/config.php';

define('APP_VERSION', '1.0.0');

// Get voyage ID from URL
$voyageId = isset($_GET['voyage_id']) ? intval($_GET['voyage_id']) : 0;

if (!$voyageId) {
    die('Invalid voyage ID');
}

try {
    $pdo = getDBConnection();

    // Get voyage details with port and location names
    $stmt = $pdo->prepare("SELECT vd.*,
                                  vs.status, vs.current_step,
                                  vs.voyage_details_complete, vs.passenger_inspection_complete,
                                  vs.passenger_seizure_complete, vs.cargo_seizure_complete, vs.cargo_release_complete
                           FROM voyage_details vd
                           LEFT JOIN voyage_status vs ON vd.VoyageID = vs.VoyageID
                           WHERE vd.VoyageID = ?");
    $stmt->execute([$voyageId]);
    $voyage = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$voyage) {
        die('Voyage not found');
    }

    // Get container counts
    $stmt = $pdo->prepare("SELECT * FROM voyage_container_counts WHERE VoyageID = ? ORDER BY container_type_code");
    $stmt->execute([$voyageId]);
    $containerCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get passenger inspections with commodity and location names
    $stmt = $pdo->prepare("SELECT pi.*, ct.CommodityType, ct.CommodityGroupID,
                                  l.location_name
                           FROM passenger_inspection pi
                           LEFT JOIN commodity_types ct ON pi.CommodityTypeID = ct.CommodityTypeID
                           LEFT JOIN locations l ON pi.LocationID = l.location_id
                           WHERE pi.VoyageID = ?
                           ORDER BY pi.PassengerInspectionID ASC");
    $stmt->execute([$voyageId]);
    $inspections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get passenger seizures with country and port names
    $stmt = $pdo->prepare("SELECT ps.*,
                                  co.CountryName AS CountryOfOriginName
                           FROM passenger_seizure ps
                           LEFT JOIN countries co ON ps.CountryOfOrigin = co.CountryID COLLATE utf8mb4_unicode_ci
                           WHERE ps.VoyageID = ?
                           ORDER BY ps.PassengerSeizureID ASC");
    $stmt->execute([$voyageId]);
    $passengerSeizures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get cargo seizures with country and port names
    $stmt = $pdo->prepare("SELECT cs.*,
                                  co.CountryName AS CountryOfOriginName
                           FROM cargo_seizure cs
                           LEFT JOIN countries co ON cs.CountryOfOrigin = co.CountryID COLLATE utf8mb4_unicode_ci
                           WHERE cs.VoyageID = ?
                           ORDER BY cs.CargoSeizureID ASC");
    $stmt->execute([$voyageId]);
    $cargoSeizures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get cargo releases
    $stmt = $pdo->prepare("SELECT * FROM cargo_release WHERE VoyageID = ? ORDER BY ReleaseID ASC");
    $stmt->execute([$voyageId]);
    $cargoReleases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get audit trail
    $stmt = $pdo->prepare("SELECT * FROM voyage_audit_trail WHERE VoyageID = ? ORDER BY performed_at DESC LIMIT 20");
    $stmt->execute([$voyageId]);
    $auditTrail = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

// Helper functions
function safeVal($val) {
    return $val !== null && $val !== '' ? htmlspecialchars($val) : '<span class="empty">—</span>';
}

function formatDate($date) {
    if (!$date) return '<span class="empty">—</span>';
    return htmlspecialchars($date);
}

function yesNo($val) {
    if ($val === 'Yes' || $val === '1' || $val === true) return 'Yes';
    if ($val === 'No' || $val === '0' || $val === false) return 'No';
    return '<span class="empty">—</span>';
}

function stepLabel($step) {
    $labels = [
        'voyage_details' => 'Voyage Details',
        'passenger_inspection' => 'Passenger Inspection',
        'passenger_seizure' => 'Passenger Seizure',
        'cargo_seizure' => 'Cargo Seizure',
        'cargo_release' => 'Cargo Release',
        'completed' => 'Completed'
    ];
    return $labels[$step] ?? $step;
}

$groupNames = [
    '1' => 'Animals and Animal Products',
    '2' => 'Plants and Plant Products',
    '3' => 'MEV (Machinery, Equipment, Vehicles)',
    '4' => 'Other',
    '5' => 'Pesticides'
];

$reportDate = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voyage Report #<?php echo htmlspecialchars($voyage['VoyageNo'] ?? $voyageId); ?> - Biosecurity System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #4f6df5;
            --primary-dark: #3b5bdb;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
            --bg-body: #f1f5f9;
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --error: #dc2626;
            --error-light: #fee2e2;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --radius: 8px;
            --radius-lg: 12px;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        .report-container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .report-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 36px;
            text-align: center;
        }
        .report-header h1 {
            font-size: 1.6em;
            margin-bottom: 8px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .report-header .subtitle {
            font-size: 1em;
            opacity: 0.85;
            margin-bottom: 12px;
        }
        .report-header .report-meta {
            font-size: 0.82em;
            opacity: 0.6;
        }
        .report-body {
            padding: 32px;
        }
        .section {
            margin-bottom: 28px;
            page-break-inside: avoid;
        }
        .section h2 {
            font-size: 1.15em;
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }
        .section h2 .step-badge {
            background: var(--primary);
            color: white;
            font-size: 0.65em;
            padding: 3px 10px;
            border-radius: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .section h2 .status-badge {
            font-size: 0.65em;
            padding: 3px 10px;
            border-radius: 15px;
            font-weight: 600;
            margin-left: auto;
        }
        .status-complete { background: var(--success-light); color: #065f46; }
        .status-incomplete { background: var(--warning-light); color: #92400e; }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
        }
        .info-item {
            background: var(--bg-light);
            padding: 10px 14px;
            border-radius: var(--radius);
            border-left: 3px solid var(--primary);
        }
        .info-item .label {
            font-size: 0.72em;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            font-weight: 600;
        }
        .info-item .value {
            font-size: 0.925em;
            font-weight: 600;
            color: var(--text-primary);
        }
        .info-item .value .empty {
            color: var(--text-muted);
            font-weight: 400;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table th {
            background: var(--bg-light);
            padding: 10px 12px;
            text-align: left;
            font-size: 0.78em;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875em;
            color: var(--text-primary);
        }
        table tr:hover { background: var(--bg-light); }
        .empty { color: var(--text-muted); }
        .compliance-rate {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .compliance-high { background: var(--success-light); color: #065f46; }
        .compliance-medium { background: var(--warning-light); color: #92400e; }
        .compliance-low { background: var(--error-light); color: #991b1b; }

        .summary-box {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 28px;
        }
        .summary-box h3 {
            color: var(--text-primary);
            margin-bottom: 14px;
            font-size: 1em;
            font-weight: 700;
        }
        .summary-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 28px;
        }
        .summary-stat {
            text-align: center;
        }
        .summary-stat .number {
            font-size: 1.6em;
            font-weight: 700;
            color: var(--primary);
        }
        .summary-stat .label {
            font-size: 0.72em;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .audit-entry {
            font-size: 0.82em;
            padding: 7px 12px;
            border-left: 3px solid var(--primary);
            background: var(--bg-light);
            margin-bottom: 4px;
            border-radius: 0 var(--radius) var(--radius) 0;
        }
        .audit-entry .audit-action {
            font-weight: 600;
            color: var(--text-primary);
        }
        .audit-entry .audit-time {
            color: var(--text-muted);
            font-size: 0.85em;
        }
        .report-footer {
            text-align: center;
            padding: 16px;
            background: var(--bg-light);
            color: var(--text-muted);
            font-size: 0.8em;
            border-top: 1px solid var(--border-color);
            line-height: 1.8;
        }
        .print-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius);
            font-size: 0.9em;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(79, 109, 245, 0.3);
            z-index: 1000;
            transition: all 0.2s ease;
            font-weight: 600;
            font-family: inherit;
        }
        .print-btn:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .back-btn {
            position: fixed;
            top: 16px;
            left: 16px;
            background: var(--text-primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius);
            font-size: 0.9em;
            cursor: pointer;
            z-index: 1000;
            text-decoration: none;
            font-weight: 600;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .back-btn:hover { background: #0f172a; }

        @media print {
            body { background: white; }
            .report-container { box-shadow: none; margin: 0; border-radius: 0; }
            .print-btn, .back-btn { display: none; }
            .section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <a href="voyage_management.php" class="back-btn">&larr; Back</a>
    <button class="print-btn" onclick="window.print()">🖨️ Print Report</button>

    <div class="report-container">
        <div class="report-header">
            <h1>🚢 Biosecurity Voyage Report</h1>
            <div class="subtitle">Voyage #<?php echo htmlspecialchars($voyage['VoyageNo'] ?? 'N/A'); ?> — Vessel: <?php echo htmlspecialchars($voyage['VesselID'] ?? 'N/A'); ?></div>
            <div class="report-meta">
                Report generated: <?php echo $reportDate; ?> |
                Status: <?php echo ucfirst($voyage['status'] ?? 'draft'); ?> |
                Current step: <?php echo stepLabel($voyage['current_step'] ?? 'voyage_details'); ?>
            </div>
        </div>

        <div class="report-body">
            <!-- Summary Box -->
            <div class="summary-box">
                <h3>📊 Voyage Summary</h3>
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="number"><?php echo count($inspections); ?></div>
                        <div class="label">Inspections</div>
                    </div>
                    <div class="summary-stat">
                        <div class="number"><?php echo count($passengerSeizures); ?></div>
                        <div class="label">Pass. Seizures</div>
                    </div>
                    <div class="summary-stat">
                        <div class="number"><?php echo count($cargoSeizures); ?></div>
                        <div class="label">Cargo Seizures</div>
                    </div>
                    <div class="summary-stat">
                        <div class="number"><?php echo count($cargoReleases); ?></div>
                        <div class="label">Cargo Releases</div>
                    </div>
                    <div class="summary-stat">
                        <div class="number"><?php echo count($containerCounts); ?></div>
                        <div class="label">Cargo Types</div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Voyage Details -->
            <div class="section">
                <h2>
                    <span class="step-badge">Step 1</span>
                    🚢 Voyage & Vessel Details
                    <span class="status-badge <?php echo $voyage['voyage_details_complete'] ? 'status-complete' : 'status-incomplete'; ?>">
                        <?php echo $voyage['voyage_details_complete'] ? '✓ Complete' : 'Incomplete'; ?>
                    </span>
                </h2>
                <div class="info-grid">
                    <div class="info-item"><div class="label">Voyage Number</div><div class="value"><?php echo safeVal($voyage['VoyageNo']); ?></div></div>
                    <div class="info-item"><div class="label">Vessel ID</div><div class="value"><?php echo safeVal($voyage['VesselID']); ?></div></div>
                    <div class="info-item"><div class="label">Transport Mode</div><div class="value"><?php echo safeVal($voyage['AirOfSea']); ?></div></div>
                    <div class="info-item"><div class="label">Arrival Date</div><div class="value"><?php echo formatDate($voyage['ArrivalDate']); ?></div></div>
                    <div class="info-item"><div class="label">Port of Loading</div><div class="value"><?php echo safeVal($voyage['PortOfLoadingID']); ?></div></div>
                    <div class="info-item"><div class="label">Last Port</div><div class="value"><?php echo safeVal($voyage['LastPortID']); ?></div></div>
                    <div class="info-item"><div class="label">Port of Arrival</div><div class="value"><?php echo safeVal($voyage['PortOfArrivalID']); ?></div></div>
                    <div class="info-item"><div class="label">Location</div><div class="value"><?php echo safeVal($voyage['LocationID']); ?></div></div>
                </div>
                <div class="info-grid" style="margin-top: 15px;">
                    <div class="info-item"><div class="label">Passengers</div><div class="value"><?php echo safeVal($voyage['Pax']); ?></div></div>
                    <div class="info-item"><div class="label">Crew</div><div class="value"><?php echo safeVal($voyage['Crew']); ?></div></div>
                    <div class="info-item"><div class="label">Crew Searched</div><div class="value"><?php echo safeVal($voyage['CrewSearched']); ?></div></div>
                    <div class="info-item"><div class="label">Total Discharged</div><div class="value"><?php echo safeVal($voyage['TotalDischarged']); ?></div></div>
                    <div class="info-item"><div class="label">Room Sealed</div><div class="value"><?php echo yesNo($voyage['RoomSealed']); ?></div></div>
                    <div class="info-item"><div class="label">X-Ray Inspection</div><div class="value"><?php echo safeVal($voyage['NoXRated']); ?></div></div>
                    <div class="info-item"><div class="label">Bonded Animals</div><div class="value"><?php echo yesNo($voyage['BondedAnimals']); ?></div></div>
                    <div class="info-item"><div class="label">Animal Health Cert.</div><div class="value"><?php echo yesNo($voyage['AnimalHealthCertificate']); ?></div></div>
                    <div class="info-item"><div class="label">Bonded Animals Desc.</div><div class="value"><?php echo safeVal($voyage['BondedAnimalsDescription']); ?></div></div>
                    <div class="info-item"><div class="label">No. Containers</div><div class="value"><?php echo safeVal($voyage['NumberContainers']); ?></div></div>
                    <div class="info-item"><div class="label">No. Cargos Discharged</div><div class="value"><?php echo safeVal($voyage['NumberCargosDischarged']); ?></div></div>
                    <div class="info-item"><div class="label">Port Authority</div><div class="value"><?php echo safeVal($voyage['PortAuthority']); ?></div></div>
                </div>

                <?php if (count($containerCounts) > 0): ?>
                <h3 style="margin-top: 20px; font-size: 1.1em; color: #4a5568;">Cargo Type Counts</h3>
                <table>
                    <thead>
                        <tr><th>Cargo Type</th><th>Count</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($containerCounts as $cc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cc['container_type_code']); ?></td>
                            <td><?php echo htmlspecialchars($cc['count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <div class="info-grid" style="margin-top: 15px;">
                    <div class="info-item"><div class="label">Modified By</div><div class="value"><?php echo safeVal($voyage['ModifiedBy']); ?></div></div>
                    <div class="info-item"><div class="label">Modified Date</div><div class="value"><?php echo formatDate($voyage['ModifiedDate']); ?></div></div>
                </div>
            </div>

            <!-- Step 2: Passenger Inspections -->
            <div class="section">
                <h2>
                    <span class="step-badge">Step 2</span>
                    🔍 Passenger Inspections
                    <span class="status-badge <?php echo $voyage['passenger_inspection_complete'] ? 'status-complete' : 'status-incomplete'; ?>">
                        <?php echo $voyage['passenger_inspection_complete'] ? '✓ Complete' : 'Incomplete'; ?>
                    </span>
                </h2>
                <?php if (count($inspections) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Commodity Type</th>
                            <th>Group</th>
                            <th>Location</th>
                            <th>Consignments</th>
                            <th>Non-Compliant</th>
                            <th>Compliance Rate</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inspections as $insp):
                            $consignments = (int)($insp['NoOfConsignments'] ?? 0);
                            $nonCompliant = (int)($insp['NoOfNonCompliant'] ?? 0);
                            $rate = $consignments > 0 ? (($consignments - $nonCompliant) / $consignments * 100) : null;
                            $rateClass = $rate === null ? '' : ($rate >= 95 ? 'compliance-high' : ($rate >= 80 ? 'compliance-medium' : 'compliance-low'));
                        ?>
                        <tr>
                            <td><?php echo $insp['PassengerInspectionID']; ?></td>
                            <td><?php echo safeVal($insp['CommodityType']); ?></td>
                            <td><?php echo $groupNames[$insp['CommodityGroupID']] ?? safeVal($insp['CommodityGroupID']); ?></td>
                            <td><?php echo safeVal($insp['location_name']); ?></td>
                            <td><?php echo $consignments; ?></td>
                            <td><?php echo $nonCompliant; ?></td>
                            <td>
                                <?php if ($rate !== null): ?>
                                <span class="compliance-rate <?php echo $rateClass; ?>"><?php echo number_format($rate, 1); ?>%</span>
                                <?php else: ?>
                                <span class="empty">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($insp['ModifiedDate']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color: #a0aec0; padding: 15px;">No passenger inspection records for this voyage.</p>
                <?php endif; ?>
            </div>

            <!-- Step 3: Passenger Seizures -->
            <div class="section">
                <h2>
                    <span class="step-badge">Step 3</span>
                    ⚠️ Passenger Seizures
                    <span class="status-badge <?php echo $voyage['passenger_seizure_complete'] ? 'status-complete' : 'status-incomplete'; ?>">
                        <?php echo $voyage['passenger_seizure_complete'] ? '✓ Complete' : 'Incomplete'; ?>
                    </span>
                </h2>
                <?php if (count($passengerSeizures) > 0): ?>
                <?php foreach ($passengerSeizures as $idx => $seizure): ?>
                <div style="background: #f7fafc; border-radius: 8px; padding: 15px; margin-bottom: 15px; border-left: 4px solid #f59e0b;">
                    <h3 style="margin-bottom: 10px; font-size: 1.05em; color: #4a5568;">Seizure #<?php echo $seizure['PassengerSeizureID']; ?> — <?php echo safeVal($seizure['SeizureNo']); ?></h3>
                    <div class="info-grid">
                        <div class="info-item"><div class="label">Seizure Date</div><div class="value"><?php echo formatDate($seizure['SeizureDate']); ?></div></div>
                        <div class="info-item"><div class="label">Importer</div><div class="value"><?php echo safeVal($seizure['Importer']); ?></div></div>
                        <div class="info-item"><div class="label">Detection Method</div><div class="value"><?php echo safeVal($seizure['DetectionMethod']); ?></div></div>
                        <div class="info-item"><div class="label">Country of Origin</div><div class="value"><?php echo safeVal($seizure['CountryOfOriginName']); ?></div></div>
                        <div class="info-item"><div class="label">Goods Declared</div><div class="value"><?php echo safeVal($seizure['GoodsDeclared']); ?></div></div>
                        <div class="info-item"><div class="label">Commodity Type</div><div class="value"><?php echo safeVal($seizure['CommodityType']); ?></div></div>
                        <div class="info-item"><div class="label">Description</div><div class="value"><?php echo safeVal($seizure['Description']); ?></div></div>
                        <div class="info-item"><div class="label">Quantity</div><div class="value"><?php echo safeVal($seizure['Quantity'] . ' ' . ($seizure['Unit'] ?? '')); ?></div></div>
                        <div class="info-item"><div class="label">Volume</div><div class="value"><?php echo safeVal($seizure['Volume']); ?></div></div>
                        <div class="info-item"><div class="label">Officer Name</div><div class="value"><?php echo safeVal($seizure['OfficerName']); ?></div></div>
                        <div class="info-item"><div class="label">Action Taken</div><div class="value"><?php echo safeVal($seizure['ActionTaken']); ?></div></div>
                        <div class="info-item"><div class="label">Action Officer</div><div class="value"><?php echo safeVal($seizure['ActionOfficer']); ?></div></div>
                        <div class="info-item"><div class="label">Date Completed</div><div class="value"><?php echo formatDate($seizure['DateActionCompleted']); ?></div></div>
                        <div class="info-item"><div class="label">Comments</div><div class="value"><?php echo safeVal($seizure['Comments']); ?></div></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p style="color: #a0aec0; padding: 15px;">No passenger seizure records for this voyage.</p>
                <?php endif; ?>
            </div>

            <!-- Step 4: Cargo Seizures -->
            <div class="section">
                <h2>
                    <span class="step-badge">Step 4</span>
                    🚨 Cargo Seizures
                    <span class="status-badge <?php echo $voyage['cargo_seizure_complete'] ? 'status-complete' : 'status-incomplete'; ?>">
                        <?php echo $voyage['cargo_seizure_complete'] ? '✓ Complete' : 'Incomplete'; ?>
                    </span>
                </h2>
                <?php if (count($cargoSeizures) > 0): ?>
                <?php foreach ($cargoSeizures as $seizure): ?>
                <div style="background: #f7fafc; border-radius: 8px; padding: 15px; margin-bottom: 15px; border-left: 4px solid #ef4444;">
                    <h3 style="margin-bottom: 10px; font-size: 1.05em; color: #4a5568;">Cargo Seizure #<?php echo $seizure['CargoSeizureID']; ?> — <?php echo safeVal($seizure['SeizureNo']); ?></h3>
                    <div class="info-grid">
                        <div class="info-item"><div class="label">Seizure Date</div><div class="value"><?php echo formatDate($seizure['SeizureDate']); ?></div></div>
                        <div class="info-item"><div class="label">Container/Cargo Ref</div><div class="value"><?php echo safeVal($seizure['ContainerCargoRefNo']); ?></div></div>
                        <div class="info-item"><div class="label">Importer</div><div class="value"><?php echo safeVal($seizure['Importer']); ?></div></div>
                        <div class="info-item"><div class="label">Cargo Description</div><div class="value"><?php echo safeVal($seizure['CargoDescription']); ?></div></div>
                        <div class="info-item"><div class="label">Depot Name</div><div class="value"><?php echo safeVal($seizure['DepotName']); ?></div></div>
                        <div class="info-item"><div class="label">Detection Method</div><div class="value"><?php echo safeVal($seizure['DetectionMethod']); ?></div></div>
                        <div class="info-item"><div class="label">Country of Origin</div><div class="value"><?php echo safeVal($seizure['CountryOfOriginName']); ?></div></div>
                        <div class="info-item"><div class="label">Commodity Type</div><div class="value"><?php echo safeVal($seizure['CommodityType']); ?></div></div>
                        <div class="info-item"><div class="label">Description</div><div class="value"><?php echo safeVal($seizure['Description']); ?></div></div>
                        <div class="info-item"><div class="label">Quantity</div><div class="value"><?php echo safeVal($seizure['Quantity'] . ' ' . ($seizure['Unit'] ?? '')); ?></div></div>
                        <div class="info-item"><div class="label">Volume (kg)</div><div class="value"><?php echo safeVal($seizure['VolumeKg']); ?></div></div>
                        <div class="info-item"><div class="label">Seizing Officer</div><div class="value"><?php echo safeVal($seizure['SeizingOfficerName']); ?></div></div>
                        <div class="info-item"><div class="label">Action Taken</div><div class="value"><?php echo safeVal($seizure['ActionTaken']); ?></div></div>
                        <div class="info-item"><div class="label">Action Officer</div><div class="value"><?php echo safeVal($seizure['ActionOfficer']); ?></div></div>
                        <div class="info-item"><div class="label">Date Completed</div><div class="value"><?php echo formatDate($seizure['DateActionCompleted']); ?></div></div>
                        <div class="info-item"><div class="label">Comments</div><div class="value"><?php echo safeVal($seizure['Comments']); ?></div></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p style="color: #a0aec0; padding: 15px;">No cargo seizure records for this voyage.</p>
                <?php endif; ?>
            </div>

            <!-- Step 5: Cargo Releases -->
            <div class="section">
                <h2>
                    <span class="step-badge">Step 5</span>
                    📦 Cargo Releases
                    <span class="status-badge <?php echo $voyage['cargo_release_complete'] ? 'status-complete' : 'status-incomplete'; ?>">
                        <?php echo $voyage['cargo_release_complete'] ? '✓ Complete' : 'Incomplete'; ?>
                    </span>
                </h2>
                <?php if (count($cargoReleases) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Release No</th>
                            <th>Importer</th>
                            <th>Release Type</th>
                            <th>Release Date</th>
                            <th>Total Costs</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cargoReleases as $release): ?>
                        <tr>
                            <td><?php echo $release['ReleaseID']; ?></td>
                            <td><?php echo safeVal($release['ReleaseNo']); ?></td>
                            <td><?php echo safeVal($release['Importer']); ?></td>
                            <td><?php echo safeVal($release['ReleaseType']); ?></td>
                            <td><?php echo formatDate($release['ReleaseDate']); ?></td>
                            <td><?php echo safeVal($release['TotalCosts']); ?></td>
                            <td><?php echo safeVal($release['Comments']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color: #a0aec0; padding: 15px;">No cargo release records for this voyage.</p>
                <?php endif; ?>
            </div>

            <!-- Audit Trail -->
            <?php if (count($auditTrail) > 0): ?>
            <div class="section">
                <h2>📋 Audit Trail (Last 20 entries)</h2>
                <?php foreach ($auditTrail as $audit): ?>
                <div class="audit-entry">
                    <span class="audit-action"><?php echo htmlspecialchars($audit['action']); ?></span>
                    — <?php echo htmlspecialchars($audit['action_details'] ?? ''); ?>
                    <span class="audit-time">by <?php echo htmlspecialchars($audit['performed_by'] ?? 'System'); ?> at <?php echo htmlspecialchars($audit['performed_at']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="report-footer">
            <p>Samoa Biosecurity Authority — Voyage Report for #<?php echo htmlspecialchars($voyage['VoyageNo'] ?? $voyageId); ?></p>
            <p>Generated on <?php echo $reportDate; ?> — Version <?php echo APP_VERSION; ?></p>
        </div>
    </div>
</body>
</html>
