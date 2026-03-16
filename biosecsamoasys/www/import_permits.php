<?php
require_once 'api/auth_check.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection for dropdowns
require_once 'api/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Permits - Biosecurity System</title>
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

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .content-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .permits-header {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .permits-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .permits-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .permits-content {
            padding: 40px;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .search-filters {
            display: flex;
            gap: 15px;
            flex: 1;
            flex-wrap: wrap;
        }

        .search-input {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            min-width: 250px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-select {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 24px;
            font-size: 1em;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%);
        }

        .permits-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .permits-table th {
            background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
        }

        .permits-table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .permits-table tr:hover {
            background: #f7fafc;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-expired {
            background: #e5e7eb;
            color: #374151;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 12px;
            width: 95%;
            max-width: 1200px;
            max-height: 90vh;
            overflow: hidden;
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5em;
        }

        .close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 30px;
            max-height: 75vh;
            overflow-y: auto;
        }

        .form-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .form-section h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #374151;
        }

        .empty-state p {
            font-size: 1.1em;
            margin-bottom: 30px;
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

        .required {
            color: #dc2626;
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

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .user-email {
            color: #a0aec0;
            font-size: 0.85em;
            margin-top: 2px;
        }

        .user-department {
            color: #a0aec0;
            font-size: 0.85em;
            margin-top: 2px;
        }

        /* Theme Styles */
        body.theme-green .app-wrapper {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        body.theme-green .nav-item.active {
            background: rgba(39, 174, 96, 0.3);
            border-left-color: #27ae60;
        }

        body.theme-green .nav-item:hover {
            background: rgba(39, 174, 96, 0.2);
            border-left-color: #27ae60;
        }

        body.theme-blue .app-wrapper {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        body.theme-blue .nav-item.active {
            background: rgba(52, 152, 219, 0.3);
            border-left-color: #3498db;
        }

        body.theme-blue .nav-item:hover {
            background: rgba(52, 152, 219, 0.2);
            border-left-color: #3498db;
        }

        body.theme-office .app-wrapper {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }

        body.theme-office .nav-item.active {
            background: rgba(108, 117, 125, 0.3);
            border-left-color: #6c757d;
        }

        body.theme-office .nav-item:hover {
            background: rgba(108, 117, 125, 0.2);
            border-left-color: #6c757d;
        }

        body.theme-office .nav-icon {
            filter: grayscale(100%);
            opacity: 0.8;
        }

        body.theme-office .nav-item.active .nav-icon {
            filter: grayscale(50%);
            opacity: 1;
        }

        body.theme-office .btn-primary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }

        body.theme-office .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
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
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-filters {
                flex-direction: column;
            }

            .form-row,
            .form-row-3 {
                grid-template-columns: 1fr;
            }

            .permits-table {
                font-size: 0.9em;
            }

            .permits-table th,
            .permits-table td {
                padding: 12px 8px;
            }

            .actions-cell {
                flex-direction: column;
                gap: 4px;
            }

            .mobile-menu-toggle {
                display: block;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="app-wrapper">
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
                    <a href="voyagement.php" class="nav-item">
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
                    <a href="import_permits.php" class="nav-item active">
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
            <div class="content-container">
                <div class="permits-header">
                    <h1>📋 Samoa Biosecurity Import Permits</h1>
                    <p>Manage biosecurity import permits and applications</p>
                </div>

                <div class="permits-content">
                    <!-- Action Bar -->
                    <div class="action-bar">
                        <div class="search-filters">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search permits...">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="openPermitModal()">
                                <span>➕</span>
                                <span>New Permit</span>
                            </button>
                        </div>
                    </div>

                    <!-- Permits Table -->
                    <div id="permitsTableContainer">
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>No Import Permits Found</h3>
                            <p>Get started by creating your first biosecurity import permit.</p>
                            <button class="btn btn-primary" onclick="openPermitModal()">
                                <span>➕</span>
                                <span>Create Permit</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create/Edit Permit Modal -->
    <div id="permitModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Create New Import Permit</h2>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="permitForm">
                    <input type="hidden" id="permitId" name="permitId">
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- Permit Information Section -->
                    <div class="form-section">
                        <h3>📋 Permit Information</h3>
                        <div class="form-row-3">
                            <div class="form-group">
                                <label for="iraReference">IRA Reference <span class="required">*</span></label>
                                <input type="text" id="iraReference" name="iraReference" required placeholder="IRA Reference">
                            </div>
                            <div class="form-group">
                                <label for="permitNumber">Permit No. <span class="required">*</span></label>
                                <input type="text" id="permitNumber" name="permitNumber" required placeholder="Auto-generated" readonly>
                            </div>
                            <div class="form-group">
                                <label for="issuingOfficer">Issuing Officer <span class="required">*</span></label>
                                <select id="issuingOfficer" name="issuingOfficer" required>
                                    <option value="">Select Officer</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row-3">
                            <div class="form-group">
                                <label for="issueDate">Issue Date <span class="required">*</span></label>
                                <input type="date" id="issueDate" name="issueDate" required>
                            </div>
                            <div class="form-group">
                                <label for="permitValidity">Permit Validity <span class="required">*</span></label>
                                <input type="date" id="permitValidity" name="permitValidity" required>
                            </div>
                            <div class="form-group">
                                <label for="portOfEntry">Port of Entry <span class="required">*</span></label>
                                <select id="portOfEntry" name="portOfEntry" required>
                                    <option value="">Select Port</option>
                                    <option value="Apia">Apia Port</option>
                                    <option value="Faleolo">Faleolo International Airport</option>
                                    <option value="Salelologa">Salelologa Wharf</option>
                                    <option value="Asau">Asau Port</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Parties Information Section -->
                    <div class="form-section">
                        <h3>👥 Parties Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="importer">Importer <span class="required">*</span></label>
                                <input type="text" id="importer" name="importer" required placeholder="Importer name">
                            </div>
                            <div class="form-group">
                                <label for="importerAddress">Importer's Address <span class="required">*</span></label>
                                <input type="text" id="importerAddress" name="importerAddress" required placeholder="Importer's full address">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="exporter">Exporter <span class="required">*</span></label>
                                <input type="text" id="exporter" name="exporter" required placeholder="Exporter name">
                            </div>
                            <div class="form-group">
                                <label for="exporterAddress">Exporter's Address <span class="required">*</span></label>
                                <input type="text" id="exporterAddress" name="exporterAddress" required placeholder="Exporter's full address">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="authorizedOfficer">Authorized Officer <span class="required">*</span></label>
                                <input type="text" id="authorizedOfficer" name="authorizedOfficer" required placeholder="Authorized officer name">
                            </div>
                            <div class="form-group">
                                <label for="endUse">End Use <span class="required">*</span></label>
                                <input type="text" id="endUse" name="endUse" required placeholder="End use description">
                            </div>
                        </div>
                    </div>

                    <!-- Commodity & Transportation Section -->
                    <div class="form-section">
                        <h3>📦 Commodity & Transportation</h3>
                        <div class="form-group">
                            <label for="template">Select a Template <span class="required">*</span></label>
                            <select id="template" name="template" required onchange="loadTemplateDetails()">
                                <option value="">Select Permit Template</option>
                                <option value="agricultural">Agricultural Products</option>
                                <option value="animal">Animal Products</option>
                                <option value="plant">Plant Products</option>
                                <option value="processed">Processed Foods</option>
                                <option value="machinery">Machinery & Equipment</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="commodity">Commodity <span class="required">*</span></label>
                            <textarea id="commodity" name="commodity" rows="4" required placeholder="Describe the commodities being imported..."></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="meansOfConveyance">Means of Conveyance <span class="required">*</span></label>
                                <select id="meansOfConveyance" name="meansOfConveyance" required>
                                    <option value="">Select Type</option>
                                    <option value="air">Air Freight</option>
                                    <option value="sea">Sea Freight</option>
                                    <option value="land">Land Transport</option>
                                    <option value="personal">Personal Effects</option>
                                    <option value="mail">Postal/Mail</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="importRequirements">Import Requirements</label>
                            <textarea id="importRequirements" name="importRequirements" rows="4" placeholder="Specific import requirements and conditions..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePermit()">Save Permit</button>
            </div>
        </div>
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

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved theme
            const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
            if (savedTheme) {
                document.body.classList.add('theme-' + savedTheme);
            }

            loadPermits();
            setupEventListeners();
            generatePermitNumber();
        });

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', filterPermits);
            document.getElementById('statusFilter').addEventListener('change', filterPermits);
            document.getElementById('issueDate').addEventListener('change', function() {
                updateValidityDate();
            });
        }

        function updateValidityDate() {
            const issueDateField = document.getElementById('issueDate');
            const validityField = document.getElementById('permitValidity');

            if (issueDateField.value) {
                const issueDate = new Date(issueDateField.value);
                const validityDate = new Date(issueDate);
                validityDate.setMonth(validityDate.getMonth() + 1);
                validityField.value = validityDate.toISOString().split('T')[0];
            }
        }

        // Permit Templates Data
        const permitTemplates = {
            agricultural: {
                importRequirements: "All agricultural products must be accompanied by phytosanitary certificates from country of origin. Products may be subject to inspection and treatment upon arrival."
            },
            animal: {
                importRequirements: "All animal products must be accompanied by health certificates. Products may be subject to veterinary inspection and quarantine."
            },
            plant: {
                importRequirements: "All plant materials must be free from pests and diseases. Products may be subject to inspection and treatment by biosecurity officers."
            },
            processed: {
                importRequirements: "Processed foods must have valid food safety certificates. Products may be sampled for testing."
            },
            machinery: {
                importRequirements: "Machinery must be clean and free from soil and organic matter. May require fumigation treatment."
            }
        };

        let permits = [];

        async function loadPermits() {
            try {
                const search = document.getElementById('searchInput').value;
                const status = document.getElementById('statusFilter').value;

                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (status) params.append('status', status);

                const response = await fetch(`api/get_permits.php?${params}`);
                const result = await response.json();

                if (result.success) {
                    permits = result.data;
                    renderPermitsTable(permits);
                } else {
                    console.error('Failed to load permits:', result.error);
                    showNotification('Failed to load permits: ' + result.error, 'error');
                }
            } catch (error) {
                console.error('Error loading permits:', error);
                showNotification('Error loading permits', 'error');
            }
        }

        async function loadOfficers() {
            try {
                const response = await fetch('api/get_officers.php');
                const result = await response.json();

                if (result.success && result.data) {
                    const officerSelect = document.getElementById('issuingOfficer');
                    // Clear existing options except the first one
                    officerSelect.innerHTML = '<option value="">Select Officer</option>';

                    result.data.forEach(officer => {
                        const option = document.createElement('option');
                        option.value = officer.officer_name;
                        option.textContent = `${officer.officer_name} (${officer.officer_role})`;
                        officerSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to load officers:', result.message || result.error);
                }
            } catch (error) {
                console.error('Error loading officers:', error);
            }
        }

        function renderPermitsTable(data) {
            const container = document.getElementById('permitsTableContainer');

            if (!data || data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <h3>No Import Permits Found</h3>
                        <p>Get started by creating your first biosecurity import permit.</p>
                        <button class="btn btn-primary" onclick="openPermitModal()">
                            <span>➕</span>
                            <span>Create Permit</span>
                        </button>
                    </div>
                `;
                return;
            }

            const table = `
                <table class="permits-table">
                    <thead>
                        <tr>
                            <th>Permit No</th>
                            <th>IRA Reference</th>
                            <th>Importer</th>
                            <th>Port of Entry</th>
                            <th>Issue Date</th>
                            <th>Validity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(permit => createPermitRow(permit)).join('')}
                    </tbody>
                </table>
            `;

            container.innerHTML = table;
        }

        function createPermitRow(permit) {
            const statusClass = `status-${permit.status}`;
            const statusText = permit.status.charAt(0).toUpperCase() + permit.status.slice(1);

            return `
                <tr>
                    <td><strong>${permit.permitNumber || 'N/A'}</strong></td>
                    <td>${permit.iraReference || 'N/A'}</td>
                    <td>${permit.importer || 'N/A'}</td>
                    <td>${permit.portOfEntry || 'N/A'}</td>
                    <td>${permit.issueDate || 'N/A'}</td>
                    <td>${permit.permitValidity || 'N/A'}</td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td class="actions-cell">
                        <button class="btn btn-sm btn-secondary" onclick="viewPermit(${permit.id})" title="View Details">
                            👁️
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="editPermit(${permit.id})" title="Edit Permit">
                            ✏️
                        </button>
                        <button class="btn btn-sm" style="background: #27ae60; color: white;" onclick="printPermit(${permit.id})" title="Print Permit">
                            🖨️
                        </button>
                        <button class="btn btn-sm" style="background: #ef4444; color: white;" onclick="deletePermit(${permit.id})" title="Delete Permit">
                            🗑️
                        </button>
                    </td>
                </tr>
            `;
        }

        function filterPermits() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;

            let filtered = permits.filter(permit => {
                const matchesSearch = !searchTerm ||
                    (permit.permitNumber && permit.permitNumber.toLowerCase().includes(searchTerm)) ||
                    (permit.iraReference && permit.iraReference.toLowerCase().includes(searchTerm)) ||
                    (permit.importer && permit.importer.toLowerCase().includes(searchTerm));

                const matchesStatus = !statusFilter || permit.status === statusFilter;

                return matchesSearch && matchesStatus;
            });

            renderPermitsTable(filtered);
        }

        function generatePermitNumber() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const permitNumber = `IMP-${year}-${random}`;
            document.getElementById('permitNumber').value = permitNumber;
        }

        function loadTemplateDetails() {
            const templateSelect = document.getElementById('template');
            const importRequirementsField = document.getElementById('importRequirements');

            if (templateSelect.value && permitTemplates[templateSelect.value]) {
                importRequirementsField.value = permitTemplates[templateSelect.value].importRequirements;
            } else {
                importRequirementsField.value = '';
            }
        }

        function openPermitModal() {
            document.getElementById('modalTitle').textContent = 'Create New Import Permit';
            document.getElementById('permitForm').reset();
            document.getElementById('permitId').value = '';

            // Set default dates
            const today = new Date();
            document.getElementById('issueDate').value = today.toISOString().split('T')[0];

            // Set validity to 1 month from issue date
            const validityDate = new Date(today);
            validityDate.setMonth(validityDate.getMonth() + 1);
            document.getElementById('permitValidity').value = validityDate.toISOString().split('T')[0];

            generatePermitNumber();
            loadOfficers();
            document.getElementById('permitModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('permitModal').style.display = 'none';
        }

        async function savePermit() {
            const formData = new FormData(document.getElementById('permitForm'));
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('api/submit_permit.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(result.message || 'Import permit saved successfully!');
                    closeModal();
                    loadPermits(); // Reload permits from database
                } else {
                    showNotification('Error: ' + result.error, 'error');
                }
            } catch (error) {
                console.error('Error saving permit:', error);
                showNotification('Error saving permit', 'error');
            }
        }

        function viewPermit(id) {
            const permit = permits.find(p => p.id === id);
            if (!permit) return;

            // For now, just edit the permit (in real implementation, show view-only modal)
            editPermit(id);
        }

        function printPermit(id) {
            // Open print page in new window
            window.open(`print_permit.php?id=${id}`, '_blank', 'width=800,height=1000');
        }

        function editPermit(id) {
            const permit = permits.find(p => p.id === id);
            if (!permit) return;

            document.getElementById('modalTitle').textContent = 'Edit Import Permit';
            document.getElementById('permitId').value = permit.id;

            // Populate form fields
            document.getElementById('iraReference').value = permit.iraReference || '';
            document.getElementById('permitNumber').value = permit.permitNumber || '';
            document.getElementById('issuingOfficer').value = permit.issuingOfficer || '';
            document.getElementById('issueDate').value = permit.issueDate || '';
            document.getElementById('permitValidity').value = permit.permitValidity || '';
            document.getElementById('portOfEntry').value = permit.portOfEntry || '';
            document.getElementById('importer').value = permit.importer || '';
            document.getElementById('importerAddress').value = permit.importerAddress || '';
            document.getElementById('exporter').value = permit.exporter || '';
            document.getElementById('exporterAddress').value = permit.exporterAddress || '';
            document.getElementById('authorizedOfficer').value = permit.authorizedOfficer || '';
            document.getElementById('endUse').value = permit.endUse || '';
            document.getElementById('meansOfConveyance').value = permit.meansOfConveyance || '';
            document.getElementById('template').value = permit.template || '';
            document.getElementById('commodity').value = permit.commodity || '';
            document.getElementById('importRequirements').value = permit.importRequirements || '';

            // Load template details if a template is selected
            loadTemplateDetails();
            loadOfficers();

            document.getElementById('permitModal').style.display = 'block';
        }

        async function deletePermit(id) {
            if (!confirm('Are you sure you want to delete this import permit? This action cannot be undone.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('permitId', id);
                formData.append('csrf_token', document.getElementById('csrf_token').value);

                const response = await fetch('api/delete_permit.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Import permit deleted successfully!');
                    loadPermits(); // Reload permits from database
                } else {
                    showNotification('Error: ' + result.error, 'error');
                }
            } catch (error) {
                console.error('Error deleting permit:', error);
                showNotification('Error deleting permit', 'error');
            }
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            let bgColor;
            let icon;

            if (type === 'error') {
                bgColor = '#ef4444'; // Red for error
                icon = '⚠️ ';
            } else {
                bgColor = '#10b981'; // Green for success
                icon = '✓ ';
            }

            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${bgColor};
                color: white;
                padding: 15px 25px;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = icon + message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('permitModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Add slideOut animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>