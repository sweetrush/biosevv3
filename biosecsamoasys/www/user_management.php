<?php
session_start();

// Include config file for authentication functions
require_once 'api/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check if user has admin access
if (!requireAuth('admin')) {
    header('Location: index.php');
    exit;
}

// Database connection
try {
    $pdo = getDBConnection();

    // Get all users
    $stmt = $pdo->query("SELECT user_id, username, first_name, last_name, email, access_level, department, is_active, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("User management error: " . $e->getMessage());
    $error = 'Failed to load user data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="User Management - Samoa Biosecurity System">
    <meta name="theme-color" content="#667eea">
    <title>User Management - Samoa Biosecurity System</title>
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

        .users-header {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .users-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .users-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .users-content {
            padding: 40px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .users-table th {
            background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
        }

        .users-table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .users-table tr:hover {
            background: #f7fafc;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .access-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .access-admin {
            background: #fef3c7;
            color: #92400e;
        }

        .access-officer {
            background: #dbeafe;
            color: #1e40af;
        }

        .access-viewer {
            background: #e5e7eb;
            color: #374151;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1em;
        }

        .mobile-menu-toggle {
            display: none;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #4a5568;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .user-details {
            flex: 1;
        }

        .user-details div {
            font-size: 0.9em;
        }

        .user-name {
            color: white;
            font-weight: 600;
        }

        .user-email {
            color: #a0aec0;
            font-size: 0.85em;
            margin-top: 2px;
        }

        .user-role {
            color: #a0aec0;
            font-size: 0.85em;
            margin-top: 2px;
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

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85em;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 40px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 30px;
        }

        .modal-header h2 {
            color: #2d3748;
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .modal-header p {
            color: #718096;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 600;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.9em;
            margin-top: 5px;
            display: none;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .modal-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-btn.cancel {
            background: #e5e7eb;
            color: #374151;
        }

        .modal-btn.cancel:hover {
            background: #d1d5db;
        }

        .modal-btn.save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 3px 15px rgba(102, 126, 234, 0.4);
        }

        .modal-btn.save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.5);
        }

        .modal-btn.save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Search and add section */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .search-container {
            display: flex;
            gap: 15px;
            align-items: center;
            flex: 1;
            min-width: 300px;
        }

        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .add-user-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 15px rgba(102, 126, 234, 0.4);
        }

        .add-user-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.5);
        }

        /* Status indicators */
        .user-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
        }

        .status-indicator.inactive {
            background: #ef4444;
        }

        /* Password field */
        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
        }

        .password-toggle:hover {
            color: #374151;
        }

        /* Loading state */
        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .loading::after {
            content: ' ⏳';
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
            font-size: 1.1em;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 3000;
            animation: slideInRight 0.3s ease;
            display: none;
            max-width: 400px;
        }

        .notification.success {
            background: #10b981;
        }

        .notification.error {
            background: #ef4444;
        }

        .notification.warning {
            background: #f59e0b;
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
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)); ?></div>
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></div>
                            <div class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['access_level'])); ?></div>
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
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Permits & Certificates</div>
                    <a href="import_permits.php" class="nav-item">
                        <span class="nav-icon">📋</span>
                        <span>Import Permits</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Administration</div>
                    <a href="user_management.php" class="nav-item active">
                        <span class="nav-icon">👥</span>
                        <span>User Management</span>
                    </a>
                    <a href="settings.php" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span>Settings</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                        <div class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['access_level'])); ?></div>
                    </div>
                </div>
                <a href="api/logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                    <span>🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
            ☰
        </button>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="content-container">
                <div class="users-header">
                    <h1>👥 User Management</h1>
                    <p>Manage system users and access levels</p>
                </div>

                <div class="users-content">
                    <!-- Action Bar -->
                    <div class="action-bar">
                        <div class="search-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search users by name, username, or email..." />
                        </div>
                        <button class="add-user-btn" onclick="openAddModal()">
                            <span>+</span>
                            <span>Add User</span>
                        </button>
                    </div>

                    <!-- Users Table -->
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Access Level</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr class="loading">
                                <td colspan="9">Loading users...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <p>Create a new user account with appropriate access level</p>
            </div>

            <form id="addUserForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="add_first_name">First Name *</label>
                        <input type="text" id="add_first_name" name="first_name" class="form-input" required>
                        <div class="error-message" id="add_first_name_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="add_last_name">Last Name *</label>
                        <input type="text" id="add_last_name" name="last_name" class="form-input" required>
                        <div class="error-message" id="add_last_name_error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="add_username">Username *</label>
                    <input type="text" id="add_username" name="username" class="form-input" required>
                    <div class="error-message" id="add_username_error"></div>
                </div>

                <div class="form-group">
                    <label for="add_email">Email Address *</label>
                    <input type="email" id="add_email" name="email" class="form-input" required>
                    <div class="error-message" id="add_email_error"></div>
                </div>

                <div class="form-group">
                    <label for="add_password">Password *</label>
                    <div class="password-field">
                        <input type="password" id="add_password" name="password" class="form-input" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('add_password')">👁️</button>
                    </div>
                    <div class="error-message" id="add_password_error"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="add_access_level">Access Level *</label>
                        <select id="add_access_level" name="access_level" class="form-select" required>
                            <option value="">Select Access Level</option>
                            <option value="viewer">Viewer - Read-only access</option>
                            <option value="officer">Officer - Standard user access</option>
                            <option value="admin">Administrator - Full system access</option>
                        </select>
                        <div class="error-message" id="add_access_level_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="add_department">Department</label>
                        <input type="text" id="add_department" name="department" class="form-input" placeholder="Optional">
                        <div class="error-message" id="add_department_error"></div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn cancel" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="modal-btn save" id="addUserBtn">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="editUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <p>Update user information and access level</p>
            </div>

            <form id="editUserForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="edit_user_id" name="user_id">

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_first_name">First Name *</label>
                        <input type="text" id="edit_first_name" name="first_name" class="form-input" required>
                        <div class="error-message" id="edit_first_name_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_last_name">Last Name *</label>
                        <input type="text" id="edit_last_name" name="last_name" class="form-input" required>
                        <div class="error-message" id="edit_last_name_error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_username">Username *</label>
                    <input type="text" id="edit_username" name="username" class="form-input" required>
                    <div class="error-message" id="edit_username_error"></div>
                </div>

                <div class="form-group">
                    <label for="edit_email">Email Address *</label>
                    <input type="email" id="edit_email" name="email" class="form-input" required>
                    <div class="error-message" id="edit_email_error"></div>
                </div>

                <div class="form-group">
                    <label for="edit_password">New Password</label>
                    <div class="password-field">
                        <input type="password" id="edit_password" name="password" class="form-input" placeholder="Leave blank to keep current">
                        <button type="button" class="password-toggle" onclick="togglePassword('edit_password')">👁️</button>
                    </div>
                    <div class="error-message" id="edit_password_error"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_access_level">Access Level *</label>
                        <select id="edit_access_level" name="access_level" class="form-select" required>
                            <option value="">Select Access Level</option>
                            <option value="viewer">Viewer - Read-only access</option>
                            <option value="officer">Officer - Standard user access</option>
                            <option value="admin">Administrator - Full system access</option>
                        </select>
                        <div class="error-message" id="edit_access_level_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_department">Department</label>
                        <input type="text" id="edit_department" name="department" class="form-input" placeholder="Optional">
                        <div class="error-message" id="edit_department_error"></div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="modal-btn save" id="editUserBtn">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteUserModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
                <p id="deleteUserMessage">Are you sure you want to delete this user?</p>
            </div>

            <form id="deleteUserForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="delete_user_id" name="user_id">

                <div class="modal-actions">
                    <button type="button" class="modal-btn cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="modal-btn save" id="deleteUserBtn">Delete User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script>
        // Global variables
        let allUsers = [];
        let currentEditId = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved theme
            const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
            document.body.classList.add('theme-' + savedTheme);

            // Load users data
            loadUsers();
        });

        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Load users from API
        async function loadUsers() {
            try {
                const response = await fetch('api/get_users.php');
                const result = await response.json();

                if (result.success) {
                    allUsers = result.data;
                    renderUsersTable(allUsers);
                } else {
                    showNotification('Error loading users: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('Failed to load users data', 'error');
            }
        }

        // Render users table
        function renderUsersTable(users) {
            const tbody = document.getElementById('usersTableBody');

            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="no-data">No users found</td></tr>';
                return;
            }

            const rows = users.map(user => `
                <tr>
                    <td>
                        <div class="user-avatar">
                            ${escapeHtml((user.first_name || '').charAt(0) + (user.last_name || '').charAt(0)).toUpperCase()}
                        </div>
                    </td>
                    <td><strong>${escapeHtml(user.username || '')}</strong></td>
                    <td>${escapeHtml((user.first_name || '') + ' ' + (user.last_name || ''))}</td>
                    <td>${escapeHtml(user.email || '')}</td>
                    <td>
                        <span class="access-badge access-${escapeHtml(user.access_level || '')}">
                            ${escapeHtml((user.access_level || '').charAt(0).toUpperCase() + (user.access_level || '').slice(1))}
                        </span>
                    </td>
                    <td>${escapeHtml(user.department || 'N/A')}</td>
                    <td>
                        <div class="user-status">
                            <span class="status-indicator ${user.is_active ? '' : 'inactive'}"></span>
                            <span class="status-badge status-${user.is_active ? 'active' : 'inactive'}">
                                ${user.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </td>
                    <td>${user.created_at ? new Date(user.created_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : 'N/A'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary" onclick="editUser(${user.user_id})">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.user_id}, '${escapeHtml(user.username)}')">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            tbody.innerHTML = rows;
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const filtered = allUsers.filter(user =>
                (user.username || '').toLowerCase().includes(query) ||
                (user.first_name || '').toLowerCase().includes(query) ||
                (user.last_name || '').toLowerCase().includes(query) ||
                (user.email || '').toLowerCase().includes(query) ||
                (user.department || '').toLowerCase().includes(query)
            );
            renderUsersTable(filtered);
        });

        // Password visibility toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            if (field.type === 'password') {
                field.type = 'text';
                button.textContent = '🙈';
            } else {
                field.type = 'password';
                button.textContent = '👁️';
            }
        }

        // Open Add User Modal
        function openAddModal() {
            currentEditId = null;

            // Clear form
            document.getElementById('addUserForm').reset();
            clearErrors('add');

            document.getElementById('addUserModal').style.display = 'block';
        }

        // Close Add User Modal
        function closeAddModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }

        // Edit user
        function editUser(userId) {
            const user = allUsers.find(u => u.user_id == userId);
            if (!user) return;

            currentEditId = userId;

            // Populate form
            document.getElementById('edit_user_id').value = user.user_id;
            document.getElementById('edit_first_name').value = user.first_name || '';
            document.getElementById('edit_last_name').value = user.last_name || '';
            document.getElementById('edit_username').value = user.username || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_access_level').value = user.access_level || '';
            document.getElementById('edit_department').value = user.department || '';

            // Clear errors
            clearErrors('edit');

            document.getElementById('editUserModal').style.display = 'block';
        }

        // Close Edit User Modal
        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
            currentEditId = null;
        }

        // Delete user
        function deleteUser(userId, username) {
            // Prevent self-deletion
            if (userId == '<?php echo $_SESSION['user_id']; ?>') {
                showNotification('You cannot delete your own account', 'error');
                return;
            }

            document.getElementById('delete_user_id').value = userId;
            document.getElementById('deleteUserMessage').textContent = `Are you sure you want to delete user "${username}"? This action cannot be undone.`;
            document.getElementById('deleteUserModal').style.display = 'block';
        }

        // Close Delete Modal
        function closeDeleteModal() {
            document.getElementById('deleteUserModal').style.display = 'none';
        }

        // Clear error messages
        function clearErrors(prefix) {
            const fields = ['first_name', 'last_name', 'username', 'email', 'password', 'access_level', 'department'];
            fields.forEach(field => {
                const errorEl = document.getElementById(`${prefix}_${field}_error`);
                if (errorEl) errorEl.style.display = 'none';
            });
        }

        // Show field errors
        function showFieldErrors(prefix, errors) {
            Object.keys(errors).forEach(field => {
                const errorEl = document.getElementById(`${prefix}_${field}_error`);
                if (errorEl) {
                    errorEl.textContent = errors[field];
                    errorEl.style.display = 'block';
                }
            });
        }

        // Handle Add User Form
        document.getElementById('addUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Clear previous errors
            clearErrors('add');

            const formData = new FormData(this);
            const btn = document.getElementById('addUserBtn');
            btn.disabled = true;
            btn.textContent = 'Creating...';

            try {
                const response = await fetch('api/create_user.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                btn.disabled = false;
                btn.textContent = 'Create User';

                if (result.success) {
                    showNotification(result.message, 'success');
                    closeAddModal();
                    await loadUsers(); // Reload the table
                } else {
                    // Show field-specific errors
                    if (result.errors) {
                        showFieldErrors('add', result.errors);
                    } else {
                        showNotification(result.message, 'error');
                    }
                }
            } catch (error) {
                showNotification('Failed to create user', 'error');
                btn.disabled = false;
                btn.textContent = 'Create User';
            }
        });

        // Handle Edit User Form
        document.getElementById('editUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Clear previous errors
            clearErrors('edit');

            const formData = new FormData(this);
            const btn = document.getElementById('editUserBtn');
            btn.disabled = true;
            btn.textContent = 'Updating...';

            try {
                const response = await fetch('api/update_user.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                btn.disabled = false;
                btn.textContent = 'Update User';

                if (result.success) {
                    showNotification(result.message, 'success');
                    closeEditModal();
                    await loadUsers(); // Reload the table
                } else {
                    // Show field-specific errors
                    if (result.errors) {
                        showFieldErrors('edit', result.errors);
                    } else {
                        showNotification(result.message, 'error');
                    }
                }
            } catch (error) {
                showNotification('Failed to update user', 'error');
                btn.disabled = false;
                btn.textContent = 'Update User';
            }
        });

        // Handle Delete User Form
        document.getElementById('deleteUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const btn = document.getElementById('deleteUserBtn');
            btn.disabled = true;
            btn.textContent = 'Deleting...';

            try {
                const response = await fetch('api/delete_user.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                btn.disabled = false;
                btn.textContent = 'Delete User';

                if (result.success) {
                    showNotification(result.message, result.action === 'DELETE_USER' ? 'success' : 'warning');
                    closeDeleteModal();
                    await loadUsers(); // Reload the table
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Failed to delete user', 'error');
                btn.disabled = false;
                btn.textContent = 'Delete User';
            }
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';

            setTimeout(() => {
                notification.style.display = 'none';
            }, 4000);
        }

        // HTML escape utility
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return (text + '').replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    </script>
</body>
</html>