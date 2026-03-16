<?php
require_once 'api/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Management - Biosecurity System Samoa</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .locations-table {
            width: 100%;
            margin: 30px;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .locations-table th {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 18px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95em;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #667eea;
        }

        .locations-table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .locations-table tr:hover {
            background: #f7fafc;
            transition: background 0.2s ease;
        }

        .location-id-cell {
            font-weight: 700;
            color: #667eea;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .location-type-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin-right: 8px;
        }

        .type-seaport { background: #e6fffa; color: #234e52; }
        .type-airport { background: #dbeafe; color: #1e40af; }
        .type-wharf { background: #fef3c7; color: #92400e; }
        .type-terminal { background: #ddd6fe; color: #5b21b6; }
        .type-warehouse { background: #d1fae5; color: #065f46; }
        .type-land_border { background: #fed7aa; color: #9a3412; }
        .type-resort { background: #fce7f3; color: #be185d; }
        .type-naval { background: #e0e7ff; color: #3730a3; }
        .type-emergency { background: #fee2e2; color: #9f1239; }
        .type-other { background: #f3f4f6; color: #4b5563; }

        .inactive-badge {
            background: #fed7d7;
            color: #742a2a;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .location-actions {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85em;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit {
            background: #4299e1;
            color: white;
        }

        .btn-edit:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #4a5568;
            font-weight: 500;
        }

        .filter-section {
            background: white;
            padding: 25px;
            margin: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .filter-controls {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
        }

        /* Override global form-group styles for filter controls */
        .filter-controls .form-group {
            display: block;
            margin-bottom: 0;
        }

        .filter-controls .form-group label {
            display: none;
        }

        .filter-controls .form-group select,
        .filter-controls .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #fff;
        }

        .filter-controls .form-group select:focus,
        .filter-controls .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 12px 105px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            width: 100%;
            font-size: 1em;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.1em;
        }

        .search-loading {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #e2e8f0;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .clear-search {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            font-size: 16px;
            cursor: pointer;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .clear-search:hover {
            background: #f7fafc;
            color: #4a5568;
        }

        .clear-search:active {
            transform: translateY(-50%) scale(0.9);
        }

        .search-button {
            position: absolute;
            right: 50px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-size: 14px;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-button:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .search-button:active {
            transform: translateY(-50%) scale(0.95);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }

        /* Adjust clear button position when search button is present */
        .clear-search {
            right: 95px;
        }

        .no-locations {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
            font-size: 1.1em;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .quick-stat {
            text-align: center;
        }

        .quick-stat-value {
            font-size: 1.8em;
            font-weight: 700;
            color: #667eea;
        }

        .quick-stat-label {
            font-size: 0.85em;
            color: #4a5568;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .locations-table {
                margin: 15px;
                font-size: 0.9em;
            }

            .locations-table th,
            .locations-table td {
                padding: 12px 8px;
            }

            .filter-controls {
                grid-template-columns: 1fr;
            }

            @media (max-width: 480px) {
                .filter-controls {
                    grid-template-columns: 1fr;
                    gap: 10px;
                }
            }

            .location-actions {
                flex-direction: column;
            }

            .btn-small {
                font-size: 0.8em;
                padding: 6px 12px;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                margin: 15px;
            }
        }

        /* Sidebar Styles - Added to fix rendering issue */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
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
            overflow-x: hidden;
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
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
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

        .close-btn {
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

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 30px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input[readonly] {
            background: #f8f9fa !important;
            color: #6c757d !important;
            cursor: not-allowed;
        }

        .required {
            color: #e53e3e;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn-refresh {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9em;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(72, 187, 120, 0.2);
        }

        .btn-refresh:hover {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(72, 187, 120, 0.3);
        }

        .btn-refresh:active {
            transform: translateY(0);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(229, 62, 62, 0.3);
        }

        .delete-warning {
            text-align: center;
        }

        .warning-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .delete-warning h3 {
            color: #2d3748;
            margin-bottom: 15px;
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 10px;
            }

            /* Header adjustments for mobile */
            header[style*="flex"] {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .btn-refresh {
                align-self: flex-end;
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
                    <a href="voyage_management.php" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span>Voyage Management</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="location_management.php" class="nav-item active">
                        <span class="nav-icon">📍</span>
                        <span>Location Management</span>
                    </a>
                    <a href="unified_seizure.php" class="nav-item">
                        <span class="nav-icon">⚠️</span>
                        <span>Seizure Management</span>
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
            <div class="content-container">
                <header style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h1>📍 Location Management</h1>
                        <p>Manage ports, airports, and border crossing points for Samoa biosecurity operations</p>
                    </div>
                    <button type="button" class="btn-refresh" onclick="refreshLocations()" title="Refresh locations">🔄 Refresh</button>
                </header>

                <!-- Quick Stats -->
                <div class="quick-stats" id="quickStats">
                    <div class="quick-stat">
                        <div class="quick-stat-value" id="totalLocations">0</div>
                        <div class="quick-stat-label">Total</div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-value" id="activeLocations">0</div>
                        <div class="quick-stat-label">Active</div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-value" id="seaportCount">0</div>
                        <div class="quick-stat-label">Seaports</div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-value" id="airportCount">0</div>
                        <div class="quick-stat-label">Airports</div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-value" id="wharfCount">0</div>
                        <div class="quick-stat-label">Wharves</div>
                    </div>
                </div>

                <!-- Simple Filter -->
                <div class="filter-section">
                    <h2>🔍 Find Locations</h2>
                    <div class="filter-controls">
                        <div class="search-box">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="searchLocation" placeholder="Search by name, ID, region, or type..." autocomplete="off">
                            <span id="searchLoading" class="search-loading" style="display: none;">
                                <div class="loading-spinner"></div>
                            </span>
                            <button type="button" id="clearSearch" class="clear-search" style="display: none;" title="Clear search">✕</button>
                            <button type="button" id="searchButton" class="search-button" title="Search">🔍</button>
                        </div>
                        <div class="form-group">
                            <select id="regionFilter">
                                <option value="">All Regions</option>
                                <option value="Apia">Apia</option>
                                <option value="Upolu">Upolu</option>
                                <option value="Savaii">Savaii</option>
                                <option value="Inter-Island">Inter-Island</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select id="typeFilter">
                                <option value="">All Types</option>
                                <option value="seaport">🚢 Seaport</option>
                                <option value="wharf">⚓ Wharf</option>
                                <option value="terminal">🏭 Terminal</option>
                                <option value="airport">✈️ Airport</option>
                                <option value="airstrip">🛩️ Airstrip</option>
                                <option value="port">🏗️ Port</option>
                                <option value="harbor">⚓ Harbor</option>
                                <option value="jetty">🪵 Jetty</option>
                                <option value="warehouse">📦 Warehouse</option>
                                <option value="office">🏢 Office</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Locations Table -->
                <table class="locations-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Location Name</th>
                            <th>Region</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="locationsTableBody">
                        <!-- Table rows will be populated here -->
                    </tbody>
                </table>

                <div id="noLocations" class="no-locations" style="display: none;">
                    <p>No locations found matching your search criteria.</p>
                </div>
            </div>
        </main>

        <!-- Edit Location Modal -->
        <div id="editLocationModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>✏️ Edit Location</h2>
                    <button class="close-btn" onclick="closeEditModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editLocationForm">
                        <input type="hidden" id="editLocationId" name="location_id">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="editLocationName">Location Name <span class="required">*</span></label>
                                <input type="text" id="editLocationName" name="location_name" required>
                            </div>
                            <div class="form-group">
                                <label for="editLocationIdDisplay">Location ID</label>
                                <input type="text" id="editLocationIdDisplay" readonly style="background: #f8f9fa; color: #6c757d;">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="editLocationRegion">Region <span class="required">*</span></label>
                                <select id="editLocationRegion" name="region" required>
                                    <option value="">Select Region...</option>
                                    <option value="Apia">Apia</option>
                                    <option value="Tuamasaga">Tuamasaga</option>
                                    <option value="Aana">Aana</option>
                                    <option value="Atua">Atua</option>
                                    <option value="Va'a-o-Fonoti">Va'a-o-Fonoti</option>
                                    <option value="Fa'asaleleaga">Fa'asaleleaga</option>
                                    <option value="Gaga'emauga">Gaga'emauga</option>
                                    <option value="Gagaifomauga">Gagaifomauga</option>
                                    <option value="Palauli">Palauli</option>
                                    <option value="Satupa'itea">Satupa'itea</option>
                                    <option value="Vaisigano">Vaisigano</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="editLocationType">Location Type <span class="required">*</span></label>
                                <select id="editLocationType" name="location_type" required>
                                    <option value="">Select Type...</option>
                                    <option value="seaport">🚢 Seaport</option>
                                    <option value="wharf">⚓ Wharf</option>
                                    <option value="terminal">🏭 Terminal</option>
                                    <option value="airport">✈️ Airport</option>
                                    <option value="airstrip">🛩️ Airstrip</option>
                                    <option value="port">🏗️ Port</option>
                                    <option value="harbor">⚓ Harbor</option>
                                    <option value="jetty">🪵 Jetty</option>
                                    <option value="warehouse">📦 Warehouse</option>
                                    <option value="office">🏢 Office</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="editLocationActive" name="is_active" checked>
                                    Active Status
                                </label>
                                <small><em>Uncheck to mark this location as inactive</em></small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" form="editLocationForm" class="btn btn-primary">💾 Save Changes</button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteConfirmModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);">
                    <h2>🗑️ Confirm Delete</h2>
                    <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="delete-warning">
                        <div class="warning-icon">⚠️</div>
                        <h3>Are you sure you want to delete this location?</h3>
                        <div id="deleteLocationDetails" style="margin: 20px 0; padding: 15px; background: #fff5f5; border-left: 4px solid #e53e3e; border-radius: 4px;">
                            <!-- Location details will be populated here -->
                        </div>
                        <div id="deleteWarningMessage" style="color: #e53e3e; font-weight: 600; margin-top: 10px; display: none;">
                            <!-- Warning message will be populated here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <span>🗑️</span> Delete Location
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global cache for location data
        let cachedLocations = null;
        let isLoadingLocations = false;

        // Load saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
            if (savedTheme) {
                document.body.classList.add('theme-' + savedTheme);
            }
            loadLocationsData();
            loadStats();
        });

        function getLocationTypeIcon(locationType) {
            const icons = {
                'seaport': '⚓',
                'airport': '✈️',
                'wharf': '🏗️',
                'terminal': '🏢',
                'warehouse': '📦',
                'land_border': '🛃',
                'resort': '🏨',
                'naval': '⚔️',
                'emergency': '🚨',
                'other': '📍'
            };
            return icons[locationType] || '📍';
        }

        function formatLocationType(locationType) {
            return locationType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function loadLocationsData() {
            if (isLoadingLocations) return Promise.resolve();

            isLoadingLocations = true;
            return fetch('api/get_locations.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cachedLocations = data.data;
                        displayLocations();
                    } else {
                        console.error('Failed to load locations:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading locations:', error);
                })
                .finally(() => {
                    isLoadingLocations = false;
                });
        }

        function refreshLocationsData() {
            cachedLocations = null;
            return loadLocationsData();
        }

        function refreshLocations() {
            console.log('Refresh button clicked'); // Debug message

            // Clear any existing filters
            document.getElementById('searchLocation').value = '';
            document.getElementById('regionFilter').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('clearSearch').style.display = 'none';

            // Clear the search input padding
            document.getElementById('searchLocation').style.paddingRight = '105px';

            // Show loading state
            const refreshBtn = document.querySelector('.btn-refresh');
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '🔄 Refreshing...';
            refreshBtn.disabled = true;

            // Reload data and refresh UI
            refreshLocationsData().then(() => {
                loadStats();
                refreshBtn.innerHTML = originalText;
                refreshBtn.disabled = false;
                console.log('Refresh completed successfully');
            }).catch(error => {
                console.error('Error refreshing locations:', error);
                refreshBtn.innerHTML = originalText;
                refreshBtn.disabled = false;
                alert('Error refreshing locations. Please try again.');
            });
        }

        function loadLocations() {
            if (cachedLocations) {
                displayLocations();
                return Promise.resolve();
            } else {
                return loadLocationsData();
            }
        }

        function displayLocations(locations = null) {
            const tbody = document.getElementById('locationsTableBody');
            const searchTerm = document.getElementById('searchLocation').value.toLowerCase();
            const regionFilter = document.getElementById('regionFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const noLocationsDiv = document.getElementById('noLocations');

            // Use cached data if no locations provided
            const sourceLocations = locations || cachedLocations || [];

            // Start with all locations
            let filteredLocations = [...sourceLocations];

            // Apply search filter
            if (searchTerm) {
                filteredLocations = filteredLocations.filter(location =>
                    location.location_name.toLowerCase().includes(searchTerm) ||
                    location.location_id.toLowerCase().includes(searchTerm) ||
                    location.region.toLowerCase().includes(searchTerm) ||
                    location.location_type.toLowerCase().includes(searchTerm)
                );
            }

            // Apply region filter
            if (regionFilter) {
                filteredLocations = filteredLocations.filter(location =>
                    location.region === regionFilter
                );
            }

            // Apply type filter
            if (typeFilter) {
                filteredLocations = filteredLocations.filter(location =>
                    location.location_type === typeFilter
                );
            }

            // Clear existing content
            tbody.innerHTML = '';

            // Show/hide no locations message
            if (filteredLocations.length === 0) {
                noLocationsDiv.style.display = 'block';
                return;
            } else {
                noLocationsDiv.style.display = 'none';
            }

            // Build and insert table rows
            const tableRows = filteredLocations.map(location => {
                const typeIcon = getLocationTypeIcon(location.location_type);
                const formattedType = formatLocationType(location.location_type);
                const statusBadge = location.is_active ?
                    '<span class="location-type-badge type-warehouse">✅ Active</span>' :
                    '<span class="inactive-badge">🚫 Inactive</span>';

                return `
                    <tr>
                        <td class="location-id-cell">${location.location_id}</td>
                        <td><strong>${location.location_name}</strong></td>
                        <td>📍 ${location.region}</td>
                        <td><span class="location-type-badge type-${location.location_type}">
                            ${typeIcon} ${formattedType}
                        </span></td>
                        <td>${statusBadge}</td>
                        <td class="location-actions">
                            <button class="btn-small btn-edit" onclick="editLocation('${location.location_id}')">✏️ Edit</button>
                            <button class="btn-small btn-delete" onclick="deleteLocation('${location.location_id}')">🗑️ Delete</button>
                        </td>
                    </tr>
                `;
            }).join('');

            tbody.innerHTML = tableRows;
        }

        function loadStats() {
            fetch('api/get_locations.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const locations = data.data;
                        const stats = {
                            total: locations.length,
                            active: locations.filter(l => l.is_active).length,
                            seaport: locations.filter(l => l.location_type === 'seaport').length,
                            airport: locations.filter(l => l.location_type === 'airport').length,
                            wharf: locations.filter(l => l.location_type === 'wharf').length
                        };

                        document.getElementById('totalLocations').textContent = stats.total;
                        document.getElementById('activeLocations').textContent = stats.active;
                        document.getElementById('seaportCount').textContent = stats.seaport;
                        document.getElementById('airportCount').textContent = stats.airport;
                        document.getElementById('wharfCount').textContent = stats.wharf;
                    }
                });
        }

        function editLocation(locationId) {
            // Fetch location data from API
            fetch(`api/edit_location.php?id=${locationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate form with location data
                        const location = data.data;
                        document.getElementById('editLocationId').value = location.location_id;
                        document.getElementById('editLocationIdDisplay').value = location.location_id;
                        document.getElementById('editLocationName').value = location.location_name;
                        document.getElementById('editLocationRegion').value = location.region;
                        document.getElementById('editLocationType').value = location.location_type;
                        document.getElementById('editLocationActive').checked = location.is_active;

                        // Show modal
                        document.getElementById('editLocationModal').style.display = 'flex';
                    } else {
                        alert(`Error loading location: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Error loading location:', error);
                    alert('Error loading location data. Please try again.');
                });
        }

        function closeEditModal() {
            document.getElementById('editLocationModal').style.display = 'none';
            // Reset form
            document.getElementById('editLocationForm').reset();
        }

        // Edit Location Form submission
        document.getElementById('editLocationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                location_id: formData.get('location_id'),
                location_name: formData.get('location_name'),
                region: formData.get('region'),
                location_type: formData.get('location_type'),
                is_active: document.getElementById('editLocationActive').checked
            };

            // Send update request
            fetch('api/edit_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Location updated successfully!');
                    closeEditModal();
                    // Refresh cached data and reload locations
                    refreshLocationsData().then(() => {
                        loadStats();
                    });
                } else {
                    alert(`Error updating location: ${result.message}`);
                }
            })
            .catch(error => {
                console.error('Error updating location:', error);
                alert('Error updating location. Please try again.');
            });
        });

        function deleteLocation(locationId) {
            // Fetch location details to show in confirmation dialog
            fetch(`api/edit_location.php?id=${locationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const location = data.data;

                        // Populate location details in the confirmation modal
                        document.getElementById('deleteLocationDetails').innerHTML = `
                            <strong>Location ID:</strong> ${location.location_id}<br>
                            <strong>Location Name:</strong> ${location.location_name}<br>
                            <strong>Region:</strong> ${location.region}<br>
                            <strong>Type:</strong> ${formatLocationType(location.location_type)}<br>
                            <strong>Status:</strong> ${location.is_active ? '✅ Active' : '🚫 Inactive'}
                        `;

                        // Store location ID for deletion
                        document.getElementById('confirmDeleteBtn').dataset.locationId = locationId;

                        // Show confirmation modal
                        document.getElementById('deleteConfirmModal').style.display = 'flex';
                        document.getElementById('deleteWarningMessage').style.display = 'none';
                        document.getElementById('confirmDeleteBtn').style.display = 'inline-flex';
                    } else {
                        alert(`Error loading location details: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Error loading location details:', error);
                    alert('Error loading location details. Please try again.');
                });
        }

        function closeDeleteModal() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
            // Clear the delete warning message
            document.getElementById('deleteWarningMessage').style.display = 'none';
            document.getElementById('confirmDeleteBtn').style.display = 'inline-flex';
        }

        // Confirm delete button click handler
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const locationId = this.dataset.locationId;

            if (!locationId) return;

            // Show loading state
            this.innerHTML = '<span>🔄</span> Deleting...';
            this.disabled = true;

            // Send delete request
            fetch('api/delete_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: locationId })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    closeDeleteModal();
                    // Refresh cached data and reload locations
                    refreshLocationsData().then(() => {
                        loadStats();
                    });
                } else {
                    // Check if there are references preventing deletion
                    if (result.has_references) {
                        let warningMessage = result.message + '\n\nYou must remove or update these references before deleting this location.';
                        document.getElementById('deleteWarningMessage').innerHTML = `
                            <strong>⚠️ Cannot Delete Location</strong><br>
                            <div style="text-align: left; margin-top: 10px;">
                                ${warningMessage.replace(/\n/g, '<br>')}
                            </div>
                        `;
                        document.getElementById('deleteWarningMessage').style.display = 'block';
                        document.getElementById('confirmDeleteBtn').style.display = 'none';
                    } else {
                        alert(`Error deleting location: ${result.message}`);
                    }
                }
            })
            .catch(error => {
                console.error('Error deleting location:', error);
                alert('Error deleting location. Please try again.');
            })
            .finally(() => {
                // Reset button state
                this.innerHTML = '<span>🗑️</span> Delete Location';
                this.disabled = false;
            });
        });

        // Add new location form submission
        document.getElementById('addLocationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const locationName = document.getElementById('locationName').value;
            const region = document.getElementById('locationRegion').value;
            const locationType = document.getElementById('locationType').value;

            alert(`Add functionality would be implemented here. This would send a POST request to create a new location: ${locationName} (${locationType}) in ${region} region.`);

            // Reset form
            this.reset();
        });

        // Debounce function to prevent excessive API calls during typing
        let searchTimeout;
        function debounceSearch(callback, delay) {
            return function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(callback, delay);
            };
        }

        // Show search loading indicator
        function showSearchLoading() {
            const searchInput = document.getElementById('searchLocation');
            const loadingIndicator = document.getElementById('searchLoading');
            const clearButton = document.getElementById('clearSearch');

            if (searchInput.value.trim()) {
                loadingIndicator.style.display = 'inline-flex';
                clearButton.style.display = 'none';
                searchInput.style.paddingRight = '105px';
            }
        }

        // Hide search loading indicator
        function hideSearchLoading() {
            const loadingIndicator = document.getElementById('searchLoading');
            const searchInput = document.getElementById('searchLocation');
            const clearButton = document.getElementById('clearSearch');

            loadingIndicator.style.display = 'none';

            // Show clear button if there's text, hide if empty
            if (searchInput.value.trim()) {
                clearButton.style.display = 'flex';
                searchInput.style.paddingRight = '105px';
            } else {
                clearButton.style.display = 'none';
                searchInput.style.paddingRight = '105px';
            }
        }

        // Update clear button visibility based on search input
        function updateClearButton() {
            const searchInput = document.getElementById('searchLocation');
            const clearButton = document.getElementById('clearSearch');
            const loadingIndicator = document.getElementById('searchLoading');

            if (searchInput.value.trim()) {
                if (loadingIndicator.style.display === 'none') {
                    clearButton.style.display = 'flex';
                    searchInput.style.paddingRight = '105px';
                }
            } else {
                clearButton.style.display = 'none';
                searchInput.style.paddingRight = '105px';
            }
        }

        // Clear search functionality
        function clearSearch() {
            const searchInput = document.getElementById('searchLocation');
            const clearButton = document.getElementById('clearSearch');
            const loadingIndicator = document.getElementById('searchLoading');

            searchInput.value = '';
            clearButton.style.display = 'none';
            loadingIndicator.style.display = 'none';
            searchInput.style.paddingRight = '105px';
            searchInput.focus();

            // Trigger instant search with empty value
            if (cachedLocations) {
                displayLocations();
            } else {
                loadLocations();
            }
        }

        // Instant filtering for search (no API calls)
        function performInstantSearch() {
            if (cachedLocations) {
                showSearchLoading();
                // Small delay to show loading spinner for better UX
                setTimeout(() => {
                    displayLocations();
                    hideSearchLoading();
                }, 100);
            } else {
                // If no cached data, load it first
                loadLocationsData().then(() => {
                    displayLocations();
                    hideSearchLoading();
                });
            }
        }

        // Enhanced loadLocations with loading indicators
        function loadLocationsWithLoading() {
            showSearchLoading();
            loadLocations().finally(() => {
                hideSearchLoading();
            });
        }

        // Apply filters - handles both search box and dropdown filters
        function applyFilters() {
            if (cachedLocations) {
                displayLocations();
            } else {
                loadLocations().then(() => {
                    displayLocations();
                });
            }
        }

        // Search and filter event listeners with debouncing for search
        const debouncedSearch = debounceSearch(performInstantSearch, 150);

        document.getElementById('searchLocation').addEventListener('input', function() {
            updateClearButton();
            debouncedSearch();
        });
        document.getElementById('regionFilter').addEventListener('change', applyFilters);
        document.getElementById('typeFilter').addEventListener('change', applyFilters);
        document.getElementById('clearSearch').addEventListener('click', clearSearch);
        document.getElementById('searchButton').addEventListener('click', function() {
            // Trigger search immediately when button is clicked
            performInstantSearch();
        });

        // Also update clear button on paste events
        document.getElementById('searchLocation').addEventListener('paste', function() {
            setTimeout(updateClearButton, 10);
        });

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

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            const editModal = document.getElementById('editLocationModal');
            const deleteModal = document.getElementById('deleteConfirmModal');
            const modalContents = document.querySelectorAll('.modal-content');

            let isClickInsideModal = false;
            modalContents.forEach(content => {
                if (content.contains(event.target)) {
                    isClickInsideModal = true;
                }
            });

            // Handle edit modal
            if (editModal && editModal.style.display === 'flex' && !isClickInsideModal) {
                closeEditModal();
            }

            // Handle delete modal
            if (deleteModal && deleteModal.style.display === 'flex' && !isClickInsideModal) {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>