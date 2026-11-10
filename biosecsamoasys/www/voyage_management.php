<?php
define('CSS_CACHE_BUST', 3);
require_once 'api/auth_check.php';
$pageTitle = 'Voyage Management - Biosecurity System';
$currentPage = 'voyage_management';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<style>
    .management-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        padding: 36px;
        text-align: center;
    }

    .management-header h1 {
        font-size: 1.75em;
        margin-bottom: 8px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .management-header p {
        font-size: 1em;
        opacity: 0.85;
        font-weight: 400;
    }

    .management-content {
        padding: 28px;
    }

    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .search-filters {
        display: flex;
        gap: 12px;
        flex: 1;
        flex-wrap: wrap;
    }

    .search-input {
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        font-size: 0.925em;
        min-width: 220px;
        transition: var(--transition);
        font-family: inherit;
        color: var(--text-primary);
        background: white;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 109, 245, 0.12);
    }

    .filter-select {
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        font-size: 0.925em;
        background: white;
        cursor: pointer;
        transition: var(--transition);
        color: var(--text-primary);
        font-family: inherit;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .btn {
        padding: 10px 20px;
        font-size: 0.925em;
        font-weight: 600;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;
    }

    .btn:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        box-shadow: 0 1px 3px rgba(79, 109, 245, 0.3);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 109, 245, 0.3);
    }

    .btn-secondary {
        background: #f1f5f9;
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }

    .btn-sm {
        padding: 7px 14px;
        font-size: 0.85em;
    }

    .voyages-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }

    .voyages-table th {
        background: var(--bg-light);
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color);
        font-size: 0.8em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .voyages-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        font-size: 0.9em;
        color: var(--text-primary);
    }

    .voyages-table tr:hover {
        background: var(--bg-light);
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.78em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-draft {
        background: var(--warning-light);
        color: #92400e;
    }

    .status-in_progress {
        background: var(--info-light);
        color: #075985;
    }

    .status-completed {
        background: var(--success-light);
        color: #065f46;
    }

    .status-archived {
        background: #e2e8f0;
        color: #374151;
    }

    .step-progress {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .step-indicator {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72em;
        font-weight: 600;
        color: var(--text-muted);
    }

    .step-indicator.complete {
        background: var(--success);
        color: white;
    }

    .step-indicator.current {
        background: var(--primary);
        color: white;
    }

    .actions-cell {
        display: flex;
        gap: 6px;
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
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        animation: fadeIn 0.2s ease;
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 0;
        border-radius: var(--radius-lg);
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: slideIn 0.3s ease;
    }

    .modal-header {
        background: var(--sidebar-bg);
        color: white;
        padding: 20px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.25em;
        font-weight: 700;
    }

    .close {
        background: none;
        border: none;
        color: white;
        font-size: 1.4em;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: var(--transition);
    }

    .close:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .modal-body {
        padding: 28px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875em;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        font-size: 0.925em;
        transition: var(--transition);
        font-family: inherit;
        color: var(--text-primary);
        background: white;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 109, 245, 0.12);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .modal-footer {
        padding: 16px 28px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 3em;
        margin-bottom: 16px;
        opacity: 0.4;
    }

    .empty-state h3 {
        font-size: 1.25em;
        margin-bottom: 8px;
        color: var(--text-primary);
        font-weight: 600;
    }

    .empty-state p {
        font-size: 0.95em;
        margin-bottom: 24px;
    }

    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 60px 20px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--border-color);
        border-top: 3px solid var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .action-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-filters {
            flex-direction: column;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .voyages-table {
            font-size: 0.85em;
        }

        .voyages-table th,
        .voyages-table td {
            padding: 10px 8px;
        }

        .actions-cell {
            flex-direction: column;
            gap: 4px;
        }
    }
</style>
<?php include 'includes/layout-start.php'; ?>
                <div class="management-header">
                    <h1>🚢 Voyage Management</h1>
                    <p>Manage biosecurity voyages with full lifecycle support</p>
                </div>

                <div class="management-content">
                    <!-- Action Bar -->
                    <div class="action-bar">
                        <div class="search-filters">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search voyage...">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                            <select id="stepFilter" class="filter-select">
                                <option value="">All Steps</option>
                                <option value="voyage_details">Voyage Details</option>
                                <option value="passenger_inspection">Passenger Inspection</option>
                                <option value="passenger_seizure">Passenger Seizure</option>
                                <option value="cargo_seizure">Cargo Seizure</option>
                                <option value="cargo_release">Cargo Release</option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="openCreateModal()">
                                <span>➕</span>
                                <span>New Voyage</span>
                            </button>
                        </div>
                    </div>

                    <!-- Voyages Table -->
                    <div id="voyagesTableContainer">
                        <div class="loading">
                            <div class="spinner"></div>
                        </div>
                    </div>
                </div>

    <!-- Create/Edit Voyage Modal -->
    <div id="voyageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Create New Voyage</h2>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="voyageForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="VoyageNo">Voyage Number <span class="required">*</span></label>
                            <input type="text" id="VoyageNo" name="VoyageNo" required>
                        </div>
                        <div class="form-group">
                            <label for="VesselID">Vessel <span class="required">*</span></label>
                            <select id="VesselID" name="VesselID" required>
                                <option value="">Select Vessel</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="PortOfLoadingID">Port of Loading <span class="required">*</span></label>
                            <select id="PortOfLoadingID" name="PortOfLoadingID" required>
                                <option value="">Select a country...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="PortOfArrivalID">Port of Arrival <span class="required">*</span></label>
                            <select id="PortOfArrivalID" name="PortOfArrivalID" required>
                                <option value="">Select a port...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ArrivalDate">Arrival Date <span class="required">*</span></label>
                            <input type="date" id="ArrivalDate" name="ArrivalDate" required>
                        </div>
                        <div class="form-group">
                            <label for="LocationID">Location <span class="required">*</span></label>
                            <select id="LocationID" name="LocationID" required>
                                <option value="">Select Location</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="Pax">Passengers</label>
                            <input type="text" id="Pax" name="Pax" placeholder="Number of passengers">
                        </div>
                        <div class="form-group">
                            <label for="Crew">Crew</label>
                            <input type="text" id="Crew" name="Crew" placeholder="Number of crew">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ModifiedBy">Created By <span class="required">*</span></label>
                        <input type="text" id="ModifiedBy" name="ModifiedBy" value="Bio Officer" required>
                    </div>
                    <div class="form-group">
                        <label for="ModifiedDate">Date Created <span class="required">*</span></label>
                        <input type="date" id="ModifiedDate" name="ModifiedDate" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveVoyage()">Save Voyage</button>
            </div>
        </div>
    </div>
<?php include 'includes/layout-end.php'; ?>
    <script>
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token']; ?>';
        let voyages = [];
        let currentEditId = null;

        // Load voyages on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadVoyages();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', filterVoyages);
            document.getElementById('statusFilter').addEventListener('change', filterVoyages);
            document.getElementById('stepFilter').addEventListener('change', filterVoyages);
        }

        async function loadVoyages() {
            try {
                const response = await fetch('api/voyage_crud.php');
                const result = await response.json();

                if (result.success) {
                    voyages = result.data;
                    renderVoyagesTable(voyages);
                } else {
                    showError('Failed to load voyages: ' + result.message);
                }
            } catch (error) {
                showError('Error loading voyages: ' + error.message);
            }
        }

        function renderVoyagesTable(data) {
            const container = document.getElementById('voyagesTableContainer');

            if (!data || data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">🚢</div>
                        <h3>No Voyages Found</h3>
                        <p>Get started by creating your first voyage.</p>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <span>➕</span>
                            <span>Create Voyage</span>
                        </button>
                    </div>
                `;
                return;
            }

            const table = `
                <table class="voyages-table">
                    <thead>
                        <tr>
                            <th>Voyage No</th>
                            <th>Vessel</th>
                            <th>Arrival Date</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(voyage => createVoyageRow(voyage)).join('')}
                    </tbody>
                </table>
            `;

            container.innerHTML = table;
        }

        function createVoyageRow(voyage) {
            const status = voyage.status || 'draft';
            const statusClass = `status-${status}`;
            const statusText = status.replace('_', ' ');

            return `
                <tr>
                    <td><strong>${voyage.VoyageNo || 'N/A'}</strong></td>
                    <td>${voyage.VesselID || 'N/A'}</td>
                    <td>${voyage.ArrivalDate || 'N/A'}</td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td>${createStepProgress(voyage)}</td>
                    <td>${formatDate(voyage.created_at)}</td>
                    <td class="actions-cell">
                        <button class="btn btn-sm btn-primary" onclick="editVoyage(${voyage.VoyageID})" title="Edit Voyage">✏️</button>
                        <a href="voyagement.php?voyage_id=${voyage.VoyageID}" class="btn btn-sm btn-secondary" title="Process Voyage">📝</a>
                        <a href="export_report.php?voyage_id=${voyage.VoyageID}" class="btn btn-sm" style="background: #10b981; color: white;" title="Export Report" target="_blank">📄</a>
                        <button class="btn btn-sm" style="background: #ef4444; color: white;" onclick="deleteVoyage(${voyage.VoyageID})" title="Delete">🗑️</button>
                    </td>
                </tr>
            `;
        }

        function createStepProgress(voyage) {
            const steps = [
                { key: 'voyage_details_complete', label: 'VD' },
                { key: 'passenger_inspection_complete', label: 'PI' },
                { key: 'passenger_seizure_complete', label: 'PS' },
                { key: 'cargo_seizure_complete', label: 'CS' },
                { key: 'cargo_release_complete', label: 'CR' }
            ];

            const indicators = steps.map(step => {
                let className = 'step-indicator';
                if (voyage[step.key]) {
                    className += ' complete';
                } else if (voyage.current_step === step.key.replace('_complete', '')) {
                    className += ' current';
                }
                return `<div class="${className}" title="${step.label}">${voyage[step.key] ? '✓' : step.label.charAt(0)}</div>`;
            });

            return `<div class="step-progress">${indicators.join('')}</div>`;
        }

        function filterVoyages() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const stepFilter = document.getElementById('stepFilter').value;

            let filtered = voyages.filter(voyage => {
                const matchesSearch = !searchTerm ||
                    (voyage.VoyageNo && voyage.VoyageNo.toLowerCase().includes(searchTerm)) ||
                    (voyage.VesselID && voyage.VesselID.toLowerCase().includes(searchTerm));

                const matchesStatus = !statusFilter || voyage.status === statusFilter;
                const matchesStep = !stepFilter || voyage.current_step === stepFilter;

                return matchesSearch && matchesStatus && matchesStep;
            });

            renderVoyagesTable(filtered);
        }

        function closeModal() {
            document.getElementById('voyageModal').style.display = 'none';
            currentEditId = null;
        }

        function openCreateModal() {
            currentEditId = null;
            document.getElementById('modalTitle').textContent = 'Create New Voyage';
            document.getElementById('voyageForm').reset();
            document.getElementById('ModifiedDate').value = new Date().toISOString().split('T')[0];

            setTimeout(() => {
                loadCountryDropdowns();
                loadLocationDropdown();
                loadVesselDropdown();
            }, 100);

            document.getElementById('voyageModal').style.display = 'block';
        }

        async function saveVoyage() {
            const formData = new FormData(document.getElementById('voyageForm'));
            const data = Object.fromEntries(formData.entries());

            try {
                let response;
                if (currentEditId) {
                    data.csrf_token = CSRF_TOKEN;
                    response = await fetch(`api/voyage_details.php?id=${currentEditId}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(data)
                    });
                } else {
                    response = await fetch('api/voyage_crud.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(data)
                    });
                }

                const result = await response.json();

                if (result.success) {
                    showSuccess(currentEditId ? 'Voyage updated successfully!' : 'Voyage created successfully!');
                    closeModal();
                    loadVoyages();
                } else {
                    showError('Failed to save voyage: ' + result.message);
                }
            } catch (error) {
                showError('Error saving voyage: ' + error.message);
            }
        }

        async function deleteVoyage(id) {
            if (!confirm('Are you sure you want to delete this voyage? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(`api/voyage_details.php?id=${id}&csrf_token=${CSRF_TOKEN}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showSuccess('Voyage deleted successfully!');
                    loadVoyages();
                } else {
                    showError('Failed to delete voyage: ' + result.message);
                }
            } catch (error) {
                showError('Error deleting voyage: ' + error.message);
            }
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-SG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function showSuccess(message) {
            showNotification(message, 'success');
        }

        function showError(message) {
            showNotification(message, 'error');
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? '#10b981' : '#ef4444';
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
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('voyageModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        async function loadLocationDropdown() {
            const locationSelect = document.getElementById('LocationID');
            if (!locationSelect) return;

            try {
                const response = await fetch('api/get_locations.php');
                const result = await response.json();

                if (result.success && result.data) {
                    locationSelect.innerHTML = `<option value="">Select a location...</option>`;
                    const locationsByRegion = {};
                    result.data.forEach(location => {
                        if (!locationsByRegion[location.region]) {
                            locationsByRegion[location.region] = [];
                        }
                        locationsByRegion[location.region].push(location);
                    });

                    Object.keys(locationsByRegion).sort().forEach(region => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = region;
                        locationsByRegion[region].sort((a, b) => a.location_name.localeCompare(b.location_name))
                            .forEach(location => {
                                const option = document.createElement('option');
                                option.value = location.location_id;
                                option.textContent = location.location_name;
                                optgroup.appendChild(option);
                            });
                        locationSelect.appendChild(optgroup);
                    });
                }
            } catch (error) {
                console.error('Error loading locations:', error);
            }
        }

        async function loadCountryDropdown(selectElement, placeholder) {
            try {
                const response = await fetch('api/get_countries.php');
                const result = await response.json();

                if (result.success && result.data) {
                    selectElement.innerHTML = `<option value="">${placeholder}</option>`;
                    result.data.forEach(country => {
                        const option = document.createElement('option');
                        option.value = country.CountryID;
                        option.textContent = country.CountryName;
                        selectElement.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading countries:', error);
            }
        }

        async function loadCountryDropdowns() {
            const portOfLoadingSelect = document.getElementById('PortOfLoadingID');
            if (portOfLoadingSelect && portOfLoadingSelect.tagName === 'SELECT') {
                await loadCountryDropdown(portOfLoadingSelect, 'Select a country...');
            }
            const portOfArrivalSelect = document.getElementById('PortOfArrivalID');
            if (portOfArrivalSelect && portOfArrivalSelect.tagName === 'SELECT') {
                await loadPortsDropdown(portOfArrivalSelect, 'Select a port...');
            }
        }

        async function loadPortsDropdown(selectElement, placeholder) {
            try {
                const response = await fetch('api/get_ports.php');
                const result = await response.json();

                if (result.success && result.data) {
                    selectElement.innerHTML = `<option value="">${placeholder}</option>`;
                    const portsByCountry = {};
                    result.data.forEach(port => {
                        const country = port.country || 'Unknown';
                        if (!portsByCountry[country]) {
                            portsByCountry[country] = [];
                        }
                        portsByCountry[country].push(port);
                    });

                    Object.keys(portsByCountry).sort().forEach(country => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = country;
                        portsByCountry[country].sort((a, b) => a.port_name.localeCompare(b.port_name))
                            .forEach(port => {
                                const option = document.createElement('option');
                                option.value = port.port_id;
                                option.textContent = port.port_name;
                                optgroup.appendChild(option);
                            });
                        selectElement.appendChild(optgroup);
                    });
                }
            } catch (error) {
                console.error('Error loading ports:', error);
            }
        }

        async function loadVesselDropdown() {
            const vesselSelect = document.getElementById('VesselID');
            if (!vesselSelect) return;

            try {
                const response = await fetch('api/get_vessels.php');
                const result = await response.json();

                if (result.success && result.data) {
                    vesselSelect.innerHTML = `<option value="">Select Vessel</option>`;
                    result.data.sort((a, b) => a.VesselName.localeCompare(b.VesselName))
                        .forEach(vessel => {
                            const option = document.createElement('option');
                            option.value = vessel.VesselID;
                            option.textContent = vessel.VesselName;
                            vesselSelect.appendChild(option);
                        });
                }
            } catch (error) {
                console.error('Error loading vessels:', error);
            }
        }

        function editVoyage(id) {
            const voyage = voyages.find(v => v.VoyageID === id);
            if (!voyage) return;

            currentEditId = id;
            document.getElementById('modalTitle').textContent = 'Edit Voyage';

            document.getElementById('VoyageNo').value = voyage.VoyageNo || '';
            document.getElementById('VesselID').value = voyage.VesselID || '';
            document.getElementById('PortOfLoadingID').value = voyage.PortOfLoadingID || '';
            document.getElementById('PortOfArrivalID').value = voyage.PortOfArrivalID || '';
            document.getElementById('LocationID').value = voyage.LocationID || '';
            document.getElementById('ArrivalDate').value = voyage.ArrivalDate || '';
            document.getElementById('Pax').value = voyage.Pax || '';
            document.getElementById('Crew').value = voyage.Crew || '';
            document.getElementById('ModifiedBy').value = voyage.ModifiedBy || '';
            document.getElementById('ModifiedDate').value = voyage.ModifiedDate || '';

            setTimeout(() => {
                loadCountryDropdowns();
                loadLocationDropdown();
                loadVesselDropdown();
            }, 100);

            document.getElementById('voyageModal').style.display = 'block';
        }
    </script>
