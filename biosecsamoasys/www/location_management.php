<?php
define('CSS_CACHE_BUST', 2);
require_once 'api/auth_check.php';
$pageTitle = 'Location Management - Samoa Biosecurity System';
$currentPage = 'location_management';
?>
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

    .btn-edit { background: #4299e1; color: white; }
    .btn-edit:hover { background: #3182ce; transform: translateY(-1px); }
    .btn-delete { background: #ef4444; color: white; }
    .btn-delete:hover { background: #dc2626; transform: translateY(-1px); }

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

    .quick-stat { text-align: center; }
    .quick-stat-value { font-size: 1.8em; font-weight: 700; color: #667eea; }
    .quick-stat-label { font-size: 0.85em; color: #4a5568; margin-top: 5px; }

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

    .filter-controls .form-group { margin-bottom: 0; }
    .filter-controls .form-group label { display: none; }

    .search-box { position: relative; }

    .search-box input {
        padding: 12px 105px 12px 45px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        width: 100%;
        font-size: 1em;
    }

    .search-box input:focus { outline: none; border-color: #667eea; }

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
        width: 16px; height: 16px;
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
        right: 95px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #a0aec0;
        font-size: 16px;
        cursor: pointer;
        width: 20px; height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .clear-search:hover { background: #f7fafc; color: #4a5568; }

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
        width: 36px; height: 36px;
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

    .no-locations {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
        font-size: 1.1em;
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

    @media (max-width: 768px) {
        .locations-table { margin: 15px; font-size: 0.9em; }
        .locations-table th,
        .locations-table td { padding: 12px 8px; }
        .filter-controls { grid-template-columns: 1fr; }
        .location-actions { flex-direction: column; }
        .btn-small { font-size: 0.8em; padding: 6px 12px; }
    }

    .modal {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
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

    .modal-header h2 { margin: 0; font-size: 1.5em; }

    .close-btn {
        background: none; border: none; color: white;
        font-size: 1.5em; cursor: pointer; padding: 0;
        width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; transition: background 0.3s ease;
    }

    .close-btn:hover { background: rgba(255, 255, 255, 0.2); }

    .modal-body { padding: 30px; max-height: 60vh; overflow-y: auto; }

    .modal-footer {
        padding: 20px 30px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }

    .form-group input[readonly] { background: #f8f9fa !important; color: #6c757d !important; cursor: not-allowed; }

    .required { color: #e53e3e; }

    .btn {
        padding: 12px 24px; border: none; border-radius: 6px;
        font-size: 1em; font-weight: 600; cursor: pointer;
        transition: all 0.3s ease; display: inline-flex;
        align-items: center; gap: 8px;
    }

    .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3); }
    .btn-secondary { background: #e2e8f0; color: #4a5568; }
    .btn-secondary:hover { background: #cbd5e0; }
    .btn-danger { background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%); color: white; }
    .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(229, 62, 62, 0.3); }

    .delete-warning { text-align: center; }
    .warning-icon { font-size: 3em; margin-bottom: 15px; }
    .delete-warning h3 { color: #2d3748; margin-bottom: 15px; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>
<?php include 'includes/layout-start.php'; ?>
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
                            <button type="button" id="clearSearch" class="clear-search" style="display: none;" title="Clear search">&#x2715;</button>
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

                <!-- Edit Location Modal -->
                <div id="editLocationModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>&#x270F;&#xFE0F; Edit Location</h2>
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
                                        <input type="text" id="editLocationIdDisplay" readonly>
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

<script>
    let cachedLocations = null;
    let isLoadingLocations = false;

    document.addEventListener('DOMContentLoaded', function() {
        loadLocationsData();
        loadStats();
    });

    function getLocationTypeIcon(locationType) {
        const icons = {
            'seaport': '⚓', 'airport': '✈️', 'wharf': '🏗️', 'terminal': '🏢',
            'warehouse': '📦', 'land_border': '🛃', 'resort': '🏨', 'naval': '⚔️',
            'emergency': '🚨', 'other': '📍'
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
                if (data.success) { cachedLocations = data.data; displayLocations(); }
                else { console.error('Failed to load locations:', data.message); }
            })
            .catch(error => console.error('Error loading locations:', error))
            .finally(() => { isLoadingLocations = false; });
    }

    function refreshLocationsData() {
        cachedLocations = null;
        return loadLocationsData();
    }

    function refreshLocations() {
        document.getElementById('searchLocation').value = '';
        document.getElementById('regionFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('clearSearch').style.display = 'none';
        document.getElementById('searchLocation').style.paddingRight = '105px';

        const refreshBtn = document.querySelector('.btn-refresh');
        const originalText = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '🔄 Refreshing...';
        refreshBtn.disabled = true;

        refreshLocationsData().then(() => {
            loadStats();
            refreshBtn.innerHTML = originalText;
            refreshBtn.disabled = false;
        }).catch(error => {
            console.error('Error refreshing locations:', error);
            refreshBtn.innerHTML = originalText;
            refreshBtn.disabled = false;
            alert('Error refreshing locations. Please try again.');
        });
    }

    function displayLocations(locations = null) {
        const tbody = document.getElementById('locationsTableBody');
        const searchTerm = document.getElementById('searchLocation').value.toLowerCase();
        const regionFilter = document.getElementById('regionFilter').value;
        const typeFilter = document.getElementById('typeFilter').value;
        const noLocationsDiv = document.getElementById('noLocations');
        const sourceLocations = locations || cachedLocations || [];
        let filteredLocations = [...sourceLocations];

        if (searchTerm) {
            filteredLocations = filteredLocations.filter(location =>
                location.location_name.toLowerCase().includes(searchTerm) ||
                location.location_id.toLowerCase().includes(searchTerm) ||
                location.region.toLowerCase().includes(searchTerm) ||
                location.location_type.toLowerCase().includes(searchTerm)
            );
        }
        if (regionFilter) {
            filteredLocations = filteredLocations.filter(location => location.region === regionFilter);
        }
        if (typeFilter) {
            filteredLocations = filteredLocations.filter(location => location.location_type === typeFilter);
        }

        tbody.innerHTML = '';
        if (filteredLocations.length === 0) { noLocationsDiv.style.display = 'block'; return; }
        else { noLocationsDiv.style.display = 'none'; }

        tbody.innerHTML = filteredLocations.map(location => {
            const typeIcon = getLocationTypeIcon(location.location_type);
            const formattedType = formatLocationType(location.location_type);
            const statusBadge = location.is_active
                ? '<span class="location-type-badge type-warehouse">✅ Active</span>'
                : '<span class="inactive-badge">🚫 Inactive</span>';
            return `
                <tr>
                    <td class="location-id-cell">${location.location_id}</td>
                    <td><strong>${location.location_name}</strong></td>
                    <td>📍 ${location.region}</td>
                    <td><span class="location-type-badge type-${location.location_type}">${typeIcon} ${formattedType}</span></td>
                    <td>${statusBadge}</td>
                    <td class="location-actions">
                        <button class="btn-small btn-edit" onclick="editLocation('${location.location_id}')">✏️ Edit</button>
                        <button class="btn-small btn-delete" onclick="deleteLocation('${location.location_id}')">🗑️ Delete</button>
                    </td>
                </tr>
            `;
        }).join('');
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
        fetch(`api/edit_location.php?id=${locationId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const location = data.data;
                    document.getElementById('editLocationId').value = location.location_id;
                    document.getElementById('editLocationIdDisplay').value = location.location_id;
                    document.getElementById('editLocationName').value = location.location_name;
                    document.getElementById('editLocationRegion').value = location.region;
                    document.getElementById('editLocationType').value = location.location_type;
                    document.getElementById('editLocationActive').checked = location.is_active;
                    document.getElementById('editLocationModal').style.display = 'flex';
                } else { alert(`Error loading location: ${data.message}`); }
            })
            .catch(error => {
                console.error('Error loading location:', error);
                alert('Error loading location data. Please try again.');
            });
    }

    function closeEditModal() {
        document.getElementById('editLocationModal').style.display = 'none';
        document.getElementById('editLocationForm').reset();
    }

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
        fetch('api/edit_location.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Location updated successfully!');
                closeEditModal();
                refreshLocationsData().then(() => loadStats());
            } else { alert(`Error updating location: ${result.message}`); }
        })
        .catch(error => {
            console.error('Error updating location:', error);
            alert('Error updating location. Please try again.');
        });
    });

    function deleteLocation(locationId) {
        fetch(`api/edit_location.php?id=${locationId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const location = data.data;
                    document.getElementById('deleteLocationDetails').innerHTML = `
                        <strong>Location ID:</strong> ${location.location_id}<br>
                        <strong>Location Name:</strong> ${location.location_name}<br>
                        <strong>Region:</strong> ${location.region}<br>
                        <strong>Type:</strong> ${formatLocationType(location.location_type)}<br>
                        <strong>Status:</strong> ${location.is_active ? '✅ Active' : '🚫 Inactive'}
                    `;
                    document.getElementById('confirmDeleteBtn').dataset.locationId = locationId;
                    document.getElementById('deleteConfirmModal').style.display = 'flex';
                    document.getElementById('deleteWarningMessage').style.display = 'none';
                    document.getElementById('confirmDeleteBtn').style.display = 'inline-flex';
                } else { alert(`Error loading location details: ${data.message}`); }
            })
            .catch(error => {
                console.error('Error loading location details:', error);
                alert('Error loading location details. Please try again.');
            });
    }

    function closeDeleteModal() {
        document.getElementById('deleteConfirmModal').style.display = 'none';
        document.getElementById('deleteWarningMessage').style.display = 'none';
        document.getElementById('confirmDeleteBtn').style.display = 'inline-flex';
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        const locationId = this.dataset.locationId;
        if (!locationId) return;

        this.innerHTML = '<span>🔄</span> Deleting...';
        this.disabled = true;

        fetch('api/delete_location.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: locationId })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert(result.message);
                closeDeleteModal();
                refreshLocationsData().then(() => loadStats());
            } else if (result.has_references) {
                let warningMessage = result.message + '\n\nYou must remove or update these references before deleting this location.';
                document.getElementById('deleteWarningMessage').innerHTML = `
                    <strong>⚠️ Cannot Delete Location</strong><br>
                    <div style="text-align: left; margin-top: 10px;">${warningMessage.replace(/\n/g, '<br>')}</div>
                `;
                document.getElementById('deleteWarningMessage').style.display = 'block';
                document.getElementById('confirmDeleteBtn').style.display = 'none';
            } else { alert(`Error deleting location: ${result.message}`); }
        })
        .catch(error => {
            console.error('Error deleting location:', error);
            alert('Error deleting location. Please try again.');
        })
        .finally(() => {
            this.innerHTML = '<span>🗑️</span> Delete Location';
            this.disabled = false;
        });
    });

    document.getElementById('addLocationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const locationName = document.getElementById('locationName').value;
        const region = document.getElementById('locationRegion').value;
        const locationType = document.getElementById('locationType').value;
        alert(`Add functionality would be implemented here. This would send a POST request to create a new location: ${locationName} (${locationType}) in ${region} region.`);
        this.reset();
    });

    let searchTimeout;
    function debounceSearch(callback, delay) {
        return function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(callback, delay);
        };
    }

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

    function hideSearchLoading() {
        const loadingIndicator = document.getElementById('searchLoading');
        const searchInput = document.getElementById('searchLocation');
        const clearButton = document.getElementById('clearSearch');
        loadingIndicator.style.display = 'none';
        if (searchInput.value.trim()) {
            clearButton.style.display = 'flex';
            searchInput.style.paddingRight = '105px';
        } else {
            clearButton.style.display = 'none';
            searchInput.style.paddingRight = '105px';
        }
    }

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

    function clearSearch() {
        const searchInput = document.getElementById('searchLocation');
        const clearButton = document.getElementById('clearSearch');
        const loadingIndicator = document.getElementById('searchLoading');
        searchInput.value = '';
        clearButton.style.display = 'none';
        loadingIndicator.style.display = 'none';
        searchInput.style.paddingRight = '105px';
        searchInput.focus();
        if (cachedLocations) { displayLocations(); } else { loadLocationsData(); }
    }

    function performInstantSearch() {
        if (cachedLocations) {
            showSearchLoading();
            setTimeout(() => { displayLocations(); hideSearchLoading(); }, 100);
        } else {
            loadLocationsData().then(() => { displayLocations(); hideSearchLoading(); });
        }
    }

    function applyFilters() {
        if (cachedLocations) { displayLocations(); }
        else { loadLocationsData().then(() => displayLocations()); }
    }

    const debouncedSearch = debounceSearch(performInstantSearch, 150);
    document.getElementById('searchLocation').addEventListener('input', function() {
        updateClearButton();
        debouncedSearch();
    });
    document.getElementById('regionFilter').addEventListener('change', applyFilters);
    document.getElementById('typeFilter').addEventListener('change', applyFilters);
    document.getElementById('clearSearch').addEventListener('click', clearSearch);
    document.getElementById('searchButton').addEventListener('click', () => performInstantSearch());
    document.getElementById('searchLocation').addEventListener('paste', function() {
        setTimeout(updateClearButton, 10);
    });
</script>
<?php include 'includes/layout-end.php'; ?>
