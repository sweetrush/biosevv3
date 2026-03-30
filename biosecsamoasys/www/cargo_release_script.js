// Global counter for release items
let releaseItemCounter = 0;

// Track voyage loading state
let voyagesLoadedRelease = false;
let voyagesLoadingRelease = null;

// Load data when the cargo release tab is accessed
document.addEventListener('DOMContentLoaded', function() {
    // Start loading voyages and store the promise
    voyagesLoadingRelease = loadVoyagesForRelease();

    loadCommoditiesForRelease();
    loadRecentReleases();

    // Check active voyage after voyages are loaded
    voyagesLoadingRelease.then(() => {
        checkActiveVoyageRelease();
    }).catch(error => {
        console.error('Error in voyage loading promise chain:', error);
    });

    // Set today's date for release date
    const releaseDateField = document.getElementById('ReleaseDate');
    if (releaseDateField) {
        const today = new Date().toISOString().split('T')[0];
        releaseDateField.value = today;
    }
});

// Check for active voyage in localStorage
function checkActiveVoyageRelease() {
    const activeVoyageID = localStorage.getItem('activeVoyageID');
    const activeVoyageNo = localStorage.getItem('activeVoyageNo');
    const activeVesselID = localStorage.getItem('activeVesselID');

    if (activeVoyageID) {
        // Show active voyage context
        document.getElementById('activeVoyageContextRelease').style.display = 'block';
        document.getElementById('contextVoyageIDRelease').textContent = activeVoyageID;
        document.getElementById('contextVoyageNoRelease').textContent = activeVoyageNo || 'N/A';
        document.getElementById('contextVesselIDRelease').textContent = activeVesselID || 'N/A';

        // Hide the voyage selection dropdown section
        document.getElementById('voyageSelectionSectionRelease').style.display = 'none';

        // Set the voyage ID in the select and hidden field (wait for voyages to load if needed)
        const voyageSelect = document.getElementById('ReleaseVoyageID');
        const voyageHidden = document.getElementById('ReleaseVoyageIDHidden');
        if (voyagesLoadedRelease) {
            if (voyageSelect) voyageSelect.value = activeVoyageID;
            if (voyageHidden) voyageHidden.value = activeVoyageID;
        } else if (voyagesLoadingRelease) {
            voyagesLoadingRelease.then(() => {
                if (voyageSelect) voyageSelect.value = activeVoyageID;
                if (voyageHidden) voyageHidden.value = activeVoyageID;
            });
        }
    } else {
        // Show voyage selection section
        document.getElementById('activeVoyageContextRelease').style.display = 'none';
        document.getElementById('voyageSelectionSectionRelease').style.display = 'block';
    }
}

// Clear active voyage
function clearActiveVoyageRelease() {
    localStorage.removeItem('activeVoyageID');
    localStorage.removeItem('activeVoyageNo');
    localStorage.removeItem('activeVesselID');
    checkActiveVoyageRelease();
}

// Load voyages for dropdown
async function loadVoyagesForRelease() {
    try {
        const response = await fetch('api/get_voyages.php');
        const data = await response.json();

        if (data.success && data.data) {
            const voyageSelect = document.getElementById('ReleaseVoyageID');
            if (voyageSelect) {
                voyageSelect.innerHTML = '<option value="">Select Voyage...</option>';

                data.data.forEach(voyage => {
                    const option = document.createElement('option');
                    option.value = voyage.VoyageID;
                    option.textContent = `${voyage.VoyageNo} - ${voyage.VesselID} (${voyage.ArrivalDate})`;
                    voyageSelect.appendChild(option);
                });

                // Auto-select active voyage if exists
                const activeVoyageID = localStorage.getItem('activeVoyageID');
                if (activeVoyageID) {
                    voyageSelect.value = activeVoyageID;
                }

                // Update hidden field when selection changes
                voyageSelect.addEventListener('change', function() {
                    const voyageHidden = document.getElementById('ReleaseVoyageIDHidden');
                    if (voyageHidden) {
                        voyageHidden.value = this.value;
                    }
                });
                voyagesLoadedRelease = true;
            }
        }
    } catch (error) {
        console.error('Error loading voyages:', error);
    }
}

// Load commodities for dropdown
function loadCommoditiesForRelease() {
    fetch('api/get_commodities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Store commodities globally for use in dynamic items
                window.commoditiesData = data.data;
            }
        })
        .catch(error => console.error('Error loading commodities:', error));
}

// Generate commodity options HTML
function generateCommodityOptions() {
    if (!window.commoditiesData) return '<option value="">Loading...</option>';

    let html = '<option value="">Select Commodity...</option>';
    let currentGroup = '';
    let optgroupHtml = '';

    window.commoditiesData.forEach(commodity => {
        if (commodity.CommodityGroupID !== currentGroup) {
            if (optgroupHtml) {
                html += optgroupHtml + '</optgroup>';
            }
            optgroupHtml = '<optgroup label="' + formatGroupNameRelease(commodity.CommodityGroupID) + '">';
            currentGroup = commodity.CommodityGroupID;
        }
        optgroupHtml += '<option value="' + commodity.CommodityType + '">' + commodity.CommodityType + '</option>';
    });

    if (optgroupHtml) {
        html += optgroupHtml + '</optgroup>';
    }

    return html;
}

// Format group name for display
function formatGroupNameRelease(groupId) {
    const groupNames = {
        '1': '🐄 Animals and Animal Products',
        '2': '🌱 Plants and Plant Products',
        '3': '🚗 MEV (Machinery, Equipment, Vehicles)',
        '4': '📦 Other',
        '5': '⚠️ Pesticides'
    };
    return groupNames[groupId] || groupId;
}

// Add release item to the form
function addReleaseItem() {
    releaseItemCounter++;
    const container = document.getElementById('releaseItemsContainer');

    const itemDiv = document.createElement('div');
    itemDiv.className = 'release-item';
    itemDiv.id = 'releaseItem' + releaseItemCounter;

    itemDiv.innerHTML = `
        <div class="release-item-header">
            <h3>Release Item #${releaseItemCounter}</h3>
            <button type="button" class="remove-item-btn" onclick="removeReleaseItem(${releaseItemCounter})">
                ✕ Remove
            </button>
        </div>

        <div class="item-section">
            <h4>General Information</h4>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="ContainerBLNo_${releaseItemCounter}">Container/BL No</label>
                    <input type="text" id="ContainerBLNo_${releaseItemCounter}" name="items[${releaseItemCounter}][ContainerBLNo]">
                </div>
                <div class="form-group">
                    <label for="Action_${releaseItemCounter}">Action</label>
                    <select id="Action_${releaseItemCounter}" name="items[${releaseItemCounter}][Action]">
                        <option value="">Select...</option>
                        <option value="Unconditional Release">Unconditional Release</option>
                        <option value="Transfer Depot">Transfer Depot</option>
                        <option value="Conditional Release">Conditional Release</option>
                        <option value="Hold">Hold</option>
                        <option value="Re-export">Re-export</option>
                        <option value="Destroy">Destroy</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="Description_${releaseItemCounter}">Description</label>
                    <textarea id="Description_${releaseItemCounter}" name="items[${releaseItemCounter}][Description]" rows="2"></textarea>
                </div>
            </div>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="Quantity_${releaseItemCounter}">Quantity</label>
                    <input type="number" id="Quantity_${releaseItemCounter}" name="items[${releaseItemCounter}][Quantity]" min="0" step="0.01" placeholder="0">
                </div>
                <div class="form-group">
                    <label for="Unit_${releaseItemCounter}">Unit</label>
                    <input type="text" id="Unit_${releaseItemCounter}" name="items[${releaseItemCounter}][Unit]" placeholder="kg, pcs, etc.">
                </div>
                <div class="form-group">
                    <label for="Weight_${releaseItemCounter}">Weight</label>
                    <input type="number" id="Weight_${releaseItemCounter}" name="items[${releaseItemCounter}][Weight]" min="0" step="0.01" placeholder="0">
                </div>
            </div>
            <div class="form-row" id="transferDepotRow_${releaseItemCounter}" style="display: none;">
                <div class="form-group">
                    <label for="TransferDepot_${releaseItemCounter}">Transfer Depot</label>
                    <input type="text" id="TransferDepot_${releaseItemCounter}" name="items[${releaseItemCounter}][TransferDepot]">
                </div>
            </div>
        </div>

        <div class="item-section">
            <h4>Quarantinable Material</h4>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="CommodityType_${releaseItemCounter}">Commodity Type</label>
                    <select id="CommodityType_${releaseItemCounter}" name="items[${releaseItemCounter}][CommodityType]">
                        ${generateCommodityOptions()}
                    </select>
                </div>
                <div class="form-group">
                    <label for="ItemCondition_${releaseItemCounter}">Condition</label>
                    <input type="text" id="ItemCondition_${releaseItemCounter}" name="items[${releaseItemCounter}][ItemCondition]">
                </div>
            </div>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="PermitNo_${releaseItemCounter}">Permit No</label>
                    <input type="text" id="PermitNo_${releaseItemCounter}" name="items[${releaseItemCounter}][PermitNo]">
                </div>
                <div class="form-group">
                    <label for="CertificateNo_${releaseItemCounter}">Certificate No</label>
                    <input type="text" id="CertificateNo_${releaseItemCounter}" name="items[${releaseItemCounter}][CertificateNo]">
                </div>
                <div class="form-group">
                    <label for="CountryOfOrigin_${releaseItemCounter}">Country of Origin</label>
                    <input type="text" id="CountryOfOrigin_${releaseItemCounter}" name="items[${releaseItemCounter}][CountryOfOrigin]">
                </div>
            </div>
        </div>
    `;

    container.appendChild(itemDiv);

    // Add event listener to show/hide Transfer Depot field based on Action selection
    const actionSelect = document.getElementById('Action_' + releaseItemCounter);
    if (actionSelect) {
        actionSelect.addEventListener('change', function() {
            const transferDepotRow = document.getElementById('transferDepotRow_' + releaseItemCounter);
            if (this.value === 'Transfer Depot') {
                transferDepotRow.style.display = 'block';
            } else {
                transferDepotRow.style.display = 'none';
            }
        });
    }
}

// Remove release item
function removeReleaseItem(itemId) {
    const item = document.getElementById('releaseItem' + itemId);
    if (item) {
        item.remove();
    }
}

// Load recent releases
function loadRecentReleases() {
    fetch('api/get_recent_releases.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const container = document.getElementById('recentReleases');
                if (container) {
                    if (data.data.length === 0) {
                        container.innerHTML = '<p>No cargo releases recorded yet.</p>';
                        return;
                    }

                    let html = '<table style="width: 100%; border-collapse: collapse;">';
                    html += '<thead><tr style="background: #3498db; color: white;">';
                    html += '<th style="padding: 10px; text-align: left;">ID</th>';
                    html += '<th style="padding: 10px; text-align: left;">Voyage</th>';
                    html += '<th style="padding: 10px; text-align: left;">Release No</th>';
                    html += '<th style="padding: 10px; text-align: left;">Importer</th>';
                    html += '<th style="padding: 10px; text-align: left;">Type</th>';
                    html += '<th style="padding: 10px; text-align: left;">Date</th>';
                    html += '<th style="padding: 10px; text-align: left;">Items</th>';
                    html += '<th style="padding: 10px; text-align: left;">Total Costs</th>';
                    html += '</tr></thead><tbody>';

                    data.data.forEach((release, index) => {
                        const bgColor = index % 2 === 0 ? '#f8f9fa' : 'white';

                        html += `<tr style="background: ${bgColor};">`;
                        html += `<td style="padding: 8px;">${release.ReleaseID}</td>`;
                        html += `<td style="padding: 8px;">${release.VoyageNo || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${release.ReleaseNo || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${release.Importer || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${release.ReleaseType || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${release.ReleaseDate || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${release.ItemCount || '0'} items</td>`;
                        html += `<td style="padding: 8px;">$${parseFloat(release.TotalCosts || 0).toFixed(2)}</td>`;
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    container.innerHTML = html;
                }
            }
        })
        .catch(error => console.error('Error loading recent releases:', error));
}

// Handle cargo release form submission
const cargoReleaseForm = document.getElementById('cargoReleaseForm');
if (cargoReleaseForm) {
    cargoReleaseForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const messageDiv = document.getElementById('releaseMessage');

        // Get voyage ID from either the active voyage or the dropdown
        let voyageID = document.getElementById('ReleaseVoyageIDHidden').value;
        if (!voyageID) {
            voyageID = document.getElementById('ReleaseVoyageID').value;
        }

        if (!voyageID) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Error: Please select a voyage';
            messageDiv.style.display = 'block';
            return;
        }

        // Get CSRF token from the hidden field
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';

        // Collect form data
        const formData = {
            csrf_token: csrfToken,
            VoyageID: voyageID,
            ReleaseNo: document.getElementById('ReleaseNo').value,
            Importer: document.getElementById('ReleaseImporter').value,
            ReleaseType: document.getElementById('ReleaseType').value,
            ReleaseDate: document.getElementById('ReleaseDate').value,
            TotalCosts: document.getElementById('TotalCosts').value,
            Comments: document.getElementById('ReleaseComments').value,
            items: []
        };

        // Collect all release items
        const releaseItems = document.querySelectorAll('.release-item');
        releaseItems.forEach(item => {
            const itemId = item.id.replace('releaseItem', '');
            const itemData = {
                ContainerBLNo: document.getElementById('ContainerBLNo_' + itemId)?.value || '',
                Description: document.getElementById('Description_' + itemId)?.value || '',
                Quantity: document.getElementById('Quantity_' + itemId)?.value || '',
                Unit: document.getElementById('Unit_' + itemId)?.value || '',
                Weight: document.getElementById('Weight_' + itemId)?.value || '',
                Action: document.getElementById('Action_' + itemId)?.value || '',
                CommodityType: document.getElementById('CommodityType_' + itemId)?.value || '',
                ItemCondition: document.getElementById('ItemCondition_' + itemId)?.value || '',
                PermitNo: document.getElementById('PermitNo_' + itemId)?.value || '',
                CertificateNo: document.getElementById('CertificateNo_' + itemId)?.value || '',
                CountryOfOrigin: document.getElementById('CountryOfOrigin_' + itemId)?.value || '',
                TransferDepot: document.getElementById('TransferDepot_' + itemId)?.value || ''
            };
            formData.items.push(itemData);
        });

        fetch('api/submit_cargo_release.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.className = data.warning ? 'message warning' : 'message success';
                messageDiv.textContent = data.warning || (data.message + ' (Release ID: ' + data.release_id + ')');
                messageDiv.style.display = 'block';

                // Refresh progress indicators
                if (typeof loadVoyageStatus === 'function') loadVoyageStatus(formData.VoyageID);

                // Reset form
                cargoReleaseForm.reset();

                // Clear release items
                document.getElementById('releaseItemsContainer').innerHTML = '';
                releaseItemCounter = 0;

                // Reset release date to today
                const releaseDateField = document.getElementById('ReleaseDate');
                if (releaseDateField) {
                    const today = new Date().toISOString().split('T')[0];
                    releaseDateField.value = today;
                }

                // Reload recent releases
                loadRecentReleases();

                // Scroll to message
                messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                // Hide message after 5 seconds
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 5000);
            } else {
                messageDiv.className = 'message error';
                messageDiv.textContent = 'Error: ' + data.message;
                messageDiv.style.display = 'block';
            }
        })
        .catch(error => {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Error submitting form: ' + error.message;
            messageDiv.style.display = 'block';
        });
    });
}
