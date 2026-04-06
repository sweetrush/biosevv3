<?php
define('CSS_CACHE_BUST', 2);
require_once 'api/auth_check.php';
$pageTitle = 'Voyage Management - Biosecurity System';
$currentPage = 'voyage_management';
?>
<style>
    .management-header {
        background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
        color: white;
        padding: 40px;
        text-align: center;
    }

    .management-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .management-header p {
        font-size: 1.1em;
        opacity: 0.9;
    }

    .management-content {
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

    .search-filters {
        display: flex;
        gap: 15px;
        flex: 1;
        flex-wrap: wrap;
    }

    .search-input {
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1em;
        min-width: 250px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filter-select {
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1em;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-select:focus {
        outline: none;
        border-color: #667eea;
    }

    .btn {
        padding: 12px 24px;
        font-size: 1em;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
        color: #4a5568;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%);
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 0.9em;
    }

    .voyages-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .voyages-table th {
        background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
        padding: 16px;
        text-align: left;
        font-weight: 600;
        color: #2d3748;
        border-bottom: 2px solid #e2e8f0;
    }

    .voyages-table td {
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .voyages-table tr:hover {
        background: #f7fafc;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-draft {
        background: #fef3c7;
        color: #92400e;
    }

    .status-in_progress {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-archived {
        background: #e5e7eb;
        color: #374151;
    }

    .step-progress {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .step-indicator {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8em;
        font-weight: 600;
        color: #6b7280;
    }

    .step-indicator.complete {
        background: #10b981;
        color: white;
    }

    .step-indicator.current {
        background: #3b82f6;
        color: white;
    }

    .actions-cell {
        display: flex;
        gap: 8px;
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
        background: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 0;
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

    .close {
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

    .close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .modal-body {
        padding: 30px;
        max-height: 60vh;
        overflow-y: auto;
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
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1em;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .modal-footer {
        padding: 20px 30px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.5em;
        margin-bottom: 10px;
        color: #374151;
    }

    .empty-state p {
        font-size: 1.1em;
        margin-bottom: 30px;
    }

    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 60px 20px;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #e5e7eb;
        border-top: 5px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
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
            transform: translateY(-50px);
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
            font-size: 0.9em;
        }

        .voyages-table th,
        .voyages-table td {
            padding: 12px 8px;
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
                const response = await fetch(`api/voyage_details.php?id=${id}`, {
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
