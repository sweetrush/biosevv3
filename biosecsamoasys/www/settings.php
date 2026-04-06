<?php
define('CSS_CACHE_BUST', 2);
require_once 'api/auth_check.php';
$pageTitle = 'Settings - Samoa Biosecurity System';
$currentPage = 'settings';
?>
<style>
    .settings-header {
        background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
        color: white;
        padding: 40px;
    }

    .settings-header h1 { font-size: 2.5em; margin-bottom: 10px; }
    .settings-header p { font-size: 1.1em; opacity: 0.9; }

    .settings-content { padding: 40px; }

    .settings-section {
        margin-bottom: 40px;
        padding-bottom: 40px;
        border-bottom: 2px solid #e2e8f0;
    }

    .settings-section:last-child { border-bottom: none; }
    .settings-section h2 { color: #2d3748; font-size: 1.8em; margin-bottom: 10px; }
    .settings-section p { color: #718096; margin-bottom: 20px; }

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

    .theme-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); }
    .theme-card.active { border-color: #667eea; background: #eef2ff; }

    .theme-preview {
        width: 100%; height: 120px;
        border-radius: 8px; margin-bottom: 15px;
        position: relative; overflow: hidden;
    }

    .theme-info h3 { color: #2d3748; font-size: 1.3em; margin-bottom: 5px; }
    .theme-info p { color: #718096; font-size: 0.95em; margin: 0; }

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
        display: flex; align-items: center; gap: 25px;
    }

    .user-mgmt-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
    }

    .user-mgmt-icon { font-size: 4em; color: #667eea; }
    .user-mgmt-info { flex: 1; }
    .user-mgmt-info h3 { color: #2d3748; font-size: 1.8em; margin-bottom: 10px; }
    .user-mgmt-info p { color: #718096; font-size: 1.05em; margin: 0; line-height: 1.6; }

    .user-mgmt-btn {
        background: #667eea; color: white;
        padding: 12px 25px; border-radius: 8px;
        font-size: 1.1em; font-weight: 600;
        text-decoration: none; display: inline-flex; align-items: center; gap: 10px;
        transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
    }

    .user-mgmt-btn:hover { background: #5568d3; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); transform: translateY(-2px); }

    .table-mgmt-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px; margin-top: 20px;
    }

    .table-mgmt-card {
        background: #f7fafc;
        border: 3px solid #e2e8f0;
        border-radius: 10px;
        padding: 25px; cursor: pointer;
        transition: all 0.3s ease;
        display: flex; flex-direction: column; gap: 20px;
        position: relative;
    }

    .table-mgmt-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-color: #667eea; }
    .table-mgmt-card.coming-soon { opacity: 0.7; cursor: not-allowed; }
    .table-mgmt-card.coming-soon:hover { transform: none; box-shadow: none; border-color: #e2e8f0; }
    .table-mgmt-card:not(.coming-soon) .table-mgmt-btn { display: flex; }

    .table-mgmt-icon { font-size: 3em; color: #667eea; text-align: center; }
    .table-mgmt-info h3 { color: #2d3748; font-size: 1.6em; margin-bottom: 10px; }
    .table-mgmt-info p { color: #718096; font-size: 1em; margin: 0; line-height: 1.5; }

    .table-mgmt-btn {
        background: #667eea; color: white; padding: 12px 20px;
        border-radius: 8px; font-size: 1em; font-weight: 600;
        text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        text-align: center; margin-top: auto;
    }

    .table-mgmt-btn:hover { background: #5568d3; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); transform: translateY(-2px); }

    .coming-soon-badge {
        background: #e53e3e; color: white; padding: 4px 12px;
        border-radius: 12px; font-size: 0.8em; font-weight: 600;
        display: inline-block; position: absolute; top: 15px; right: 15px;
    }

    body.theme-green .table-mgmt-card:hover { border-color: #27ae60; }
    body.theme-green .table-mgmt-icon { color: #27ae60; }
    body.theme-green .table-mgmt-btn { background: #27ae60; }
    body.theme-green .user-mgmt-card:hover { border-color: #27ae60; }
    body.theme-green .user-mgmt-icon { color: #27ae60; }
    body.theme-green .user-mgmt-btn { background: #27ae60; }

    body.theme-blue .table-mgmt-card:hover { border-color: #3498db; }
    body.theme-blue .table-mgmt-icon { color: #3498db; }
    body.theme-blue .table-mgmt-btn { background: #3498db; }
    body.theme-blue .user-mgmt-card:hover { border-color: #3498db; }
    body.theme-blue .user-mgmt-icon { color: #3498db; }
    body.theme-blue .user-mgmt-btn { background: #3498db; }

    body.theme-office .table-mgmt-card:hover { border-color: #6c757d; }
    body.theme-office .table-mgmt-icon { color: #6c757d; }
    body.theme-office .table-mgmt-btn { background: #6c757d; }
    body.theme-office .user-mgmt-card:hover { border-color: #6c757d; }
    body.theme-office .user-mgmt-icon { color: #6c757d; }
    body.theme-office .user-mgmt-btn { background: #6c757d; }

    @keyframes slideIn  { from { transform: translate(-50%, -60%); opacity: 0; } to { transform: translate(-50%, -50%); opacity: 1; } }
    @keyframes slideOut { from { transform: translateY(-50%) translateX(0); opacity: 1; } to { transform: translateY(-50%) translateX(400px); opacity: 0; } }

    @media (max-width: 768px) {
        .settings-header h1 { font-size: 1.8em; }
        .settings-content { padding: 20px; }
        .theme-grid { grid-template-columns: 1fr; }
    }
</style>
<?php include 'includes/layout-start.php'; ?>
                <div class="settings-header">
                    <h1>&#x2699;&#xFE0F; Settings</h1>
                    <p>Customize your biosecurity system experience</p>
                </div>

                <div class="settings-content">
                    <!-- Theme Settings -->
                    <div class="settings-section">
                        <h2>&#x1F3A8; Theme Selection</h2>
                        <p>Choose a color theme that suits your preference. Your selection will be saved automatically.</p>

                        <div class="theme-grid">
                            <div class="theme-card active" onclick="setTheme('purple')" id="theme-card-purple">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                <div class="theme-info">
                                    <h3>&#x1F324;&#xFE0F; Clear Skies</h3>
                                    <p>Professional purple gradient theme with modern aesthetics</p>
                                    <span class="theme-badge" style="display: none;">Active</span>
                                </div>
                            </div>

                            <div class="theme-card" onclick="setTheme('green')" id="theme-card-green">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);"></div>
                                <div class="theme-info">
                                    <h3>&#x1F33F; Green Environment</h3>
                                    <p>Natural green theme perfect for biosecurity and environmental work</p>
                                    <span class="theme-badge" style="display: none;">Active</span>
                                </div>
                            </div>

                            <div class="theme-card" onclick="setTheme('blue')" id="theme-card-blue">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);"></div>
                                <div class="theme-info">
                                    <h3>&#x1F30A; Blue Ocean</h3>
                                    <p>Calming blue theme inspired by maritime operations</p>
                                    <span class="theme-badge" style="display: none;">Active</span>
                                </div>
                            </div>

                            <div class="theme-card" onclick="setTheme('office')" id="theme-card-office">
                                <div class="theme-preview" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);"></div>
                                <div class="theme-info">
                                    <h3>&#x1F3E2; Professional Office</h3>
                                    <p>Clean gray theme with professional office aesthetics and muted icons</p>
                                    <span class="theme-badge" style="display: none;">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Management Section -->
                    <div class="settings-section">
                        <h2>&#x1F4CB; Table Management</h2>
                        <p>Manage reference data tables used throughout the system</p>

                        <div class="table-mgmt-grid">
                            <div class="table-mgmt-card">
                                <div class="table-mgmt-icon">&#x1F6A2;</div>
                                <div class="table-mgmt-info">
                                    <h3>Port of Entry Management</h3>
                                    <p>Add, edit, and remove ports where vessels can arrive. This affects voyage forms and reporting.</p>
                                </div>
                                <a href="port_of_entry_management.php" class="table-mgmt-btn">
                                    Manage Ports
                                    <span style="font-size: 1.3em;">&#x2192;</span>
                                </a>
                            </div>

                            <div class="table-mgmt-card coming-soon">
                                <div class="table-mgmt-icon">&#x1F6F3;&#xFE0F;</div>
                                <div class="table-mgmt-info">
                                    <h3>Vessel Management</h3>
                                    <p>Vessel registry management (coming soon)</p>
                                </div>
                                <div class="coming-soon-badge">Coming Soon</div>
                            </div>

                            <div class="table-mgmt-card coming-soon">
                                <div class="table-mgmt-icon">&#x1F4E6;</div>
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
                        <h2>&#x1F465; User Management</h2>
                        <p>Manage system users, roles, and permissions</p>

                        <a href="user_management.php" class="user-mgmt-card">
                            <div class="user-mgmt-icon">&#x1F464;</div>
                            <div class="user-mgmt-info">
                                <h3>User Administration</h3>
                                <p>Add, edit, or remove users. Assign roles and manage access levels for biosecurity officers and administrators.</p>
                            </div>
                            <div class="user-mgmt-btn">
                                Access User Management
                                <span style="font-size: 1.3em;">&#x2192;</span>
                            </div>
                        </a>
                    </div>

                    <!-- Future Settings Placeholder -->
                    <div class="settings-section">
                        <h2>&#x1F514; Notifications</h2>
                        <p>Notification settings coming soon...</p>
                    </div>

                    <div class="settings-section">
                        <h2>&#x1F464; User Preferences</h2>
                        <p>User preference settings coming soon...</p>
                    </div>
                </div>

<script>
    function setTheme(theme) {
        document.body.classList.remove('theme-purple', 'theme-green', 'theme-blue', 'theme-office');
        document.body.classList.add('theme-' + theme);

        document.querySelectorAll('.theme-card').forEach(card => {
            card.classList.remove('active');
            const badge = card.querySelector('.theme-badge');
            if (badge) badge.style.display = 'none';
        });

        const activeCard = document.getElementById('theme-card-' + theme);
        if (activeCard) {
            activeCard.classList.add('active');
            const badge = activeCard.querySelector('.theme-badge');
            if (badge) badge.style.display = 'inline-block';
        }

        localStorage.setItem('selectedTheme', theme);
        showNotification('Theme changed successfully!');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('selectedTheme') || 'purple';
        setTheme(savedTheme);
    });

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
<?php include 'includes/layout-end.php'; ?>
