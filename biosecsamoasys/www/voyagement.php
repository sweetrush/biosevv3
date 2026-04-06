<?php
require_once 'api/auth_check.php';
define('CSS_CACHE_BUST', 2);
$pageTitle = 'Voyagement - Samoa Biosecurity System';
$currentPage = 'voyagement';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize voyage editing variables
$edit_voyage_id = null;
$edit_mode = false;
$voyage_data = null;

// Database connection using getDBConnection()
require_once 'api/config.php';

function getVoyageData($voyageId) {
    try {
        $conn = getDBConnection();

        $sql = "SELECT vd.*, vs.current_step, vs.status,
                       vs.voyage_details_complete, vs.passenger_inspection_complete,
                       vs.passenger_seizure_complete, vs.cargo_seizure_complete, vs.cargo_release_complete
                FROM voyage_details vd
                LEFT JOIN voyage_status vs ON vd.VoyageID = vs.VoyageID
                WHERE vd.VoyageID = :VoyageID";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':VoyageID', $voyageId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        error_log("Error loading voyage data: " . $e->getMessage());
        return false;
    }
}

// Check for voyage_id parameter
if (isset($_GET['voyage_id']) && !empty($_GET['voyage_id'])) {
    $edit_voyage_id = (int)$_GET['voyage_id'];
    $edit_mode = true;

    if ($edit_voyage_id) {
        try {
            $voyage_data = getVoyageData($edit_voyage_id);
            if (!$voyage_data) {
                $edit_mode = false;
                $edit_voyage_id = null;
            }
        } catch (Exception $e) {
            $edit_mode = false;
            $edit_voyage_id = null;
        }
    }
}
?>
<style>
    /* Tabs styling */
    .tabs {
        display: flex;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        padding: 0;
        margin: 0;
        list-style: none;
        border-radius: 12px 12px 0 0;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .tabs li {
        flex: 1;
        position: relative;
    }

    .tabs li::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 1px;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
    }

    .tabs li:last-child::after {
        display: none;
    }

    .tabs button {
        width: 100%;
        padding: 16px 8px;
        background: transparent;
        color: rgba(255, 255, 255, 0.8);
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 85px;
        position: relative;
        overflow: hidden;
    }

    .tabs button::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: translateY(-100%);
        transition: transform 0.3s ease;
    }

    .tabs button:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transform: translateY(-2px);
    }

    .tabs button:hover::before {
        transform: translateY(0);
    }

    .tabs button.active {
        background: rgba(102, 126, 234, 0.2);
        color: white;
        box-shadow: inset 0 3px 0 #667eea;
    }

    .tabs button.active::before {
        transform: translateY(0);
        height: 4px;
    }

    .tabs button.current-step {
        background: rgba(99, 102, 241, 0.15);
        border-bottom: 3px solid #6366f1;
    }

    .tab-content {
        display: none;
        animation: slideInFade 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        border-radius: 0 0 12px 12px;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes slideInFade {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tab-number {
        font-size: 10px;
        opacity: 0.7;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .tab-icon {
        font-size: 24px;
        margin: 0;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        transition: transform 0.3s ease;
    }

    .tab-label {
        font-size: 11px;
        line-height: 1.3;
        text-align: center;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    .tabs button:hover .tab-icon {
        transform: scale(1.1) rotate(5deg);
    }

    /* Container override for voyagement layout */
    .content-container > .container {
        max-width: 100%;
        margin: 0;
        border-radius: 0;
        box-shadow: none;
        background: transparent;
    }

    @media (max-width: 768px) {
        .tabs {
            flex-wrap: wrap;
            height: auto;
        }

        .tabs li {
            flex: 1 1 50%;
            min-width: 140px;
        }

        .tabs li:nth-child(5) {
            flex: 1 1 100%;
        }

        .tabs button {
            padding: 12px 6px;
            font-size: 11px;
            min-height: 70px;
            gap: 4px;
        }

        .tab-number {
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        .tab-icon {
            font-size: 18px;
        }

        .tab-label {
            font-size: 10px;
            line-height: 1.2;
        }

        .tabs button:hover .tab-icon {
            transform: scale(1.05);
        }

        .tab-content {
            border-radius: 0;
        }

        /* Form responsive adjustments */
        .form-row,
        .form-row.form-row-3,
        .form-row.form-row-4 {
            grid-template-columns: 1fr;
        }

        .form-section {
            margin: 0 15px 30px 15px;
            padding: 20px;
        }

        .btn {
            padding: 14px 30px;
            font-size: 0.9em;
            width: 100%;
        }

        .form-actions {
            flex-direction: column;
            gap: 15px;
        }
    }

    @media (min-width: 769px) and (max-width: 1024px) {
        .tabs button {
            padding: 14px 6px;
            font-size: 12px;
            min-height: 75px;
        }

        .tab-icon {
            font-size: 20px;
        }

        .form-row.form-row-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1025px) and (max-width: 1200px) {
        .form-row.form-row-4 {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>
<?php include 'includes/layout-start.php'; ?>
                <div class="container">
                    <header>
                        <h1>🚢 Biosecurity Information System - Samoa</h1>
                        <p>
                            <?php if (isset($edit_mode) && $edit_mode && isset($voyage_data) && $voyage_data): ?>
                                <strong>Edit Mode:</strong> Voyage #<?php echo htmlspecialchars($voyage_data['VoyageNo']); ?> - <?php echo htmlspecialchars($voyage_data['VesselID']); ?>
                            <?php elseif (isset($edit_mode) && $edit_mode): ?>
                                <strong>Edit Mode:</strong> Voyage ID #<?php echo $edit_voyage_id; ?>
                            <?php else: ?>
                                Vessel Voyage and Passenger Inspection Management
                            <?php endif; ?>
                        </p>
                    </header>

                    <ul class="tabs" role="tablist">
                        <li>
                            <button class="tab-link active" onclick="openTab(event, 'voyageTab')" role="tab" aria-selected="true">
                                <span class="tab-number">Step 1</span>
                                <span class="tab-icon">🚢</span>
                                <span class="tab-label">Voyage Details</span>
                            </button>
                        </li>
                        <li>
                            <button class="tab-link" onclick="openTab(event, 'inspectionTab')" role="tab" aria-selected="false">
                                <span class="tab-number">Step 2</span>
                                <span class="tab-icon">🔍</span>
                                <span class="tab-label">Passenger<br>Inspection</span>
                            </button>
                        </li>
                        <li>
                            <button class="tab-link" onclick="openTab(event, 'seizureTab')" role="tab" aria-selected="false">
                                <span class="tab-number">Step 3</span>
                                <span class="tab-icon">⚠️</span>
                                <span class="tab-label">Passenger<br>Seizure</span>
                            </button>
                        </li>
                        <li>
                            <button class="tab-link" onclick="openTab(event, 'cargoSeizureTab')" role="tab" aria-selected="false">
                                <span class="tab-number">Step 4</span>
                                <span class="tab-icon">🚨</span>
                                <span class="tab-label">Cargo<br>Seizure</span>
                            </button>
                        </li>
                        <li>
                            <button class="tab-link" onclick="openTab(event, 'cargoReleaseTab')" role="tab" aria-selected="false">
                                <span class="tab-number">Step 5</span>
                                <span class="tab-icon">📦</span>
                                <span class="tab-label">Cargo<br>Release</span>
                            </button>
                        </li>
                    </ul>

                    <div id="voyageTab" class="tab-content active" role="tabpanel">
                        <?php include 'voyage_form.php'; ?>
                    </div>

                    <div id="inspectionTab" class="tab-content" role="tabpanel">
                        <?php include 'inspection_form.php'; ?>
                    </div>

                    <div id="seizureTab" class="tab-content" role="tabpanel">
                        <?php include 'seizure_form.php'; ?>
                    </div>

                    <div id="cargoSeizureTab" class="tab-content" role="tabpanel">
                        <?php include 'cargo_seizure_form.php'; ?>
                    </div>

                    <div id="cargoReleaseTab" class="tab-content" role="tabpanel">
                        <?php include 'cargo_release_form.php'; ?>
                    </div>
                </div>

<?php include 'includes/layout-end.php'; ?>
    <script>
        // Tab hash fragment support - open correct tab on page load
        function activateTabFromHash() {
            const hash = window.location.hash.replace('#', '');
            if (hash && ['voyageTab', 'inspectionTab', 'seizureTab', 'cargoSeizureTab', 'cargoReleaseTab'].includes(hash)) {
                const tabButton = document.querySelector(`.tab-link[onclick*="${hash}"]`);
                if (tabButton) {
                    tabButton.click();
                }
            }
        }

        // Update URL hash when tab changes
        function updateTabHash(tabName) {
            history.replaceState(null, '', '#' + tabName);
        }

        // Load saved theme and initialize edit mode
        document.addEventListener('DOMContentLoaded', function() {
            // Handle URL hash for direct tab navigation
            activateTabFromHash();

            // Load locations for dropdown
            loadLocationDropdown();
            // Load countries for dropdown
            loadCountryDropdowns();
            // Load ports for dropdown
            loadPortsDropdown();

            // Initialize edit mode if voyage_id is present
            const appWrapper = document.querySelector('.app-wrapper');
            const voyageId = appWrapper.getAttribute('data-voyage-id');
            if (voyageId) {
                initializeEditMode(voyageId);
            }
        });

        // Load locations dropdown
        async function loadLocationDropdown() {
            const locationSelect = document.getElementById('LocationID');
            const loadingDiv = document.getElementById('locationLoading');

            if (!locationSelect) {
                console.log('Location dropdown not found on current page');
                return;
            }

            try {
                loadingDiv.textContent = 'Loading locations...';
                loadingDiv.style.color = '#666';

                const response = await fetch('api/get_locations.php');
                const result = await response.json();

                if (result.success) {
                    // Clear existing options except the first one
                    locationSelect.innerHTML = '<option value="">Select a location...</option>';

                    // Group locations by region
                    const locationsByRegion = {};
                    result.data.forEach(location => {
                        if (!locationsByRegion[location.region]) {
                            locationsByRegion[location.region] = [];
                        }
                        locationsByRegion[location.region].push(location);
                    });

                    // Add grouped options
                    Object.keys(locationsByRegion).sort().forEach(region => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = `📍 ${region}`;

                        locationsByRegion[region].sort((a, b) => a.location_name.localeCompare(b.location_name));

                        locationsByRegion[region].forEach(location => {
                            const option = document.createElement('option');
                            option.value = location.location_id;
                            option.textContent = `${location.location_name} (${location.location_type})`;
                            if (!location.is_active) {
                                option.textContent += ' [Inactive]';
                                option.style.color = '#999';
                            }
                            optgroup.appendChild(option);
                        });

                        locationSelect.appendChild(optgroup);
                    });

                    loadingDiv.textContent = `${result.data.length} locations loaded`;
                    loadingDiv.style.color = '#28a745';
                } else {
                    loadingDiv.textContent = 'Error loading locations';
                    loadingDiv.style.color = '#dc3545';
                }
            } catch (error) {
                console.error('Error loading locations:', error);
                loadingDiv.textContent = 'Network error loading locations';
                loadingDiv.style.color = '#dc3545';
            }
        }

        // Load countries dropdown
        async function loadCountryDropdowns() {
            const portOfLoadingSelect = document.getElementById('PortOfLoadingID');
            if (portOfLoadingSelect && portOfLoadingSelect.tagName === 'SELECT') {
                await loadCountryDropdown(portOfLoadingSelect, 'Port of Loading');
            }

            const lastPortSelect = document.getElementById('LastPortID');
            if (lastPortSelect && lastPortSelect.tagName === 'SELECT') {
                await loadCountryDropdown(lastPortSelect, 'Last Port');
            }
        }

        async function loadCountryDropdown(selectElement, fieldName) {
            try {
                const response = await fetch('api/get_countries.php');
                const result = await response.json();

                if (result.success) {
                    const currentValue = selectElement.value;
                    selectElement.innerHTML = '<option value="">Select a country...</option>';

                    const countriesByLetter = {};
                    result.data.forEach(country => {
                        if (country.CountryName && country.CountryName.trim() !== '') {
                            const firstLetter = country.CountryName.charAt(0).toUpperCase();
                            if (!countriesByLetter[firstLetter]) {
                                countriesByLetter[firstLetter] = [];
                            }
                            countriesByLetter[firstLetter].push(country);
                        }
                    });

                    Object.keys(countriesByLetter).sort().forEach(letter => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = `${letter}`;

                        countriesByLetter[letter].sort((a, b) => a.CountryName.localeCompare(b.CountryName));

                        countriesByLetter[letter].forEach(country => {
                            const option = document.createElement('option');
                            option.value = country.CountryID;
                            option.textContent = country.CountryName;
                            optgroup.appendChild(option);
                        });

                        selectElement.appendChild(optgroup);
                    });

                    if (currentValue) {
                        const existingOption = Array.from(selectElement.options).find(option => option.value === currentValue);
                        if (existingOption) {
                            selectElement.value = currentValue;
                        }
                    }

                    console.log(`Loaded ${result.data.length} countries for ${fieldName}`);
                } else {
                    console.error('Error loading countries:', result.message);
                }
            } catch (error) {
                console.error('Error loading countries:', error);
            }
        }

        // Load ports dropdown for Port of Arrival
        async function loadPortsDropdown() {
            const portSelect = document.getElementById('PortOfArrivalID');
            if (!portSelect) return;

            if (portSelect.tagName !== 'SELECT') {
                console.log('PortOfArrivalID is not a select element, skipping port loading');
                return;
            }

            try {
                const response = await fetch('api/get_ports.php');
                const result = await response.json();

                if (result.success && result.data) {
                    const currentValue = portSelect.value;
                    portSelect.innerHTML = '<option value="">Select a port...</option>';

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

                        portSelect.appendChild(optgroup);
                    });

                    if (currentValue) {
                        const existingOption = Array.from(portSelect.options).find(option => option.value === currentValue);
                        if (existingOption) {
                            portSelect.value = currentValue;
                        }
                    }

                    console.log(`Loaded ${result.data.length} ports`);
                } else {
                    console.error('Error loading ports:', result.message);
                }
            } catch (error) {
                console.error('Error loading ports:', error);
            }
        }

        // Initialize edit mode functionality
        function initializeEditMode(voyageId) {
            window.currentVoyageId = voyageId;
            loadVoyageData(voyageId);
            loadVoyageStatus(voyageId);
        }

        async function loadVoyageData(voyageId) {
            try {
                const response = await fetch(`api/voyage_details.php?id=${voyageId}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const voyage = result.data;

                    populateVoyageForm(voyage);

                    if (voyage.container_counts) {
                        populateContainerCounts(voyage.container_counts);
                    }

                    if (voyage.passenger_inspections) {
                        populatePassengerInspections(voyage.passenger_inspections);
                    }

                    if (voyage.passenger_seizures) {
                        populatePassengerSeizures(voyage.passenger_seizures);
                    }

                    if (voyage.cargo_seizures) {
                        populateCargoSeizures(voyage.cargo_seizures);
                    }

                    if (voyage.cargo_releases) {
                        populateCargoReleases(voyage.cargo_releases);
                    }
                } else {
                    console.error('Failed to load voyage data:', result.message);
                }
            } catch (error) {
                console.error('Error loading voyage data:', error);
            }
        }

        async function loadVoyageStatus(voyageId) {
            try {
                const response = await fetch(`api/voyage_status.php?id=${voyageId}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const status = result.data;

                    updateStatusIndicators(status);

                    if (status.current_step) {
                        const stepMap = {
                            'voyage_details': 'voyageTab',
                            'passenger_inspection': 'inspectionTab',
                            'passenger_seizure': 'seizureTab',
                            'cargo_seizure': 'cargoSeizureTab',
                            'cargo_release': 'cargoReleaseTab'
                        };

                        const targetTab = stepMap[status.current_step];
                        if (targetTab) {
                            const tabButton = document.querySelector(`.tab-link[onclick*="${targetTab}"]`);
                            if (tabButton) {
                                tabButton.click();
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading voyage status:', error);
            }
        }

        function populateVoyageForm(voyage) {
            Object.keys(voyage).forEach(key => {
                const element = document.getElementById(key);
                if (element && element.type !== 'hidden') {
                    element.value = voyage[key] || '';
                }
            });
        }

        function populateContainerCounts(containerCounts) {
            if (!containerCounts || containerCounts.length === 0) {
                console.log('No container counts to populate');
                return;
            }

            const containerInputs = document.querySelectorAll('[id*="CC_"][type="text"]');

            containerInputs.forEach(input => {
                input.value = '';
            });

            containerCounts.forEach(container => {
                const fieldId = `CC_${container.container_type_code}`;
                const input = document.getElementById(fieldId);
                if (input) {
                    input.value = container.count || '';
                    input.dispatchEvent(new Event('input'));
                }
            });

            console.log('Loaded container counts:', containerCounts.length);
        }

        function populatePassengerInspections(inspections) {
            if (!inspections || inspections.length === 0) {
                console.log('No passenger inspections to populate');
                return;
            }

            const inspection = inspections[0];
            Object.keys(inspection).forEach(key => {
                const element = document.getElementById(key);
                if (element && element.type !== 'hidden') {
                    element.value = inspection[key] || '';
                }
                const select = document.getElementById(key);
                if (select && select.tagName === 'SELECT' && inspection[key]) {
                    select.value = inspection[key];
                    select.dispatchEvent(new Event('change'));
                }
            });

            console.log('Loaded passenger inspection:', inspection);
        }

        function populatePassengerSeizures(seizures) {
            if (!seizures || seizures.length === 0) {
                console.log('No passenger seizures to populate');
                return;
            }

            const seizure = seizures[0];

            const existingForms = document.querySelectorAll('[id^="item-"]');
            existingForms.forEach(form => form.remove());

            Object.keys(seizure).forEach(key => {
                const element = document.getElementById(key);
                if (element && element.type !== 'hidden') {
                    element.value = seizure[key] || '';
                }
                const select = document.getElementById(key);
                if (select && select.tagName === 'SELECT' && seizure[key]) {
                    select.value = seizure[key];
                    select.dispatchEvent(new Event('change'));
                }
                const radios = document.querySelectorAll(`input[name="${key}"]`);
                if (radios.length > 0 && seizure[key]) {
                    radios.forEach(radio => {
                        if (radio.value === seizure[key]) {
                            radio.checked = true;
                        }
                    });
                }
            });

            console.log('Loaded passenger seizure:', seizure);
        }

        function populateCargoSeizures(seizures) {
            if (!seizures || seizures.length === 0) {
                console.log('No cargo seizures to populate');
                return;
            }

            const seizure = seizures[0];

            Object.keys(seizure).forEach(key => {
                let element = document.getElementById(key);
                if (!element) {
                    element = document.getElementById(`cs_${key}`);
                }

                if (element && element.type !== 'hidden') {
                    element.value = seizure[key] || '';
                }
                if (element && element.tagName === 'SELECT' && seizure[key]) {
                    element.value = seizure[key];
                    element.dispatchEvent(new Event('change'));
                }
            });

            console.log('Loaded cargo seizure:', seizure);
        }

        function populateCargoReleases(releases) {
            if (!releases || releases.length === 0) {
                console.log('No cargo releases to populate');
                return;
            }

            const release = releases[0];

            Object.keys(release).forEach(key => {
                const element = document.getElementById(key);
                if (element && element.type !== 'hidden') {
                    element.value = release[key] || '';
                }
                if (element && element.tagName === 'SELECT' && release[key]) {
                    element.value = release[key];
                    element.dispatchEvent(new Event('change'));
                }
            });

            console.log('Loaded cargo release:', release);
        }

        function updateStatusIndicators(status) {
            console.log('Updating status indicators:', status);

            const steps = [
                { key: 'voyage_details_complete', tabId: 'voyageTab', tabSelector: '[onclick*="voyageTab"]' },
                { key: 'passenger_inspection_complete', tabId: 'inspectionTab', tabSelector: '[onclick*="inspectionTab"]' },
                { key: 'passenger_seizure_complete', tabId: 'seizureTab', tabSelector: '[onclick*="seizureTab"]' },
                { key: 'cargo_seizure_complete', tabId: 'cargoSeizureTab', tabSelector: '[onclick*="cargoSeizureTab"]' },
                { key: 'cargo_release_complete', tabId: 'cargoReleaseTab', tabSelector: '[onclick*="cargoReleaseTab"]' }
            ];

            steps.forEach(step => {
                const tabButton = document.querySelector(step.tabSelector);
                if (tabButton) {
                    const existingIcon = tabButton.querySelector('.step-complete-icon');
                    if (existingIcon) {
                        existingIcon.remove();
                    }

                    if (status[step.key]) {
                        const icon = document.createElement('span');
                        icon.className = 'step-complete-icon';
                        icon.innerHTML = ' ✓';
                        icon.style.cssText = 'position: absolute; top: 8px; right: 8px; color: #10b981; font-size: 1.2em; font-weight: bold;';
                        icon.title = 'Completed';
                        tabButton.style.position = 'relative';
                        tabButton.appendChild(icon);

                        const tabContent = document.getElementById(step.tabId);
                        if (tabContent) {
                            tabContent.classList.add('step-completed');
                        }
                    }
                }
            });
        }

        function openTab(evt, tabName) {
            var tabContent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabContent.length; i++) {
                tabContent[i].classList.remove("active");
            }

            var tabLinks = document.getElementsByClassName("tab-link");
            for (var i = 0; i < tabLinks.length; i++) {
                tabLinks[i].classList.remove("active");
            }

            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");

            // Update URL hash
            updateTabHash(tabName);

            // Trigger tab-specific checks
            if (tabName === 'inspectionTab' && typeof checkActiveVoyage === 'function') {
                checkActiveVoyage();
            }
            if (tabName === 'seizureTab' && typeof checkActiveVoyageSeizure === 'function') {
                checkActiveVoyageSeizure();
            }
            if (tabName === 'cargoReleaseTab' && typeof checkActiveVoyageRelease === 'function') {
                checkActiveVoyageRelease();
            }
            if (tabName === 'cargoSeizureTab' && typeof checkActiveVoyageCargoSeizure === 'function') {
                checkActiveVoyageCargoSeizure();
            }
            if (tabName === 'seizureTab' && typeof loadCountryDropdownForSeizure === 'function') {
                setTimeout(loadCountryDropdownForSeizure, 100);
            }
            if (tabName === 'cargoSeizureTab' && typeof loadCountryDropdownForCargoSeizure === 'function') {
                setTimeout(loadCountryDropdownForCargoSeizure, 100);
            }
        }
    </script>

    <script src="script.js?v=<?php echo CSS_CACHE_BUST; ?>"></script>
    <script src="inspection_script.js?v=<?php echo CSS_CACHE_BUST; ?>"></script>
    <script src="seizure_script.js?v=<?php echo CSS_CACHE_BUST; ?>"></script>
    <script src="cargo_release_script.js?v=<?php echo CSS_CACHE_BUST; ?>"></script>
    <script src="cargo_seizure_script.js?v=<?php echo CSS_CACHE_BUST; ?>"></script>
