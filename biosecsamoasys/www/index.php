<?php
require_once 'api/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biosecurity System - Samoa</title>
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
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .welcome-header {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .welcome-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .welcome-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .dashboard-content {
            padding: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
        }

        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #718096;
            font-size: 0.95em;
        }

        .quick-actions {
            background: #f7fafc;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .quick-actions h2 {
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #2d3748;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            border-color: #667eea;
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .action-btn-icon {
            font-size: 1.5em;
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
            }

            .mobile-menu-toggle {
                display: block;
            }

            .welcome-header h1 {
                font-size: 1.8em;
            }

            .dashboard-content {
                padding: 20px;
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
                    <a href="index.php" class="nav-item active">
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
                    <a href="voyage_management.php" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span>Voyage Management</span>
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
            <div class="content-container">
                <div class="welcome-header">
                    <h1>🚢 Biosecurity Information System</h1>
                    <p>Samoa Vessel Voyage and Passenger Inspection Management</p>
                </div>

                <div class="dashboard-content">
                    <!-- Statistics Grid -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">🚢</div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">Total Voyages</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🔍</div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">Inspections</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">⚠️</div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">Seizures</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">Cargo Releases</div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h2>Quick Actions</h2>
                        <div class="action-buttons">
                            <a href="voyagement.php" class="action-btn">
                                <span class="action-btn-icon">🚢</span>
                                <span>New Voyage</span>
                            </a>
                            <a href="voyagement.php" class="action-btn">
                                <span class="action-btn-icon">🔍</span>
                                <span>Passenger Inspection</span>
                            </a>
                            <a href="voyagement.php" class="action-btn">
                                <span class="action-btn-icon">⚠️</span>
                                <span>Record Seizure</span>
                            </a>
                            <a href="voyagement.php" class="action-btn">
                                <span class="action-btn-icon">📦</span>
                                <span>Cargo Release</span>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity Placeholder -->
                    <div style="background: #f7fafc; padding: 30px; border-radius: 10px;">
                        <h2 style="color: #2d3748; margin-bottom: 15px;">Recent Activity</h2>
                        <p style="color: #718096;">No recent activity to display. Start by creating a new voyage or inspection.</p>
                    </div>
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

        // Load dashboard statistics
        function loadDashboardStats() {
            // Fetch voyage count
            fetch('api/get_voyages.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        document.querySelector('.stats-grid .stat-card:nth-child(1) .stat-value').textContent = data.data.length;
                    }
                });

            // Fetch inspection count
            fetch('api/get_recent_inspections.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        document.querySelector('.stats-grid .stat-card:nth-child(2) .stat-value').textContent = data.data.length;
                    }
                });

            // Fetch seizure count
            fetch('api/get_recent_seizures.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        document.querySelector('.stats-grid .stat-card:nth-child(3) .stat-value').textContent = data.data.length;
                    }
                });

            // Fetch cargo release count
            fetch('api/get_recent_releases.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        document.querySelector('.stats-grid .stat-card:nth-child(4) .stat-value').textContent = data.data.length;
                    }
                });
        }

        // Load saved theme on page load and stats
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
            if (savedTheme) {
                document.body.classList.add('theme-' + savedTheme);
            }
            loadDashboardStats();
        });
    </script>
</body>
</html>
