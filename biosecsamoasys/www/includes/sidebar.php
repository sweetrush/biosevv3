<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            🚢
            <span>BioSecure</span>
        </div>
        <div class="sidebar-subtitle">Samoa Biosecurity</div>

        <!-- User Profile Tile -->
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

    <nav class="sidebar-nav" aria-label="Main navigation">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="index.php" class="nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">🏠</span>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="voyage_management.php" class="nav-item <?= $currentPage === 'voyage_management' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">🚢</span>
                <span>Voyage Management</span>
            </a>
            <a href="voyagement.php" class="nav-item <?= $currentPage === 'voyagement' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">📝</span>
                <span>Voyagement</span>
            </a>
            <a href="location_management.php" class="nav-item <?= $currentPage === 'location_management' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">📍</span>
                <span>Location Management</span>
            </a>
            <a href="unified_seizure.php" class="nav-item <?= $currentPage === 'unified_seizure' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">⚠️</span>
                <span>Seizure Management</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon" aria-hidden="true">📊</span>
                <span>Reports</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Permits & Certificates</div>
            <a href="import_permits.php" class="nav-item <?= $currentPage === 'import_permits' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">📋</span>
                <span>Import Permits</span>
            </a>
        </div>

        <?php
            $sidebarRole = $_SESSION['access_level'] ?? '';
            $sidebarRoleLevels = ['viewer' => 0, 'officer' => 1, 'admin' => 2, 'authorising_officer' => 3];
            if (isset($sidebarRoleLevels[$sidebarRole]) && $sidebarRoleLevels[$sidebarRole] >= 2):
        ?>
        <div class="nav-section">
            <div class="nav-section-title">Administration</div>
            <a href="user_management.php" class="nav-item <?= $currentPage === 'user_management' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">👥</span>
                <span>User Management</span>
            </a>
            <a href="settings.php" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">⚙️</span>
                <span>Settings</span>
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)); ?></div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
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
<button class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
    ☰
</button>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
