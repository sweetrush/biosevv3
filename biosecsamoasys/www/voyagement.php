<?php
require_once 'api/auth_check.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voyagement - Biosecurity System</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: #1a202c;
            border-bottom: 1px solid #4a5568;
        }

        .sidebar-user-tile {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            font-size: 1.5em;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-subtitle {
            font-size: 0.85em;
            color: #a0aec0;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 25px;
        }

        .nav-section-title {
            padding: 0 20px;
            margin-bottom: 10px;
            font-size: 0.75em;
            text-transform: uppercase;
            color: #718096;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .nav-item {
            display: block;
            padding: 15px 20px;
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(102, 126, 234, 0.2);
            border-left-color: #667eea;
            padding-left: 25px;
        }

        .nav-item.active {
            background: rgba(102, 126, 234, 0.3);
            border-left-color: #667eea;
            color: white;
        }

        .nav-icon {
            font-size: 1.3em;
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 2px solid #4a5568;
            background: #1a202c;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e2e8f0;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95em;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 15px;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 0.85em;
            font-weight: 600;
            margin-top: 15px;
            width: 100%;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 0;
            transition: margin-left 0.3s ease;
        }

        .container {
            max-width: 100%;
            margin: 0;
            border-radius: 0;
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #2d3748;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2em;
        }

        /* Tabs styling */
        .tabs {
            display: flex;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            padding: 0;
            margin: 0;
            list-style: none;
            border-radius: 12px 12px 0 0;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .tabs li {
            flex: 1;
            position: relative;
        }

        .tabs li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 1px;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
        }

        .tabs li:last-child::after {
            display: none;
        }

        .tabs button {
            width: 100%;
            padding: 16px 8px;
            background: transparent;
            color: rgba(255, 255, 255, 0.8);
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 85px;
            position: relative;
            overflow: hidden;
        }

        .tabs button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }

        .tabs button:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-2px);
        }

        .tabs button:hover::before {
            transform: translateY(0);
        }

        .tabs button.active {
            background: rgba(102, 126, 234, 0.2);
            color: white;
            box-shadow: inset 0 3px 0 #667eea;
        }

        .tabs button.active::before {
            transform: translateY(0);
            height: 4px;
        }

        .tab-content {
            display: none;
            animation: slideInFade 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            border-radius: 0 0 12px 12px;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes slideInFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-number {
            font-size: 10px;
            opacity: 0.7;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .tab-icon {
            font-size: 24px;
            margin: 0;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            transition: transform 0.3s ease;
        }

        .tab-label {
            font-size: 11px;
            line-height: 1.3;
            text-align: center;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .tabs button:hover .tab-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* Theme Selector */
        .theme-selector {
            padding: 15px 20px;
            border-top: 2px solid #4a5568;
        }

        .theme-selector-title {
            font-size: 0.75em;
            text-transform: uppercase;
            color: #718096;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .theme-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .theme-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .theme-option:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .theme-option.active {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.2);
        }

        .theme-color-preview {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .theme-name {
            color: #e2e8f0;
            font-size: 0.9em;
            font-weight: 500;
        }

        /* Theme: Green Environment */
        body.theme-green {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        body.theme-green .app-wrapper {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        body.theme-green .tabs button.active {
            background: #27ae60;
        }

        body.theme-green .nav-item.active {
            background: rgba(39, 174, 96, 0.3);
            border-left-color: #27ae60;
        }

        body.theme-green .nav-item:hover {
            background: rgba(39, 174, 96, 0.2);
            border-left-color: #27ae60;
        }

        body.theme-green .form-section h2 {
            border-bottom: 3px solid #27ae60;
        }

        body.theme-green .submit-btn,
        body.theme-green .btn-primary {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        body.theme-green .submit-btn:hover,
        body.theme-green .btn-primary:hover {
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
        }

        /* Theme: Blue Ocean */
        body.theme-blue {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        body.theme-blue .app-wrapper {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        body.theme-blue .tabs button.active {
            background: #3498db;
        }

        body.theme-blue .nav-item.active {
            background: rgba(52, 152, 219, 0.3);
            border-left-color: #3498db;
        }

        body.theme-blue .nav-item:hover {
            background: rgba(52, 152, 219, 0.2);
            border-left-color: #3498db;
        }

        body.theme-blue .form-section h2 {
            border-bottom: 3px solid #3498db;
        }

        body.theme-blue .submit-btn,
        body.theme-blue .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        body.theme-blue .submit-btn:hover,
        body.theme-blue .btn-primary:hover {
            box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
        }

        /* Theme: Clear Skies (Purple - default) */
        body.theme-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body.theme-purple .app-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body.theme-purple .tabs button.active {
            background: #667eea;
        }

        body.theme-purple .nav-item.active {
            background: rgba(102, 126, 234, 0.3);
            border-left-color: #667eea;
        }

        body.theme-purple .nav-item:hover {
            background: rgba(102, 126, 234, 0.2);
            border-left-color: #667eea;
        }

        body.theme-purple .form-section h2 {
            border-bottom: 3px solid #667eea;
        }

        body.theme-purple .submit-btn,
        body.theme-purple .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body.theme-purple .submit-btn:hover,
        body.theme-purple .btn-primary:hover {
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        /* Theme: Professional Office */
        body.theme-office {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }

        body.theme-office .app-wrapper {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }

        body.theme-office .tabs button.active {
            background: #6c757d;
        }

        body.theme-office .nav-item.active {
            background: rgba(108, 117, 125, 0.3);
            border-left-color: #6c757d;
        }

        body.theme-office .nav-item:hover {
            background: rgba(108, 117, 125, 0.2);
            border-left-color: #6c757d;
        }

        body.theme-office .form-section h2 {
            border-bottom: 3px solid #6c757d;
        }

        body.theme-office .submit-btn,
        body.theme-office .btn-primary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }

        body.theme-office .submit-btn:hover,
        body.theme-office .btn-primary:hover {
            box-shadow: 0 5px 20px rgba(108, 117, 125, 0.4);
        }

        body.theme-office .nav-icon {
            filter: grayscale(100%);
            opacity: 0.8;
        }

        body.theme-office .nav-item.active .nav-icon {
            filter: grayscale(50%);
            opacity: 1;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .tabs {
                flex-wrap: wrap;
                height: auto;
            }

            .tabs li {
                flex: 1 1 50%;
                min-width: 140px;
            }

            .tabs li:nth-child(5) {
                flex: 1 1 100%;
            }

            .tabs button {
                padding: 12px 6px;
                font-size: 11px;
                min-height: 70px;
                gap: 4px;
            }

            .tab-number {
                font-size: 9px;
                letter-spacing: 0.3px;
            }

            .tab-icon {
                font-size: 18px;
            }

            .tab-label {
                font-size: 10px;
                line-height: 1.2;
            }

            .tabs button:hover .tab-icon {
                transform: scale(1.05);
            }

            .container {
                border-radius: 0;
            }

            .tab-content {
                border-radius: 0;
            }

            /* Form responsive adjustments */
            .form-row,
            .form-row.form-row-3,
            .form-row.form-row-4 {
                grid-template-columns: 1fr;
            }

            .form-section {
                margin: 0 15px 30px 15px;
                padding: 20px;
            }

            .btn {
                padding: 14px 30px;
                font-size: 0.9em;
                width: 100%;
            }

            .form-actions {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .tabs button {
                padding: 14px 6px;
                font-size: 12px;
                min-height: 75px;
            }

            .tab-icon {
                font-size: 20px;
            }

            .form-row.form-row-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1025px) and (max-width: 1200px) {
            .form-row.form-row-4 {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php
    // Initialize voyage editing variables
    $edit_voyage_id = null;
    $edit_mode = false;
    $voyage_data = null;

    // Database connection using getDBConnection()
    require_once 'api/config.php';

    function getVoyageData($voyageId) {
        try {
            $conn = getDBConnection();

            // Get voyage details
            $sql = "SELECT vd.*, vs.current_step, vs.status,
                           vs.voyage_details_complete, vs.passenger_inspection_complete,
                           vs.passenger_seizure_complete, vs.cargo_seizure_complete, vs.cargo_release_complete
                    FROM voyage_details vd
                    LEFT JOIN voyage_status vs ON vd.VoyageID = vs.VoyageID
                    WHERE vd.VoyageID = :VoyageID";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':VoyageID', $voyageId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            // Log the error for debugging
            error_log("Error loading voyage data: " . $e->getMessage());
            return false;
        }
    }

    // Check for voyage_id parameter
    if (isset($_GET['voyage_id']) && !empty($_GET['voyage_id'])) {
        $edit_voyage_id = (int)$_GET['voyage_id'];
        $edit_mode = true;

        // Load voyage data directly from database
        if ($edit_voyage_id) {
            try {
                $voyage_data = getVoyageData($edit_voyage_id);
                if (!$voyage_data) {
                    // Voyage not found, revert to create mode
                    $edit_mode = false;
                    $edit_voyage_id = null;
                }
            } catch (Exception $e) {
                // Error loading voyage data - will proceed in create mode
                $edit_mode = false;
                $edit_voyage_id = null;
            }
        }
    }
    ?>
    <div class="app-wrapper" <?php echo $edit_mode ? 'data-voyage-id="' . $edit_voyage_id . '"' : ''; ?>>
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    🚢
                    <span>BioSecure</span>
                </div>
                <div class="sidebar-subtitle">Samoa Biosecurity</div>

                <!-- User Profile Tile at Top -->
                <div class="sidebar-user-tile">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['first_name'] ?? 'B', 0, 1) . substr($_SESSION['last_name'] ?? 'S', 0, 1)); ?></div>
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars(($_SESSION['first_name'] ?? 'Bio') . ' ' . ($_SESSION['last_name'] ?? 'Officer')); ?></div>
                            <div class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['access_level'] ?? 'Administrator')); ?></div>
                        </div>
                    </div>
                    <a href="api/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                        <span>🚪</span>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="index.php" class="nav-item">
                        <span class="nav-icon">🏠</span>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="voyage_management.php" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span>Voyage Management</span>
                    </a>
                    <a href="voyagement.php" class="nav-item active">
                        <span class="nav-icon">🚢</span>
                        <span>Voyagement</span>
                    </a>
                    <a href="location_management.php" class="nav-item">
                        <span class="nav-icon">📍</span>
                        <span>Location Management</span>
                    </a>
                    <a href="unified_seizure.php" class="nav-item">
                        <span class="nav-icon">⚠️</span>
                        <span>Seizure Management</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">📊</span>
                        <span>Reports</span>
                    </a>
                    <a href="settings.php" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span>Settings</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Permits & Certificates</div>
                    <a href="import_permits.php" class="nav-item">
                        <span class="nav-icon">📋</span>
                        <span>Import Permits</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">📄</span>
                        <span>Export Certificates</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">🏥</span>
                        <span>Health Certificates</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">🧪</span>
                        <span>Laboratory Reports</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">📜</span>
                        <span>Quarantine Orders</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Resources</div>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">📚</span>
                        <span>Documentation</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">❓</span>
                        <span>Help</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
            ☰
        </button>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="container">
                <header>
                    <h1>🚢 Biosecurity Information System - Samoa</h1>
                    <p>
                        <?php if ($edit_mode && $voyage_data): ?>
                            <strong>Edit Mode:</strong> Voyage #<?php echo htmlspecialchars($voyage_data['VoyageNo']); ?> - <?php echo htmlspecialchars($voyage_data['VesselID']); ?>
                        <?php elseif ($edit_mode): ?>
                            <strong>Edit Mode:</strong> Voyage ID #<?php echo $edit_voyage_id; ?>
                        <?php else: ?>
                            Vessel Voyage and Passenger Inspection Management
                        <?php endif; ?>
                    </p>
                </header>

                <ul class="tabs">
                    <li>
                        <button class="tab-link active" onclick="openTab(event, 'voyageTab')">
                            <span class="tab-number">Step 1</span>
                            <span class="tab-icon">🚢</span>
                            <span class="tab-label">Voyage Details</span>
                        </button>
                    </li>
                    <li>
                        <button class="tab-link" onclick="openTab(event, 'inspectionTab')">
                            <span class="tab-number">Step 2</span>
                            <span class="tab-icon">🔍</span>
                            <span class="tab-label">Passenger<br>Inspection</span>
                        </button>
                    </li>
                    <li>
                        <button class="tab-link" onclick="openTab(event, 'seizureTab')">
                            <span class="tab-number">Step 3</span>
                            <span class="tab-icon">⚠️</span>
                            <span class="tab-label">Passenger<br>Seizure</span>
                        </button>
                    </li>
                    <li>
                        <button class="tab-link" onclick="openTab(event, 'cargoSeizureTab')">
                            <span class="tab-number">Step 4</span>
                            <span class="tab-icon">🚨</span>
                            <span class="tab-label">Cargo<br>Seizure</span>
                        </button>
                    </li>
                    <li>
                        <button class="tab-link" onclick="openTab(event, 'cargoReleaseTab')">
                            <span class="tab-number">Step 5</span>
                            <span class="tab-icon">📦</span>
                            <span class="tab-label">Cargo<br>Release</span>
                        </button>
                    </li>
                </ul>

                <div id="voyageTab" class="tab-content active">
                    <?php include 'voyage_form.php'; ?>
                </div>

                <div id="inspectionTab" class="tab-content">
                    <?php include 'inspection_form.php'; ?>
                </div>

                <div id="seizureTab" class="tab-content">
                    <?php include 'seizure_form.php'; ?>
                </div>

                <div id="cargoSeizureTab" class="tab-content">
                    <?php include 'cargo_seizure_form.php'; ?>
                </div>

                <div id="cargoReleaseTab" class="tab-content">
                    <?php include 'cargo_release_form.php'; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Load saved theme on page load and initialize edit mode
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
            if (savedTheme) {
                document.body.classList.add('theme-' + savedTheme);
            }

            // Load locations for dropdown
            loadLocationDropdown();
            // Load countries for dropdown
            loadCountryDropdowns();
            // Load ports for dropdown
            loadPortsDropdown();

            // Initialize edit mode if voyage_id is present
            const appWrapper = document.querySelector('.app-wrapper');
            const voyageId = appWrapper.getAttribute('data-voyage-id');
            if (voyageId) {
                initializeEditMode(voyageId);
            }
        });

        // Load locations dropdown
        async function loadLocationDropdown() {
            const locationSelect = document.getElementById('LocationID');
            const loadingDiv = document.getElementById('locationLoading');

            if (!locationSelect) {
                console.log('Location dropdown not found on current page');
                return;
            }

            try {
                loadingDiv.textContent = 'Loading locations...';
                loadingDiv.style.color = '#666';

                const response = await fetch('api/get_locations.php');
                const result = await response.json();

                if (result.success) {
                    // Clear existing options except the first one
                    locationSelect.innerHTML = '<option value="">Select a location...</option>';

                    // Group locations by region
                    const locationsByRegion = {};
                    result.data.forEach(location => {
                        if (!locationsByRegion[location.region]) {
                            locationsByRegion[location.region] = [];
                        }
                        locationsByRegion[location.region].push(location);
                    });

                    // Add grouped options
                    Object.keys(locationsByRegion).sort().forEach(region => {
                        // Add region as optgroup
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = `📍 ${region}`;

                        // Sort locations within region
                        locationsByRegion[region].sort((a, b) => a.location_name.localeCompare(b.location_name));

                        // Add locations to optgroup
                        locationsByRegion[region].forEach(location => {
                            const option = document.createElement('option');
                            option.value = location.location_id;
                            option.textContent = `${location.location_name} (${location.location_type})`;
                            if (!location.is_active) {
                                option.textContent += ' [Inactive]';
                                option.style.color = '#999';
                            }
                            optgroup.appendChild(option);
                        });

                        locationSelect.appendChild(optgroup);
                    });

                    loadingDiv.textContent = `${result.data.length} locations loaded`;
                    loadingDiv.style.color = '#28a745';
                } else {
                    loadingDiv.textContent = 'Error loading locations';
                    loadingDiv.style.color = '#dc3545';
                }
            } catch (error) {
                console.error('Error loading locations:', error);
                loadingDiv.textContent = 'Network error loading locations';
                loadingDiv.style.color = '#dc3545';
            }
        }

        // Load countries dropdown
        async function loadCountryDropdowns() {
            // Load Port of Loading dropdown
            const portOfLoadingSelect = document.getElementById('PortOfLoadingID');
            if (portOfLoadingSelect && portOfLoadingSelect.tagName === 'SELECT') {
                await loadCountryDropdown(portOfLoadingSelect, 'Port of Loading');
            }

            // Load Last Port dropdown
            const lastPortSelect = document.getElementById('LastPortID');
            if (lastPortSelect && lastPortSelect.tagName === 'SELECT') {
                await loadCountryDropdown(lastPortSelect, 'Last Port');
            }
        }

        async function loadCountryDropdown(selectElement, fieldName) {
            try {
                const response = await fetch('api/get_countries.php');
                const result = await response.json();

                if (result.success) {
                    // Store original options and clear
                    const currentValue = selectElement.value;
                    selectElement.innerHTML = '<option value="">Select a country...</option>';

                    // Group countries by first letter for better organization
                    const countriesByLetter = {};
                    result.data.forEach(country => {
                        if (country.CountryName && country.CountryName.trim() !== '') {
                            const firstLetter = country.CountryName.charAt(0).toUpperCase();
                            if (!countriesByLetter[firstLetter]) {
                                countriesByLetter[firstLetter] = [];
                            }
                            countriesByLetter[firstLetter].push(country);
                        }
                    });

                    // Add grouped options
                    Object.keys(countriesByLetter).sort().forEach(letter => {
                        // Add letter as optgroup
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = `${letter}`;

                        // Sort countries within letter
                        countriesByLetter[letter].sort((a, b) => a.CountryName.localeCompare(b.CountryName));

                        // Add countries to optgroup
                        countriesByLetter[letter].forEach(country => {
                            const option = document.createElement('option');
                            option.value = country.CountryID;
                            option.textContent = country.CountryName;
                            optgroup.appendChild(option);
                        });

                        selectElement.appendChild(optgroup);
                    });

                    // Restore previous selection if it still exists
                    if (currentValue) {
                        const existingOption = Array.from(selectElement.options).find(option => option.value === currentValue);
                        if (existingOption) {
                            selectElement.value = currentValue;
                        }
                    }

                    console.log(`Loaded ${result.data.length} countries for ${fieldName}`);
                } else {
                    console.error('Error loading countries:', result.message);
                }
            } catch (error) {
                console.error('Error loading countries:', error);
            }
        }

        // Load ports dropdown for Port of Arrival
        async function loadPortsDropdown() {
            const portSelect = document.getElementById('PortOfArrivalID');
            if (!portSelect) return;

            // Only process if it's a select element (not a text input)
            if (portSelect.tagName !== 'SELECT') {
                console.log('PortOfArrivalID is not a select element, skipping port loading');
                return;
            }

            try {
                const response = await fetch('api/get_ports.php');
                const result = await response.json();

                if (result.success && result.data) {
                    const currentValue = portSelect.value;
                    portSelect.innerHTML = '<option value="">Select a port...</option>';

                    // Group ports by country
                    const portsByCountry = {};
                    result.data.forEach(port => {
                        const country = port.country || 'Unknown';
                        if (!portsByCountry[country]) {
                            portsByCountry[country] = [];
                        }
                        portsByCountry[country].push(port);
                    });

                    // Add grouped options
                    Object.keys(portsByCountry).sort().forEach(country => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = country;

                        portsByCountry[country].sort((a, b) => a.port_name.localeCompare(b.port_name))
                            .forEach(port => {
                                const option = document.createElement('option');
                                option.value = port.port_id;
                                option.textContent = port.port_name;
                                optgroup.appendChild(option);
                            });

                        portSelect.appendChild(optgroup);
                    });

                    // Restore previous selection if it still exists
                    if (currentValue) {
                        const existingOption = Array.from(portSelect.options).find(option => option.value === currentValue);
                        if (existingOption) {
                            portSelect.value = currentValue;
                        }
                    }

                    console.log(`Loaded ${result.data.length} ports`);
                } else {
                    console.error('Error loading ports:', result.message);
                }
            } catch (error) {
                console.error('Error loading ports:', error);
            }
        }

        // Initialize edit mode functionality
        function initializeEditMode(voyageId) {
            // Set global voyage ID for form submissions
            window.currentVoyageId = voyageId;

            // Load voyage data and populate forms
            loadVoyageData(voyageId);

            // Load voyage status and set appropriate active tab
            loadVoyageStatus(voyageId);
        }

        async function loadVoyageData(voyageId) {
            try {
                const response = await fetch(`api/voyage_details.php?id=${voyageId}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const voyage = result.data;

                    // Populate voyage details form
                    populateVoyageForm(voyage);

                    // Load related data for other tabs
                    if (voyage.container_counts) {
                        populateContainerCounts(voyage.container_counts);
                    }

                    if (voyage.passenger_inspections) {
                        populatePassengerInspections(voyage.passenger_inspections);
                    }

                    if (voyage.passenger_seizures) {
                        populatePassengerSeizures(voyage.passenger_seizures);
                    }

                    if (voyage.cargo_seizures) {
                        populateCargoSeizures(voyage.cargo_seizures);
                    }

                    if (voyage.cargo_releases) {
                        populateCargoReleases(voyage.cargo_releases);
                    }
                } else {
                    console.error('Failed to load voyage data:', result.message);
                }
            } catch (error) {
                console.error('Error loading voyage data:', error);
            }
        }

        async function loadVoyageStatus(voyageId) {
            try {
                const response = await fetch(`api/voyage_status.php?id=${voyageId}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const status = result.data;

                    // Update status indicators
                    updateStatusIndicators(status);

                    // Open appropriate tab based on current step
                    if (status.current_step) {
                        const stepMap = {
                            'voyage_details': 'voyageTab',
                            'passenger_inspection': 'inspectionTab',
                            'passenger_seizure': 'seizureTab',
                            'cargo_seizure': 'cargoSeizureTab',
                            'cargo_release': 'cargoReleaseTab'
                        };

                        const targetTab = stepMap[status.current_step];
                        if (targetTab) {
                            const tabButton = document.querySelector(`[onclick="openTab(event, '${targetTab}')"]`);
                            if (tabButton) {
                                tabButton.click();
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading voyage status:', error);
            }
        }

        function populateVoyageForm(voyage) {
            // Populate all form fields with voyage data
            Object.keys(voyage).forEach(key => {
                const element = document.getElementById(key);
                if (element && element.type !== 'hidden') {
                    element.value = voyage[key] || '';
                }
            });
        }

        function populateContainerCounts(containerCounts) {
            if (!containerCounts || containerCounts.length === 0) {
                console.log('No container counts to populate');
                return;
            }

            // Get all container count input fields
            const containerInputs = document.querySelectorAll('[id*="CC_"][type="text"]');

            // Clear existing values
            containerInputs.forEach(input => {
                input.value = '';
            });

            // Populate with database values
            containerCounts.forEach(container => {
                const fieldId = `CC_${container.container_type_code}`;
                const input = document.getElementById(fieldId);
                if (input) {
                    input.value = container.count || '';
                    // Trigger input event for any listeners
                    input.dispatchEvent(new Event('input'));
                }
            });

            console.log('Loaded container counts:', containerCounts.length);
        }

        function populatePassengerInspections(inspections) {
            if (!inspections || inspections.length === 0) {
                console.log('No passenger inspections to populate');
                return;
            }

            // For passenger inspections, load the first one
            const inspection = inspections[0];
            Object.keys(inspection).forEach(key => {
                const element = document.getElementById(key);
                if (element && element.type !== 'hidden') {
                    element.value = inspection[key] || '';
                }
                // Handle select elements specifically if needed
                const select = document.getElementById(key);
                if (select && select.tagName === 'SELECT' && inspection[key]) {
                    select.value = inspection[key];
                    select.dispatchEvent(new Event('change'));
                }
            });

            console.log('Loaded passenger inspection:', inspection);
        }

        function populatePassengerSeizures(seizures) {
            if (!seizures || seizures.length === 0) {
                console.log('No passenger seizures to populate');
                return;
            }

            // For passenger seizures, load the first one
            const seizure = seizures[0];

            // Remove any existing passenger seizure forms first
            const existingForms = document.querySelectorAll('[id^="item-"]');
            existingForms.forEach(form => form.remove());

            Object.keys(seizure).forEach(key => {
                const element = document.getElementById(key);
                if (element && element.type !== 'hidden') {
                    element.value = seizure[key] || '';
                }
                // Handle select elements specifically
                const select = document.getElementById(key);
                if (select && select.tagName === 'SELECT' && seizure[key]) {
                    select.value = seizure[key];
                    select.dispatchEvent(new Event('change'));
                }
                // Handle radio buttons
                const radios = document.querySelectorAll(`input[name="${key}"]`);
                if (radios.length > 0 && seizure[key]) {
                    radios.forEach(radio => {
                        if (radio.value === seizure[key]) {
                            radio.checked = true;
                        }
                    });
                }
            });

            console.log('Loaded passenger seizure:', seizure);
        }

        function populateCargoSeizures(seizures) {
            if (!seizures || seizures.length === 0) {
                console.log('No cargo seizures to populate');
                return;
            }

            // For cargo seizures, load the first one
            const seizure = seizures[0];

            Object.keys(seizure).forEach(key => {
                // Handle both cs_ prefixed and regular IDs
                let element = document.getElementById(key);
                if (!element) {
                    element = document.getElementById(`cs_${key}`);
                }

                if (element && element.type !== 'hidden') {
                    element.value = seizure[key] || '';
                }
                // Handle select elements specifically
                if (element && element.tagName === 'SELECT' && seizure[key]) {
                    element.value = seizure[key];
                    element.dispatchEvent(new Event('change'));
                }
            });

            console.log('Loaded cargo seizure:', seizure);
        }

        function populateCargoReleases(releases) {
            if (!releases || releases.length === 0) {
                console.log('No cargo releases to populate');
                return;
            }

            // For cargo releases, load the first one
            const release = releases[0];

            Object.keys(release).forEach(key => {
                const element = document.getElementById(key);
                if (element && element.type !== 'hidden') {
                    element.value = release[key] || '';
                }
                // Handle select elements specifically
                if (element && element.tagName === 'SELECT' && release[key]) {
                    element.value = release[key];
                    element.dispatchEvent(new Event('change'));
                }
            });

            console.log('Loaded cargo release:', release);
        }

        function updateStatusIndicators(status) {
            // Update UI to show voyage status and step completion
            console.log('Updating status indicators:', status);

            // Define step mapping
            const steps = [
                { key: 'voyage_details_complete', tabId: 'voyageTab', tabSelector: '[onclick*="voyageTab"]' },
                { key: 'passenger_inspection_complete', tabId: 'inspectionTab', tabSelector: '[onclick*="inspectionTab"]' },
                { key: 'passenger_seizure_complete', tabId: 'seizureTab', tabSelector: '[onclick*="seizureTab"]' },
                { key: 'cargo_seizure_complete', tabId: 'cargoSeizureTab', tabSelector: '[onclick*="cargoSeizureTab"]' },
                { key: 'cargo_release_complete', tabId: 'cargoReleaseTab', tabSelector: '[onclick*="cargoReleaseTab"]' }
            ];

            // Update each step indicator
            steps.forEach(step => {
                const tabButton = document.querySelector(step.tabSelector);
                if (tabButton) {
                    // Remove existing completion indicators
                    const existingIcon = tabButton.querySelector('.step-complete-icon');
                    if (existingIcon) {
                        existingIcon.remove();
                    }

                    // If step is complete, add checkmark
                    if (status[step.key]) {
                        const icon = document.createElement('span');
                        icon.className = 'step-complete-icon';
                        icon.innerHTML = ' ✓';
                        icon.style.cssText = 'position: absolute; top: 8px; right: 8px; color: #10b981; font-size: 1.2em; font-weight: bold;';
                        icon.title = 'Completed';
                        tabButton.style.position = 'relative';
                        tabButton.appendChild(icon);

                        // Add completed class to tab content
                        const tabContent = document.getElementById(step.tabId);
                        if (tabContent) {
                            tabContent.classList.add('step-completed');
                        }
                    }
                }
            });

            // Update current step indicator
            if (status.current_step) {
                steps.forEach(step => {
                    const tabButton = document.querySelector(step.tabSelector);
                    if (tabButton) {
                        tabButton.classList.remove('current-step');
                        if (status.current_step === step.tabId.replace('Tab', '').replace('cargoSeizure', 'cargo_seizure').replace('cargoRelease', 'cargo_release')) {
                            tabButton.classList.add('current-step');
                        }
                    }
                });
            }
        }

        function openTab(evt, tabName) {
            // Hide all tab content
            var tabContent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabContent.length; i++) {
                tabContent[i].classList.remove("active");
            }

            // Remove active class from all tab links
            var tabLinks = document.getElementsByClassName("tab-link");
            for (var i = 0; i < tabLinks.length; i++) {
                tabLinks[i].classList.remove("active");
            }

            // Show the current tab and mark button as active
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");

            // If switching to inspection tab, check for active voyage
            if (tabName === 'inspectionTab' && typeof checkActiveVoyage === 'function') {
                checkActiveVoyage();
            }

            // If switching to seizure tab, check for active voyage
            if (tabName === 'seizureTab' && typeof checkActiveVoyageSeizure === 'function') {
                checkActiveVoyageSeizure();
            }

            // If switching to cargo release tab, check for active voyage
            if (tabName === 'cargoReleaseTab' && typeof checkActiveVoyageRelease === 'function') {
                checkActiveVoyageRelease();
            }

            // If switching to cargo seizure tab, check for active voyage
            if (tabName === 'cargoSeizureTab' && typeof checkActiveVoyageCargoSeizure === 'function') {
                checkActiveVoyageCargoSeizure();
            }

            // Load country dropdowns when switching to seizure forms
            if (tabName === 'seizureTab' && typeof loadCountryDropdownForSeizure === 'function') {
                setTimeout(loadCountryDropdownForSeizure, 100);
            }
            if (tabName === 'cargoSeizureTab' && typeof loadCountryDropdownForCargoSeizure === 'function') {
                setTimeout(loadCountryDropdownForCargoSeizure, 100);
            }
        }
    </script>

    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="inspection_script.js?v=<?php echo time(); ?>"></script>
    <script src="seizure_script.js?v=<?php echo time(); ?>"></script>
    <script src="cargo_release_script.js?v=<?php echo time(); ?>"></script>
    <script src="cargo_seizure_script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
