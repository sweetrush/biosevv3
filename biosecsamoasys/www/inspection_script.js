// Track voyage loading state
let voyagesLoaded = false;
let voyagesLoading = null;

// Load data when the inspection tab is accessed
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date for modified date
    const modifiedDateField = document.getElementById('InspectionModifiedDate');
    if (modifiedDateField) {
        const today = new Date().toISOString().split('T')[0];
        modifiedDateField.value = today;
    }

    // Start loading voyages and store the promise
    voyagesLoading = loadVoyages();

    loadCommodities();
    loadLocations();

    // Check active voyage after voyages are loaded, then load voyage-specific inspections
    voyagesLoading.then(() => {
        checkActiveVoyage();
        // Load inspections for the active voyage
        const activeVoyageID = localStorage.getItem('activeVoyageID');
        if (activeVoyageID) {
            loadVoyageInspections(activeVoyageID);
        } else {
            loadRecentInspections();
        }
    }).catch(error => {
        console.error('Error in voyage loading promise chain:', error);
    });

    // Add event listeners for compliance calculation
    const consignmentsField = document.getElementById('NoOfConsignments');
    const nonCompliantField = document.getElementById('NoOfNonCompliant');

    if (consignmentsField && nonCompliantField) {
        consignmentsField.addEventListener('input', calculateCompliance);
        nonCompliantField.addEventListener('input', calculateCompliance);
    }

    // Add event listener for commodity selection
    const commoditySelect = document.getElementById('CommodityTypeID');
    if (commoditySelect) {
        commoditySelect.addEventListener('change', showCommodityInfo);
    }
});

// Check for active voyage in localStorage
function checkActiveVoyage() {
    const activeVoyageID = localStorage.getItem('activeVoyageID');
    const activeVoyageNo = localStorage.getItem('activeVoyageNo');
    const activeVesselID = localStorage.getItem('activeVesselID');

    if (activeVoyageID) {
        // Show active voyage context
        document.getElementById('activeVoyageContext').style.display = 'block';
        document.getElementById('contextVoyageID').textContent = activeVoyageID;
        document.getElementById('contextVoyageNo').textContent = activeVoyageNo || 'N/A';
        document.getElementById('contextVesselID').textContent = activeVesselID || 'N/A';

        // Hide the voyage selection dropdown section
        document.getElementById('voyageSelectionSection').style.display = 'none';

        // Set the voyage ID in the select dropdown (wait for voyages to load if needed)
        const voyageSelect = document.getElementById('InspectionVoyageID');
        if (voyageSelect) {
            if (voyagesLoaded) {
                voyageSelect.value = activeVoyageID;
            } else if (voyagesLoading) {
                voyagesLoading.then(() => {
                    voyageSelect.value = activeVoyageID;
                });
            }
        }

        // Load inspections for this voyage
        loadVoyageInspections(activeVoyageID);
    } else {
        // Show voyage selection section
        document.getElementById('activeVoyageContext').style.display = 'none';
        document.getElementById('voyageSelectionSection').style.display = 'block';
        // Show all recent inspections when no active voyage
        loadRecentInspections();
    }
}

// Clear active voyage
function clearActiveVoyage() {
    localStorage.removeItem('activeVoyageID');
    localStorage.removeItem('activeVoyageNo');
    localStorage.removeItem('activeVesselID');
    checkActiveVoyage();
}

// Load voyages for dropdown
async function loadVoyages() {
    try {
        const response = await fetch('api/get_voyages.php');
        const data = await response.json();
        console.log('Inspection: Loaded voyages:', data);

        if (data.success && data.data) {
            const voyageSelect = document.getElementById('InspectionVoyageID');
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
                console.log('Inspection: Active voyage from localStorage:', activeVoyageID);
                if (activeVoyageID) {
                    voyageSelect.value = activeVoyageID;
                    console.log('Inspection: Set dropdown to:', voyageSelect.value);
                }
                voyagesLoaded = true;
            }
        }
    } catch (error) {
        console.error('Error loading voyages:', error);
    }
}

// Load commodity types for dropdown
let commodityData = {};
function loadCommodities() {
    fetch('api/get_commodities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const commoditySelect = document.getElementById('CommodityTypeID');
                if (commoditySelect) {
                    commoditySelect.innerHTML = '<option value="">Select Commodity...</option>';

                    // Group commodities by CommodityGroupID
                    let currentGroup = '';
                    let optgroup = null;

                    data.data.forEach(commodity => {
                        commodityData[commodity.CommodityTypeID] = commodity;

                        // Create new optgroup if group changes
                        if (commodity.CommodityGroupID !== currentGroup) {
                            if (optgroup) {
                                commoditySelect.appendChild(optgroup);
                            }
                            optgroup = document.createElement('optgroup');
                            optgroup.label = formatGroupName(commodity.CommodityGroupID);
                            currentGroup = commodity.CommodityGroupID;
                        }

                        const option = document.createElement('option');
                        option.value = commodity.CommodityTypeID;
                        option.textContent = commodity.CommodityType;
                        option.dataset.group = commodity.CommodityGroupID;
                        optgroup.appendChild(option);
                    });

                    // Append last optgroup
                    if (optgroup) {
                        commoditySelect.appendChild(optgroup);
                    }
                }
            }
        })
        .catch(error => console.error('Error loading commodities:', error));
}

// Format group name for display
function formatGroupName(groupId) {
    const groupNames = {
        '1': '🐄 Animals and Animal Products',
        '2': '🌱 Plants and Plant Products',
        '3': '🚗 MEV (Machinery, Equipment, Vehicles)',
        '4': '📦 Other',
        '5': '⚠️ Pesticides'
    };
    return groupNames[groupId] || groupId;
}

// Load locations for dropdown
function loadLocations() {
    fetch('api/get_locations.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const locationSelect = document.getElementById('InspectionLocationID');
                if (locationSelect) {
                    locationSelect.innerHTML = '<option value="">Select Location...</option>';

                    data.data.forEach(location => {
                        const option = document.createElement('option');
                        option.value = location.location_id;
                        option.textContent = `${location.location_name} - ${location.port_area}`;
                        locationSelect.appendChild(option);
                    });
                }
            }
        })
        .catch(error => console.error('Error loading locations:', error));
}

// Show commodity information when selected
function showCommodityInfo() {
    const commoditySelect = document.getElementById('CommodityTypeID');
    const commodityInfo = document.getElementById('commodityInfo');
    const commodityDesc = document.getElementById('commodityDesc');

    if (commoditySelect && commoditySelect.value && commodityData[commoditySelect.value]) {
        const commodity = commodityData[commoditySelect.value];
        const groupLabel = formatGroupName(commodity.CommodityGroupID);
        commodityDesc.textContent = `Group: ${groupLabel} | Type: ${commodity.CommodityType}`;
        commodityInfo.style.display = 'block';
        commodityInfo.style.background = '#e3f2fd';
        commodityInfo.style.borderLeft = '4px solid #2196f3';
    } else {
        commodityInfo.style.display = 'none';
    }
}

// Calculate compliance rate
function calculateCompliance() {
    const consignments = parseInt(document.getElementById('NoOfConsignments').value) || 0;
    const nonCompliant = parseInt(document.getElementById('NoOfNonCompliant').value) || 0;

    const complianceStatus = document.getElementById('complianceStatus');
    const complianceRate = document.getElementById('complianceRate');

    if (consignments > 0) {
        const compliantCount = consignments - nonCompliant;
        const rate = (compliantCount / consignments * 100).toFixed(1);

        complianceRate.textContent = `${rate}%`;
        complianceStatus.style.display = 'block';

        // Color code the compliance rate
        if (rate >= 95) {
            complianceStatus.style.backgroundColor = '#d4edda';
            complianceStatus.style.color = '#155724';
        } else if (rate >= 80) {
            complianceStatus.style.backgroundColor = '#fff3cd';
            complianceStatus.style.color = '#856404';
        } else {
            complianceStatus.style.backgroundColor = '#f8d7da';
            complianceStatus.style.color = '#721c24';
        }
    } else {
        complianceStatus.style.display = 'none';
    }
}

// Load inspections for a specific voyage
function loadVoyageInspections(voyageId) {
    fetch(`api/get_recent_inspections.php?voyage_id=${voyageId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentInspections');
            const actionsDiv = document.getElementById('inspectionActions');
            if (!container) return;

            if (!data.success || !data.data || data.data.length === 0) {
                container.innerHTML = '<p>No inspections recorded for this voyage yet. Fill in the form above to add one.</p>';
                if (actionsDiv) actionsDiv.style.display = 'none';
                return;
            }

            let html = `<p style="margin-bottom: 10px;"><strong>${data.count} inspection(s) for this voyage:</strong></p>`;
            html += '<table style="width: 100%; border-collapse: collapse;">';
            html += '<thead><tr style="background: #3498db; color: white;">';
            html += '<th style="padding: 10px; text-align: left;">ID</th>';
            html += '<th style="padding: 10px; text-align: left;">Commodity</th>';
            html += '<th style="padding: 10px; text-align: left;">Group</th>';
            html += '<th style="padding: 10px; text-align: left;">Location</th>';
            html += '<th style="padding: 10px; text-align: left;">Consignments</th>';
            html += '<th style="padding: 10px; text-align: left;">Non-Compliant</th>';
            html += '<th style="padding: 10px; text-align: left;">Compliance</th>';
            html += '<th style="padding: 10px; text-align: left;">Date</th>';
            html += '</tr></thead><tbody>';

            data.data.forEach((inspection, index) => {
                const bgColor = index % 2 === 0 ? '#f8f9fa' : 'white';
                const groupLabel = formatGroupName(inspection.CommodityGroupID);
                const consignments = parseInt(inspection.NoOfConsignments) || 0;
                const nonCompliant = parseInt(inspection.NoOfNonCompliant) || 0;
                let complianceRate = 'N/A';
                let complianceColor = '';
                if (consignments > 0) {
                    const rate = ((consignments - nonCompliant) / consignments * 100).toFixed(1);
                    complianceRate = rate + '%';
                    if (rate >= 95) complianceColor = '#d4edda';
                    else if (rate >= 80) complianceColor = '#fff3cd';
                    else complianceColor = '#f8d7da';
                }

                html += `<tr style="background: ${bgColor};">`;
                html += `<td style="padding: 8px;">${inspection.PassengerInspectionID}</td>`;
                html += `<td style="padding: 8px;">${inspection.CommodityType || 'N/A'}</td>`;
                html += `<td style="padding: 8px;">${groupLabel || 'N/A'}</td>`;
                html += `<td style="padding: 8px;">${inspection.location_name || 'N/A'}</td>`;
                html += `<td style="padding: 8px;">${consignments}</td>`;
                html += `<td style="padding: 8px;">${nonCompliant}</td>`;
                html += `<td style="padding: 8px; background: ${complianceColor};">${complianceRate}</td>`;
                html += `<td style="padding: 8px;">${inspection.ModifiedDate || inspection.created_at}</td>`;
                html += '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;

            // Show the post-submission action buttons
            if (actionsDiv) actionsDiv.style.display = 'flex';
        })
        .catch(error => console.error('Error loading voyage inspections:', error));
}

// Load all recent inspections (fallback when no active voyage)
function loadRecentInspections() {
    fetch('api/get_recent_inspections.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentInspections');
            const actionsDiv = document.getElementById('inspectionActions');
            if (!container) return;

            if (!data.success || !data.data || data.data.length === 0) {
                container.innerHTML = '<p>No inspections recorded yet.</p>';
                if (actionsDiv) actionsDiv.style.display = 'none';
                return;
            }

            let html = '<table style="width: 100%; border-collapse: collapse;">';
            html += '<thead><tr style="background: #3498db; color: white;">';
            html += '<th style="padding: 10px; text-align: left;">ID</th>';
            html += '<th style="padding: 10px; text-align: left;">Voyage</th>';
            html += '<th style="padding: 10px; text-align: left;">Vessel</th>';
            html += '<th style="padding: 10px; text-align: left;">Commodity</th>';
            html += '<th style="padding: 10px; text-align: left;">Consignments</th>';
            html += '<th style="padding: 10px; text-align: left;">Non-Compliant</th>';
            html += '<th style="padding: 10px; text-align: left;">Date</th>';
            html += '</tr></thead><tbody>';

            data.data.forEach((inspection, index) => {
                const bgColor = index % 2 === 0 ? '#f8f9fa' : 'white';
                html += `<tr style="background: ${bgColor};">`;
                html += `<td style="padding: 8px;">${inspection.PassengerInspectionID}</td>`;
                html += `<td style="padding: 8px;">${inspection.VoyageNo || 'N/A'}</td>`;
                html += `<td style="padding: 8px;">${inspection.VesselID || 'N/A'}</td>`;
                html += `<td style="padding: 8px;">${inspection.CommodityType || 'N/A'}</td>`;
                html += `<td style="padding: 8px;">${inspection.NoOfConsignments || '0'}</td>`;
                html += `<td style="padding: 8px;">${inspection.NoOfNonCompliant || '0'}</td>`;
                html += `<td style="padding: 8px;">${inspection.ModifiedDate || inspection.created_at}</td>`;
                html += '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;

            if (actionsDiv) actionsDiv.style.display = 'none';
        })
        .catch(error => console.error('Error loading recent inspections:', error));
}

// Handle inspection form submission
const inspectionForm = document.getElementById('inspectionForm');
if (inspectionForm) {
    inspectionForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const messageDiv = document.getElementById('inspectionMessage');

        fetch('api/submit_inspection.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.className = data.warning ? 'message warning' : 'message success';
                messageDiv.textContent = data.warning || (data.message + ' (Inspection ID: ' + data.inspection_id + ')');
                messageDiv.style.display = 'block';

                // Refresh progress indicators
                const vid = document.getElementById('InspectionVoyageID');
                if (vid && typeof loadVoyageStatus === 'function') {
                    loadVoyageStatus(vid.value);
                }

                // Reset form
                inspectionForm.reset();

                // Reset modified date to today
                const modifiedDateField = document.getElementById('InspectionModifiedDate');
                if (modifiedDateField) {
                    const today = new Date().toISOString().split('T')[0];
                    modifiedDateField.value = today;
                }

                // Hide commodity info
                document.getElementById('commodityInfo').style.display = 'none';
                document.getElementById('complianceStatus').style.display = 'none';

                // Reload the voyage-specific inspection list
                const activeVoyageID = localStorage.getItem('activeVoyageID') || (vid ? vid.value : null);
                if (activeVoyageID) {
                    loadVoyageInspections(activeVoyageID);
                }

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

// Add another inspection - reset form and scroll to top
function addAnotherInspection() {
    inspectionForm.reset();
    document.getElementById('commodityInfo').style.display = 'none';
    document.getElementById('complianceStatus').style.display = 'none';
    document.getElementById('inspectionMessage').style.display = 'none';
    document.getElementById('inspectionActions').style.display = 'none';

    // Reset modified date to today
    const modifiedDateField = document.getElementById('InspectionModifiedDate');
    if (modifiedDateField) {
        modifiedDateField.value = new Date().toISOString().split('T')[0];
    }

    // Scroll to the form
    inspectionForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Continue to Step 3 (Passenger Seizure)
function continueToSeizure() {
    const nextBtn = document.querySelector('.tab-link[onclick*="seizureTab"]');
    if (nextBtn) nextBtn.click();
}