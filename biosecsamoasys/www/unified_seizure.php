<?php
require_once 'api/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seizure Management - Biosecurity System</title>
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

            .container {
                border-radius: 0;
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
    </style>
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
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
                    <a href="voyagement.php" class="nav-item">
                        <span class="nav-icon">🚢</span>
                        <span>Voyagement</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="voyage_management.php" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span>Voyage Management</span>
                    </a>
                    <a href="location_management.php" class="nav-item">
                        <span class="nav-icon">📍</span>
                        <span>Location Management</span>
                    </a>
                    <a href="unified_seizure.php" class="nav-item active">
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
        <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <header>
                    <h1>📦 Unified Seizure Management</h1>
                    <p>Combined view of passenger and cargo seizures</p>
                </header>

                <div class="form-section">
                    <h2>📋 Recent Seizures (All Types)</h2>
                    <div id="seizureListContainer" style="margin-top: 20px;">
                        <p>Loading all seizures...</p>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <a href="voyagement.php" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        🚢 Go to Voyagement to Add/Edit Seizures
                    </a>
                </div>
            </div>
        </main>
    </div>

    <!-- Passenger Seizure Edit Modal -->
    <div id="passengerSeizureModal" class="edit-modal">
        <div class="modal-backdrop" onclick="closePassengerModal()"></div>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header passenger-header">
                    <h3><i class="icon">👤</i> Edit Passenger Seizure</h3>
                    <button class="close-btn" onclick="closePassengerModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="passengerSeizureForm" class="seizure-form">
                        <input type="hidden" id="passengerSeizureId" name="PassengerSeizureID">
                        <input type="hidden" id="passengerVoyageId" name="VoyageID">

                        <div class="form-section">
                            <h4>📋 Basic Information</h4>
                            <div class="form-grid">
                                <div class="form-field">
                                    <label for="passengerSeizureDate">Seizure Date <span class="required">*</span></label>
                                    <input type="date" id="passengerSeizureDate" name="SeizureDate" required>
                                </div>
                                <div class="form-field">
                                    <label for="passengerSeizureNo">Seizure Number <span class="required">*</span></label>
                                    <input type="text" id="passengerSeizureNo" name="SeizureNo" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>👤 Passenger Details</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="passengerImporter">Importer/Passenger <span class="required">*</span></label>
                                    <input type="text" id="passengerImporter" name="Importer" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>📦 Item Details</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="passengerCommodityType">Commodity Type <span class="required">*</span></label>
                                    <select id="passengerCommodityType" name="CommodityType" required>
                                        <option value="">Select commodity...</option>
                                    </select>
                                </div>
                                <div class="form-field full-width">
                                    <label for="passengerDescription">Description</label>
                                    <input type="text" id="passengerDescription" name="Description">
                                </div>
                                <div class="form-field">
                                    <label for="passengerQuantity">Quantity</label>
                                    <input type="text" id="passengerQuantity" name="Quantity">
                                </div>
                                <div class="form-field">
                                    <label for="passengerUnit">Unit</label>
                                    <input type="text" id="passengerUnit" name="Unit">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>🔍 Detection & Action</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="passengerDetectionMethod">Detection Method</label>
                                    <input type="text" id="passengerDetectionMethod" name="DetectionMethod">
                                </div>
                                <div class="form-field full-width">
                                    <label for="passengerOfficerName">Officer Name</label>
                                    <input type="text" id="passengerOfficerName" name="OfficerName">
                                </div>
                                <div class="form-field full-width">
                                    <label for="passengerActionTaken">Action Taken</label>
                                    <input type="text" id="passengerActionTaken" name="ActionTaken">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>📝 Comments</h4>
                            <div class="form-field full-width">
                                <label for="passengerComments">Additional Comments</label>
                                <textarea id="passengerComments" name="Comments" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closePassengerModal()">
                        <i class="icon">❌</i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="savePassengerSeizure()">
                        <i class="icon">💾</i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cargo Seizure Edit Modal -->
    <div id="cargoSeizureModal" class="edit-modal">
        <div class="modal-backdrop" onclick="closeCargoModal()"></div>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header cargo-header">
                    <h3><i class="icon">📦</i> Edit Cargo Seizure</h3>
                    <button class="close-btn" onclick="closeCargoModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="cargoSeizureForm" class="seizure-form">
                        <input type="hidden" id="cargoSeizureId" name="CargoSeizureID">
                        <input type="hidden" id="cargoVoyageId" name="VoyageID">

                        <div class="form-section">
                            <h4>📋 Basic Information</h4>
                            <div class="form-grid">
                                <div class="form-field">
                                    <label for="cargoSeizureDate">Seizure Date <span class="required">*</span></label>
                                    <input type="date" id="cargoSeizureDate" name="SeizureDate" required>
                                </div>
                                <div class="form-field">
                                    <label for="cargoSeizureNo">Seizure Number <span class="required">*</span></label>
                                    <input type="text" id="cargoSeizureNo" name="SeizureNo" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>🚢 Container & Cargo Details</h4>
                            <div class="form-grid">
                                <div class="form-field">
                                    <label for="cargoContainerRef">Container/Cargo Ref No</label>
                                    <input type="text" id="cargoContainerRef" name="ContainerCargoRefNo">
                                </div>
                                <div class="form-field">
                                    <label for="cargoImporter">Importer <span class="required">*</span></label>
                                    <input type="text" id="cargoImporter" name="Importer" required>
                                </div>
                                <div class="form-field full-width">
                                    <label for="cargoDescription">Cargo Description</label>
                                    <input type="text" id="cargoDescription" name="CargoDescription">
                                </div>
                                <div class="form-field">
                                    <label for="cargoDepotName">Depot Name</label>
                                    <input type="text" id="cargoDepotName" name="DepotName">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>📦 Item Details</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="cargoCommodityType">Commodity Type <span class="required">*</span></label>
                                    <select id="cargoCommodityType" name="CommodityType" required>
                                        <option value="">Select commodity...</option>
                                    </select>
                                </div>
                                <div class="form-field full-width">
                                    <label for="cargoDetectionMethod">Detection Method</label>
                                    <input type="text" id="cargoDetectionMethod" name="DetectionMethod">
                                </div>
                                <div class="form-field">
                                    <label for="cargoQuantity">Quantity</label>
                                    <input type="text" id="cargoQuantity" name="Quantity">
                                </div>
                                <div class="form-field">
                                    <label for="cargoUnit">Unit</label>
                                    <input type="text" id="cargoUnit" name="Unit">
                                </div>
                                <div class="form-field">
                                    <label for="cargoVolume">Volume (kg)</label>
                                    <input type="text" id="cargoVolume" name="VolumeKg">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>👮 Officer & Action Details</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="cargoSeizingOfficerName">Seizing Officer Name</label>
                                    <input type="text" id="cargoSeizingOfficerName" name="SeizingOfficerName">
                                </div>
                                <div class="form-field full-width">
                                    <label for="cargoActionTaken">Action Taken</label>
                                    <input type="text" id="cargoActionTaken" name="ActionTaken">
                                </div>
                                <div class="form-field full-width">
                                    <label for="cargoActionOfficer">Action Officer</label>
                                    <input type="text" id="cargoActionOfficer" name="ActionOfficer">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>📝 Comments</h4>
                            <div class="form-field full-width">
                                <label for="cargoComments">Additional Comments</label>
                                <textarea id="cargoComments" name="Comments" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCargoModal()">
                        <i class="icon">❌</i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveCargoSeizure()">
                        <i class="icon">💾</i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modal Styles */
        .edit-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .modal-dialog {
            position: relative;
            max-width: 900px;
            max-height: 95vh;
            margin: 2vh auto;
            overflow: hidden;
            animation: slideUp 0.4s ease;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-content {
            background: white;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 24px 32px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .passenger-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .cargo-header {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .close-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 0;
            overflow-y: auto;
            flex: 1;
            max-height: calc(95vh - 160px);
        }

        .seizure-form {
            padding: 32px;
        }

        .form-section {
            margin-bottom: 32px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .form-section h4 {
            margin: 0 0 16px 0;
            color: #374151;
            font-size: 1.1rem;
            font-weight: 600;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
        }

        .form-field.full-width {
            grid-column: 1 / -1;
        }

        .form-field label {
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 0.95rem;
        }

        .form-field input,
        .form-field select,
        .form-field textarea {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
            box-sizing: border-box;
        }

        .form-field input:focus,
        .form-field select:focus,
        .form-field textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-field textarea {
            resize: vertical;
            min-height: 80px;
        }

        .required {
            color: #ef4444;
            font-weight: 600;
        }

        .modal-footer {
            padding: 24px 32px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.2);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 114, 128, 0.3);
        }

        /* Edit Button Styles */
        .edit-btn {
            padding: 6px 12px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
        }

        .edit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
            background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 0;
                max-height: 100vh;
                border-radius: 0;
            }

            .modal-header {
                padding: 20px 24px;
            }

            .seizure-form {
                padding: 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .modal-footer {
                padding: 20px 24px;
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
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
    </style>

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

        async function loadAllSeizures() {
            const container = document.getElementById('seizureListContainer');
            container.innerHTML = '<p>Loading all seizures...</p>';

            try {
                // Load both passenger and cargo seizures
                const [passengerResponse, cargoResponse] = await Promise.all([
                    fetch('api/get_recent_seizures.php'),
                    fetch('api/get_recent_cargo_seizures.php')
                ]);

                const passengerResult = await passengerResponse.json();
                const cargoResult = await cargoResponse.json();

                let allSeizures = [];

                if (passengerResult.success && passengerResult.data) {
                    allSeizures = allSeizures.concat(passengerResult.data.map(seizure => ({
                        ...seizure,
                        type: 'passenger',
                        date: seizure.SeizureDate || seizure.created_at,
                        number: seizure.SeizureNo || `PS-${seizure.PassengerSeizureID}`,
                        mainId: seizure.PassengerSeizureID
                    })));
                }

                if (cargoResult.success && cargoResult.data) {
                    allSeizures = allSeizures.concat(cargoResult.data.map(seizure => ({
                        ...seizure,
                        type: 'cargo',
                        date: seizure.SeizureDate || seizure.created_at,
                        number: seizure.SeizureNo || `CS-${seizure.CargoSeizureID}`,
                        mainId: seizure.CargoSeizureID
                    })));
                }

                // Sort by date descending
                allSeizures.sort((a, b) => {
                    const dateA = new Date(a.date || '1970-01-01');
                    const dateB = new Date(b.date || '1970-01-01');
                    return dateB - dateA;
                });

                displaySeizures(allSeizures);

            } catch (error) {
                console.error('Error loading seizures:', error);
                container.innerHTML = '<p style="color: #e74c3c;">Error loading seizures. Please try again.</p>';
            }
        }

        function displaySeizures(seizures) {
            const container = document.getElementById('seizureListContainer');

            if (seizures.length === 0) {
                container.innerHTML = '<p>No seizures found.</p>';
                return;
            }

            const tableHTML = `
                <div style="overflow-x: auto; max-height: 600px; overflow-y: auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9em; background: white;">
                        <thead style="position: sticky; top: 0; z-index: 10;">
                            <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Type</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Seizure No</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Voyage</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Date</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Importer</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Commodity/Item</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Quantity</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Unit</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Officer</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Action</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${seizures.map(seizure => `
                                <tr style="border-left: 4px solid ${seizure.type === 'passenger' ? '#f5576c' : '#fdbb2d'}; ${seizure.type === 'passenger' ? 'background: #fff5f7;' : 'background: #fffef5;'}">
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">
                                        <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: 600; color: white; background: ${seizure.type === 'passenger' ? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' : 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'};">
                                            ${seizure.type === 'passenger' ? '👤' : '📦'} ${seizure.type}
                                        </span>
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;"><strong>${seizure.number || 'N/A'}</strong></td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">
                                        ${seizure.VoyageNo || 'Voyage #' + seizure.VoyageID || 'N/A'}
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${formatDate(seizure.date)}</td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.Importer || seizure.PassengerID || 'N/A'}</td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.CommodityType || seizure.ItemDescription || seizure.CargoDescription || 'N/A'}</td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.Quantity || '-'}</td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.Unit || '-'}</td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.SeizingOfficerName || seizure.ActionOfficer || 'N/A'}</td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.ActionTaken || 'N/A'}</td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">
                                        <button onclick="editSeizure('${seizure.type}', ${seizure.mainId})" class="edit-btn" title="Edit ${seizure.type} seizure">
                                            ✏️ Edit
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = tableHTML;
        }

        function formatDate(dateString) {
            if (!dateString || dateString === 'N/A') return 'N/A';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (e) {
                return dateString;
            }
        }

        // Edit seizure functions
        async function editSeizure(type, id) {
            try {
                let endpoint;
                if (type === 'passenger') {
                    endpoint = `api/get_passenger_seizure.php?id=${id}`;
                } else if (type === 'cargo') {
                    endpoint = `api/get_cargo_seizure.php?id=${id}`;
                }

                const response = await fetch(endpoint);
                const result = await response.json();

                if (result.success && result.data) {
                    if (type === 'passenger') {
                        openPassengerModal(result.data);
                    } else if (type === 'cargo') {
                        openCargoModal(result.data);
                    }
                } else {
                    alert('Failed to load seizure details: ' + result.message);
                }
            } catch (error) {
                console.error('Error loading seizure details:', error);
                alert('Error loading seizure details. Please try again.');
            }
        }

        async function loadCommodities() {
            try {
                const response = await fetch('api/get_commodities.php');
                const result = await response.json();

                if (result.success && result.data) {
                    const passengerSelect = document.getElementById('passengerCommodityType');
                    const cargoSelect = document.getElementById('cargoCommodityType');

                    if (passengerSelect) {
                        passengerSelect.innerHTML = '<option value="">Select commodity...</option>';
                        result.data.forEach(commodity => {
                            const option = document.createElement('option');
                            option.value = commodity.CommodityType;
                            option.textContent = commodity.CommodityType;
                            passengerSelect.appendChild(option);
                        });
                    }

                    if (cargoSelect) {
                        cargoSelect.innerHTML = '<option value="">Select commodity...</option>';
                        result.data.forEach(commodity => {
                            const option = document.createElement('option');
                            option.value = commodity.CommodityType;
                            option.textContent = commodity.CommodityType;
                            cargoSelect.appendChild(option);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading commodities:', error);
            }
        }

        function openPassengerModal(seizure) {
            // Load commodities first
            loadCommodities().then(() => {
                // Populate form fields
                document.getElementById('passengerSeizureId').value = seizure.PassengerSeizureID;
                document.getElementById('passengerVoyageId').value = seizure.VoyageID;
                document.getElementById('passengerSeizureDate').value = seizure.SeizureDate || '';
                document.getElementById('passengerSeizureNo').value = seizure.SeizureNo || '';
                document.getElementById('passengerImporter').value = seizure.Importer || '';
                document.getElementById('passengerCommodityType').value = seizure.CommodityType || '';
                document.getElementById('passengerDescription').value = seizure.Description || '';
                document.getElementById('passengerDetectionMethod').value = seizure.DetectionMethod || '';
                document.getElementById('passengerQuantity').value = seizure.Quantity || '';
                document.getElementById('passengerUnit').value = seizure.Unit || '';
                document.getElementById('passengerOfficerName').value = seizure.OfficerName || '';
                document.getElementById('passengerActionTaken').value = seizure.ActionTaken || '';
                document.getElementById('passengerComments').value = seizure.Comments || '';

                document.getElementById('passengerSeizureModal').style.display = 'block';
            });
        }

        function openCargoModal(seizure) {
            // Load commodities first
            loadCommodities().then(() => {
                // Populate form fields
                document.getElementById('cargoSeizureId').value = seizure.CargoSeizureID;
                document.getElementById('cargoVoyageId').value = seizure.VoyageID;
                document.getElementById('cargoSeizureDate').value = seizure.SeizureDate || '';
                document.getElementById('cargoSeizureNo').value = seizure.SeizureNo || '';
                document.getElementById('cargoContainerRef').value = seizure.ContainerCargoRefNo || '';
                document.getElementById('cargoImporter').value = seizure.Importer || '';
                document.getElementById('cargoDescription').value = seizure.CargoDescription || '';
                document.getElementById('cargoDepotName').value = seizure.DepotName || '';
                document.getElementById('cargoCommodityType').value = seizure.CommodityType || '';
                document.getElementById('cargoDetectionMethod').value = seizure.DetectionMethod || '';
                document.getElementById('cargoQuantity').value = seizure.Quantity || '';
                document.getElementById('cargoUnit').value = seizure.Unit || '';
                document.getElementById('cargoVolume').value = seizure.VolumeKg || '';
                document.getElementById('cargoSeizingOfficerName').value = seizure.SeizingOfficerName || '';
                document.getElementById('cargoActionTaken').value = seizure.ActionTaken || '';
                document.getElementById('cargoActionOfficer').value = seizure.ActionOfficer || '';
                document.getElementById('cargoComments').value = seizure.Comments || '';

                document.getElementById('cargoSeizureModal').style.display = 'block';
            });
        }

        function closePassengerModal() {
            document.getElementById('passengerSeizureModal').style.display = 'none';
        }

        function closeCargoModal() {
            document.getElementById('cargoSeizureModal').style.display = 'none';
        }

        async function savePassengerSeizure() {
            const formData = new FormData(document.getElementById('passengerSeizureForm'));
            const seizureId = formData.get('PassengerSeizureID');
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch(`api/update_passenger_seizure.php?id=${seizureId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Passenger seizure updated successfully!');
                    closePassengerModal();
                    loadAllSeizures(); // Reload the table
                } else {
                    alert('Failed to update passenger seizure: ' + result.message);
                }
            } catch (error) {
                console.error('Error updating passenger seizure:', error);
                alert('Error updating passenger seizure. Please try again.');
            }
        }

        async function saveCargoSeizure() {
            const formData = new FormData(document.getElementById('cargoSeizureForm'));
            const seizureId = formData.get('CargoSeizureID');
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch(`api/update_cargo_seizure.php?id=${seizureId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Cargo seizure updated successfully!');
                    closeCargoModal();
                    loadAllSeizures(); // Reload the table
                } else {
                    alert('Failed to update cargo seizure: ' + result.message);
                }
            } catch (error) {
                console.error('Error updating cargo seizure:', error);
                alert('Error updating cargo seizure. Please try again.');
            }
        }

        // Load seizures when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved theme
            const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
            if (savedTheme) {
                document.body.classList.add('theme-' + savedTheme);
            }

            loadAllSeizures();
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const passengerModal = document.getElementById('passengerSeizureModal');
            const cargoModal = document.getElementById('cargoSeizureModal');

            if (event.target === passengerModal) {
                closePassengerModal();
            }
            if (event.target === cargoModal) {
                closeCargoModal();
            }
        }
    </script>
    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
