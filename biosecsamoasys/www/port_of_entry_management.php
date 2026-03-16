<?php
require_once 'api/auth_check.php';
require_once 'api/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Port of Entry Management - Biosecurity System</title>
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

        .header {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: white;
            padding: 40px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content-section {
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

        .add-btn {
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

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.5);
        }

        .ports-table {
            border-collapse: collapse;
            width: 100%;
            background: #fafbfc;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }

        .ports-table th,
        .ports-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .ports-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            font-size: 0.95em;
        }

        .ports-table tr:last-child td {
            border-bottom: none;
        }

        .ports-table tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .edit-btn,
        .delete-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background: #27ae60;
            color: white;
        }

        .edit-btn:hover {
            background: #219a52;
            transform: translateY(-1px);
        }

        .delete-btn {
            background: #e53e3e;
            color: white;
        }

        .delete-btn:hover {
            background: #c53030;
            transform: translateY(-1px);
        }

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
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
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
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .error-message {
            color: #e53e3e;
            font-size: 0.9em;
            margin-top: 5px;
            display: none;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
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
            background: #e2e8f0;
            color: #2d3748;
        }

        .modal-btn.cancel:hover {
            background: #cbd5e0;
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

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #718096;
        }

        .loading::after {
            content: ' ⏳';
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
            font-size: 1.2em;
        }

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
        }

        .notification.success {
            background: #27ae60;
        }

        .notification.error {
            background: #e53e3e;
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

        /* Theme support for different themes */
        body.theme-green {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        body.theme-green .app-wrapper {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        body.theme-blue {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        body.theme-blue .app-wrapper {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        body.theme-office {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }

        body.theme-office .app-wrapper {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                flex-direction: column;
                align-items: stretch;
            }

            .content-section {
                padding: 20px;
            }

            .modal-content {
                padding: 25px;
                width: 95%;
            }

            .ports-table {
                font-size: 0.9em;
            }

            .action-buttons {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 5px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                transform: translate(-50%, -50%) scale(0.9);
                opacity: 0;
            }
            to {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
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
                    <div class="nav-section-title">Navigation</div>
                    <a href="index.php" class="nav-item">
                        <span class="nav-icon">🏠</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="voyage_management.php" class="nav-item">
                        <span class="nav-icon">📋</span>
                        <span>Voyage Management</span>
                    </a>
                    <a href="settings.php" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span>Settings</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="user_management.php" class="nav-item">
                        <span class="nav-icon">👥</span>
                        <span>User Management</span>
                    </a>
                    <a href="port_of_entry_management.php" class="nav-item active">
                        <span class="nav-icon">⚓</span>
                        <span>Port Management</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-container">
                <!-- Header Section -->
                <div class="header">
                    <h1>⚓ Port of Entry Management</h1>
                    <p>Manage ports where vessels can arrive and depart. These ports are referenced in voyage forms.</p>
                </div>

                <!-- Main Content -->
                <div class="content-section">
                    <!-- Action Bar -->
                    <div class="action-bar">
                        <div class="search-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search ports by name, country..." />
                        </div>
                        <button class="add-btn" onclick="openAddModal()">
                            <span>+</span>
                            <span>Add Port</span>
                        </button>
                    </div>

                    <!-- Ports Table -->
                    <table class="ports-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Port Name</th>
                                <th>Country</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="portsTableBody">
                            <tr class="loading">
                                <td colspan="4">Loading ports...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal" id="portModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Port</h2>
                <p id="modalSubtitle">Enter the port details below</p>
            </div>

            <form id="portForm">
                <input type="hidden" id="portId" name="portId" value="">

                <div class="form-group">
                    <label for="portName">Port Name *</label>
                    <input type="text" id="portName" name="portName" class="form-input" required>
                    <div class="error-message" id="portNameError"></div>
                </div>

                <div class="form-group">
                    <label for="country">Country *</label>
                    <input type="text" id="country" name="country" class="form-input" required>
                    <div class="error-message" id="countryError"></div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="modal-btn save" id="saveBtn">Add Port</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script>
        // Global variables
        let allPorts = [];
        let currentEditId = null;

        // Theme loader
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved theme
            const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
            document.body.classList.add('theme-' + savedTheme);

            // Load ports data
            loadPorts();
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

        // Load ports from API
        async function loadPorts() {
            try {
                const response = await fetch('api/get_ports.php');
                const result = await response.json();

                if (result.success) {
                    allPorts = result.data;
                    renderPortsTable(allPorts);
                } else {
                    showNotification('Error loading ports: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('Failed to load ports data', 'error');
            }
        }

        // Render ports table
        function renderPortsTable(ports) {
            const tbody = document.getElementById('portsTableBody');

            if (ports.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="no-data">No ports found</td></tr>';
                return;
            }

            const rows = ports.map(port => `
                <tr>
                    <td>${port.port_id}</td>
                    <td>${escapeHtml(port.port_name)}</td>
                    <td>${escapeHtml(port.country)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="edit-btn" onclick="editPort(${port.port_id})">Edit</button>
                            <button class="delete-btn" onclick="deletePort(${port.port_id}, '${escapeHtml(port.port_name)}')">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');

            tbody.innerHTML = rows;
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const filtered = allPorts.filter(port =>
                port.port_name.toLowerCase().includes(query) ||
                port.country.toLowerCase().includes(query)
            );
            renderPortsTable(filtered);
        });

        // Open add modal
        function openAddModal() {
            currentEditId = null;
            document.getElementById('modalTitle').textContent = 'Add New Port';
            document.getElementById('modalSubtitle').textContent = 'Enter the port details below';
            document.getElementById('saveBtn').textContent = 'Add Port';

            // Clear form
            document.getElementById('portForm').reset();
            document.getElementById('portId').value = '';

            // Clear error messages
            clearErrors();

            document.getElementById('portModal').style.display = 'block';
        }

        // Edit port
        function editPort(portId) {
            const port = allPorts.find(p => p.port_id === portId);
            if (!port) return;

            currentEditId = portId;
            document.getElementById('modalTitle').textContent = 'Edit Port';
            document.getElementById('modalSubtitle').textContent = 'Update the port information';
            document.getElementById('saveBtn').textContent = 'Update Port';

            // Populate form
            document.getElementById('portId').value = port.port_id;
            document.getElementById('portName').value = port.port_name;
            document.getElementById('country').value = port.country;

            // Clear error messages
            clearErrors();

            document.getElementById('portModal').style.display = 'block';
        }

        // Close modal
        function closeModal() {
            document.getElementById('portModal').style.display = 'none';
            currentEditId = null;
            clearErrors();
        }

        // Clear error messages
        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
        }

        // Form submission
        document.getElementById('portForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Clear previous errors
            clearErrors();

            const formData = new FormData(this);
            const isEdit = currentEditId !== null;
            const url = isEdit ? 'api/update_port.php' : 'api/create_port.php';

            try {
                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.textContent = isEdit ? 'Updating...' : 'Adding...';

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                btn.disabled = false;
                btn.textContent = isEdit ? 'Update Port' : 'Add Port';

                if (result.success) {
                    showNotification(result.message, 'success');
                    closeModal();
                    await loadPorts(); // Reload the table
                } else {
                    // Show field-specific errors
                    if (result.errors) {
                        if (result.errors.portName) {
                            document.getElementById('portNameError').textContent = result.errors.portName;
                            document.getElementById('portNameError').style.display = 'block';
                        }
                        if (result.errors.country) {
                            document.getElementById('countryError').textContent = result.errors.country;
                            document.getElementById('countryError').style.display = 'block';
                        }
                    } else {
                        showNotification(result.message, 'error');
                    }
                }
            } catch (error) {
                showNotification('Operation failed', 'error');
                document.getElementById('saveBtn').disabled = false;
                document.getElementById('saveBtn').textContent = isEdit ? 'Update Port' : 'Add Port';
            }
        });

        // Delete port
        async function deletePort(portId, portName) {
            const confirmed = confirm(`Are you sure you want to delete the port "${portName}"?\n\nThis action cannot be undone.`);

            if (!confirmed) return;

            try {
                const formData = new FormData();
                formData.append('portId', portId);

                const response = await fetch('api/delete_port.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    await loadPorts(); // Reload the table
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Delete operation failed', 'error');
            }
        }

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';

            setTimeout(() => {
                notification.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => notification.style.display = 'none', 300);
            }, 3000);
        }

        // HTML escape utility
        function escapeHtml(text) {
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