<?php
require_once 'api/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Biosecurity System</title>
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

        .settings-header {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
            padding: 40px;
        }

        .settings-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .settings-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .settings-content {
            padding: 40px;
        }

        .settings-section {
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 2px solid #e2e8f0;
        }

        .settings-section:last-child {
            border-bottom: none;
        }

        .settings-section h2 {
            color: #2d3748;
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        .settings-section p {
            color: #718096;
            margin-bottom: 20px;
        }

        .theme-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .theme-card {
            background: #f7fafc;
            border: 3px solid #e2e8f0;
            border-radius: 10px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .theme-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .theme-card.active {
            border-color: #667eea;
            background: #eef2ff;
        }

        .theme-preview {
            width: 100%;
            height: 120px;
            border-radius: 8px;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
        }

        .theme-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: rgba(0, 0, 0, 0.1);
        }

        .theme-info h3 {
            color: #2d3748;
            font-size: 1.3em;
            margin-bottom: 5px;
        }

        .theme-info p {
            color: #718096;
            font-size: 0.95em;
            margin: 0;
        }

        .theme-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
            margin-top: 10px;
        }

        .user-mgmt-card {
            background: #f7fafc;
            border: 3px solid #e2e8f0;
            border-radius: 10px;
            padding: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .user-mgmt-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .user-mgmt-icon {
            font-size: 4em;
            color: #667eea;
        }

        .user-mgmt-info {
            flex: 1;
        }

        .user-mgmt-info h3 {
            color: #2d3748;
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        .user-mgmt-info p {
            color: #718096;
            font-size: 1.05em;
            margin: 0;
            line-height: 1.6;
        }

        .user-mgmt-btn {
            background: #667eea;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }

        .user-mgmt-btn:hover {
            background: #5568d3;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
        }

        /* Table Management Styles */
        .table-mgmt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .table-mgmt-card {
            background: #f7fafc;
            border: 3px solid #e2e8f0;
            border-radius: 10px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: relative;
        }

        .table-mgmt-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .table-mgmt-card.coming-soon {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .table-mgmt-card.coming-soon:hover {
            transform: none;
            box-shadow: none;
            border-color: #e2e8f0;
        }

        .table-mgmt-card:not(.coming-soon) .table-mgmt-btn {
            display: flex;
        }

        .table-mgmt-icon {
            font-size: 3em;
            color: #667eea;
            text-align: center;
        }

        .table-mgmt-info h3 {
            color: #2d3748;
            font-size: 1.6em;
            margin-bottom: 10px;
        }

        .table-mgmt-info p {
            color: #718096;
            font-size: 1em;
            margin: 0;
            line-height: 1.5;
        }

        .table-mgmt-btn {
            background: #667eea;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
            text-align: center;
            margin-top: auto;
        }

        .table-mgmt-btn:hover {
            background: #5568d3;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
        }

        .coming-soon-badge {
            background: #e53e3e;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
            display: inline-block;
            position: absolute;
            top: 15px;
            right: 15px;
        }

        body.theme-green .table-mgmt-card:hover {
            border-color: #27ae60;
        }

        body.theme-green .table-mgmt-icon {
            color: #27ae60;
        }

        body.theme-green .table-mgmt-btn {
            background: #27ae60;
        }

        body.theme-green .user-mgmt-card:hover {
            border-color: #27ae60;
        }

        body.theme-green .user-mgmt-icon {
            color: #27ae60;
        }

        body.theme-green .user-mgmt-btn {
            background: #27ae60;
        }

        body.theme-blue .table-mgmt-card:hover {
            border-color: #3498db;
        }

        body.theme-blue .table-mgmt-icon {
            color: #3498db;
        }

        body.theme-blue .table-mgmt-btn {
            background: #3498db;
        }

        body.theme-blue .user-mgmt-card:hover {
            border-color: #3498db;
        }

        body.theme-blue .user-mgmt-icon {
            color: #3498db;
        }

        body.theme-blue .user-mgmt-btn {
            background: #3498db;
        }

        body.theme-office .table-mgmt-card:hover {
            border-color: #6c757d;
        }

        body.theme-office .table-mgmt-icon {
            color: #6c757d;
        }

        body.theme-office .table-mgmt-btn {
            background: #6c757d;
        }

        body.theme-office .user-mgmt-card:hover {
            border-color: #6c757d;
        }

        body.theme-office .user-mgmt-icon {
            color: #6c757d;
        }

        body.theme-office .user-mgmt-btn {
            background: #6c757d;
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

        /* Theme Styles */
        body.theme-green {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

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

        body.theme-green .theme-card.active {
            border-color: #27ae60;
        }

        body.theme-green .theme-badge {
            background: #27ae60;
        }

        body.theme-blue {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
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

        body.theme-blue .theme-card.active {
            border-color: #3498db;
        }

        body.theme-blue .theme-badge {
            background: #3498db;
        }

        body.theme-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body.theme-purple .app-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body.theme-purple .nav-item.active {
            background: rgba(102, 126, 234, 0.3);
            border-left-color: #667eea;
        }

        body.theme-purple .nav-item:hover {
            background: rgba(102, 126, 234, 0.2);
            border-left-color: #667eea;
        }

        /* Professional Office Theme */
        body.theme-office {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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

        body.theme-office .theme-card.active {
            border-color: #6c757d;
        }

        body.theme-office .theme-badge {
            background: #6c757d;
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
            }

            .mobile-menu-toggle {
                display: block;
            }

            .settings-header h1 {
                font-size: 1.8em;
            }

            .settings-content {
                padding: 20px;
            }

            .theme-grid {
                grid-template-columns: 1fr;
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
                    <a href="voyage_management.php" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span>Voyage Management</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="unified_seizure.php" class="nav-item">
                        <span class="nav-icon">⚠️</span>
                        <span>Seizure Management</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">📊</span>
                        <span>Reports</span>
                    </a>
                    <a href="settings.php" class="nav-item active">
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
                <div class="settings-header">
                    <h1>⚙️ Settings</h1>
                    <p>Customize your biosecurity system experience</p>
                </div>

                <div class="settings-content">
                    <!-- Theme Settings -->
                    <div class="settings-section">
                        <h2>🎨 Theme Selection</h2>
                        <p>Choose a color theme that suits your preference. Your selection will be saved automatically.</p>

                        <div class="theme-grid">
                            <!-- Clear Skies Theme -->
                            <div class="theme-card active" onclick="setTheme('purple')" id="theme-card-purple">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                </div>
                                <div class="theme-info">
                                    <h3>🌤️ Clear Skies</h3>
                                    <p>Professional purple gradient theme with modern aesthetics</p>
                                    <span class="theme-badge" style="display: none;">Active</span>
                                </div>
                            </div>

                            <!-- Green Environment Theme -->
                            <div class="theme-card" onclick="setTheme('green')" id="theme-card-green">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                                </div>
                                <div class="theme-info">
                                    <h3>🌿 Green Environment</h3>
                                    <p>Natural green theme perfect for biosecurity and environmental work</p>
                                    <span class="theme-badge" style="display: none;">Active</span>
                                </div>
                            </div>

                            <!-- Blue Ocean Theme -->
                            <div class="theme-card" onclick="setTheme('blue')" id="theme-card-blue">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                                </div>
                                <div class="theme-info">
                                    <h3>🌊 Blue Ocean</h3>
                                    <p>Calming blue theme inspired by maritime operations</p>
                                    <span class="theme-badge" style="display: none;">Active</span>
                                </div>
                            </div>

                            <!-- Professional Office Theme -->
                            <div class="theme-card" onclick="setTheme('office')" id="theme-card-office">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                </div>
                                <div class="theme-info">
                                    <h3>🏢 Professional Office</h3>
                                    <p>Clean gray theme with professional office aesthetics and muted icons</p>
                                    <span class="theme-badge" style="display: none;">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Table Management Section -->
                <div class="settings-section">
                    <h2>📋 Table Management</h2>
                    <p>Manage reference data tables used throughout the system</p>

                    <div class="table-mgmt-grid">
                        <!-- Port of Entry Management -->
                        <div class="table-mgmt-card">
                            <div class="table-mgmt-icon">🚢</div>
                            <div class="table-mgmt-info">
                                <h3>Port of Entry Management</h3>
                                <p>Add, edit, and remove ports where vessels can arrive. This affects voyage forms and reporting.</p>
                            </div>
                            <a href="port_of_entry_management.php" class="table-mgmt-btn">
                                Manage Ports
                                <span style="font-size: 1.3em;">→</span>
                            </a>
                        </div>

                        <!-- Future Table Management Cards -->
                        <div class="table-mgmt-card coming-soon">
                            <div class="table-mgmt-icon">🛳️</div>
                            <div class="table-mgmt-info">
                                <h3>Vessel Management</h3>
                                <p>Vessel registry management (coming soon)</p>
                            </div>
                            <div class="coming-soon-badge">Coming Soon</div>
                        </div>

                        <div class="table-mgmt-card coming-soon">
                            <div class="table-mgmt-icon">📦</div>
                            <div class="table-mgmt-info">
                                <h3>Cargo Types Management</h3>
                                <p>Manage cargo and container types (coming soon)</p>
                            </div>
                            <div class="coming-soon-badge">Coming Soon</div>
                        </div>
                    </div>
                </div>

                <!-- User Management Section -->
                <div class="settings-section">
                    <h2>👥 User Management</h2>
                    <p>Manage system users, roles, and permissions</p>

                    <a href="user_management.php" class="user-mgmt-card">
                        <div class="user-mgmt-icon">👤</div>
                        <div class="user-mgmt-info">
                            <h3>User Administration</h3>
                            <p>Add, edit, or remove users. Assign roles and manage access levels for biosecurity officers and administrators.</p>
                        </div>
                        <div class="user-mgmt-btn">
                            Access User Management
                            <span style="font-size: 1.3em;">→</span>
                        </div>
                    </a>
                </div>

                <!-- Future Settings Placeholder -->
                <div class="settings-section">
                    <h2>🔔 Notifications</h2>
                    <p>Notification settings coming soon...</p>
                </div>

                <div class="settings-section">
                    <h2>👤 User Preferences</h2>
                    <p>User preference settings coming soon...</p>
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

        // Theme switching functionality
        function setTheme(theme) {
            // Remove all theme classes
            document.body.classList.remove('theme-purple', 'theme-green', 'theme-blue', 'theme-office');

            // Add selected theme class
            document.body.classList.add('theme-' + theme);

            // Update active state on theme cards
            document.querySelectorAll('.theme-card').forEach(card => {
                card.classList.remove('active');
                card.querySelector('.theme-badge').style.display = 'none';
            });

            const activeCard = document.getElementById('theme-card-' + theme);
            activeCard.classList.add('active');
            activeCard.querySelector('.theme-badge').style.display = 'inline-block';

            // Save theme preference to localStorage
            localStorage.setItem('selectedTheme', theme);

            // Show success message
            showNotification('Theme changed successfully!');
        }

        // Load saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
            setTheme(savedTheme);
        });

        // Simple notification function
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #27ae60; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 10000; animation: slideIn 0.3s ease;';
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }
    </script>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
</body>
</html>
