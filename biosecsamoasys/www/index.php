<?php
define('CSS_CACHE_BUST', 2);
require_once 'api/auth_check.php';
$pageTitle = 'Dashboard - Samoa Biosecure';
$currentPage = 'index';
?>
<style>
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

    .stat-value.loading {
        animation: pulse 1.5s infinite;
        color: #cbd5e0;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
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

    .activity-list {
        list-style: none;
        padding: 0;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1em;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-summary {
        font-size: 0.95em;
        color: #2d3748;
        margin-bottom: 2px;
    }

    .activity-time {
        font-size: 0.8em;
        color: #a0aec0;
        white-space: nowrap;
    }
</style>
<?php include 'includes/layout-start.php'; ?>
                <div class="welcome-header">
                    <h1>🚢 Biosecurity Information System</h1>
                    <p>Samoa Vessel Voyage and Passenger Inspection Management</p>
                </div>

                <div class="dashboard-content">
                    <!-- Statistics Grid -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">🚢</div>
                            <div class="stat-value loading" id="statVoyages">0</div>
                            <div class="stat-label">Total Voyages</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🔍</div>
                            <div class="stat-value loading" id="statInspections">0</div>
                            <div class="stat-label">Inspections</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">⚠️</div>
                            <div class="stat-value loading" id="statSeizures">0</div>
                            <div class="stat-label">Seizures</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-value loading" id="statReleases">0</div>
                            <div class="stat-label">Cargo Releases</div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h2>Quick Actions</h2>
                        <div class="action-buttons">
                            <a href="voyagement.php#voyageTab" class="action-btn">
                                <span class="action-btn-icon">🚢</span>
                                <span>New Voyage</span>
                            </a>
                            <a href="voyagement.php#inspectionTab" class="action-btn">
                                <span class="action-btn-icon">🔍</span>
                                <span>Passenger Inspection</span>
                            </a>
                            <a href="voyagement.php#seizureTab" class="action-btn">
                                <span class="action-btn-icon">⚠️</span>
                                <span>Record Seizure</span>
                            </a>
                            <a href="voyagement.php#releaseTab" class="action-btn">
                                <span class="action-btn-icon">📦</span>
                                <span>Cargo Release</span>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div style="background: #f7fafc; padding: 30px; border-radius: 10px;">
                        <h2 style="color: #2d3748; margin-bottom: 15px;">Recent Activity</h2>
                        <ul class="activity-list" id="recentActivityList">
                            <li class="empty-state">
                                <div class="empty-state-icon">📝</div>
                                <div class="empty-state-text">No recent activity</div>
                                <div class="empty-state-sub">Start by creating a new voyage or inspection</div>
                            </li>
                        </ul>
                    </div>
                </div>
<script>
    // Load dashboard statistics
    function loadDashboardStats() {
        const statEl = (id) => document.getElementById(id);

        // Fetch voyage count
        fetch('api/get_voyages.php')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    const el = statEl('statVoyages');
                    el.textContent = data.data.length;
                    el.classList.remove('loading');
                }
            })
            .catch(() => statEl('statVoyages').classList.remove('loading'));

        // Fetch inspection count
        fetch('api/get_recent_inspections.php')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    const el = statEl('statInspections');
                    el.textContent = data.data.length;
                    el.classList.remove('loading');
                }
            })
            .catch(() => statEl('statInspections').classList.remove('loading'));

        // Fetch seizure count
        fetch('api/get_recent_seizures.php')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    const el = statEl('statSeizures');
                    el.textContent = data.data.length;
                    el.classList.remove('loading');
                }
            })
            .catch(() => statEl('statSeizures').classList.remove('loading'));

        // Fetch cargo release count
        fetch('api/get_recent_releases.php')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    const el = statEl('statReleases');
                    el.textContent = data.data.length;
                    el.classList.remove('loading');
                }
            })
            .catch(() => statEl('statReleases').classList.remove('loading'));

        // Fetch recent activity for list
        Promise.all([
            fetch('api/get_recent_inspections.php').then(r => r.json()).catch(() => ({success:false})),
            fetch('api/get_recent_seizures.php').then(r => r.json()).catch(() => ({success:false}))
        ]).then(([inspections, seizures]) => {
            const listEl = document.getElementById('recentActivityList');
            const items = [];

            if (inspections.success && inspections.data) {
                inspections.data.slice(0, 4).forEach(i => {
                    const voyageNo = i.VoyageNo ? escapeHtml(i.VoyageNo) : 'Unknown';
                    const date = i.ModifiedDate ? new Date(i.ModifiedDate).toLocaleDateString() : '';
                    items.push({
                        date: i.ModifiedDate || '',
                        html: `<li class="activity-item"><div class="activity-icon">${i.NoOfNonCompliant > 0 ? '⚠️' : '✅'}</div><div class="activity-content"><div class="activity-summary">Inspection - ${voyageNo}</div></div><div class="activity-time">${date}</div></li>`
                    });
                });
            }

            if (seizures.success && seizures.data) {
                seizures.data.slice(0, 4).forEach(s => {
                    const name = escapeHtml(s.PassengerName || 'Unknown');
                    const date = s.ModifiedDate ? new Date(s.ModifiedDate).toLocaleDateString() : '';
                    items.push({
                        date: s.ModifiedDate || '',
                        html: `<li class="activity-item"><div class="activity-icon">⚠️</div><div class="activity-content"><div class="activity-summary">Seizure - ${name}</div></div><div class="activity-time">${date}</div></li>`
                    });
                });
            }

            if (items.length > 0) {
                items.sort((a, b) => b.date.localeCompare(a.date));
                listEl.innerHTML = items.map(i => i.html).join('');
            }
        });
    }

    function escapeHtml(text) {
        const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
        return (text+'').replace(/[&<>"']/g, m => map[m]);
    }

    document.addEventListener('DOMContentLoaded', loadDashboardStats);
</script>
<?php include 'includes/layout-end.php'; ?>
