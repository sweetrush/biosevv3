// Track voyage loading state
let voyagesLoadedCargoSeizure = false;
let voyagesLoadingCargoSeizure = null;

// Load data when the cargo seizure tab is accessed
document.addEventListener('DOMContentLoaded', function() {
    // Start loading voyages and store the promise
    voyagesLoadingCargoSeizure = loadVoyagesForCargoSeizure();

    loadPortsForCargoSeizure();
    loadCommoditiesForCargoSeizure();
    loadRecentCargoSeizures();

    // Check active voyage after voyages are loaded
    voyagesLoadingCargoSeizure.then(() => {
        checkActiveVoyageCargoSeizure();
    });

    // Add event listener for clear voyage button
    const clearBtn = document.getElementById('clearVoyageBtnCargoSeizure');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearActiveVoyageCargoSeizure);
    }

    // Set today's date for seizure date
    const seizureDateField = document.getElementById('SeizureDate');
    if (seizureDateField) {
        const today = new Date().toISOString().split('T')[0];
        seizureDateField.value = today;
    }
});

// Check for active voyage in localStorage
function checkActiveVoyageCargoSeizure() {
    const activeVoyageID = localStorage.getItem('activeVoyageID');
    const activeVoyageNo = localStorage.getItem('activeVoyageNo');
    const activeVesselID = localStorage.getItem('activeVesselID');

    if (activeVoyageID) {
        // Show active voyage context
        document.getElementById('activeVoyageContextCargoSeizure').style.display = 'block';
        document.getElementById('contextVoyageIDCargoSeizure').textContent = activeVoyageID;
        document.getElementById('contextVoyageNoCargoSeizure').textContent = activeVoyageNo || 'N/A';
        document.getElementById('contextVesselIDCargoSeizure').textContent = activeVesselID || 'N/A';

        // Hide the voyage selection dropdown section
        document.getElementById('cs_voyageSelectionSection').style.display = 'none';

        // Set the voyage ID in the select and hidden field (wait for voyages to load if needed)
        const voyageSelect = document.getElementById('cs_CargoSeizureVoyageID');
        const voyageHidden = document.getElementById('cs_CargoSeizureVoyageIDHidden');
        if (voyagesLoadedCargoSeizure) {
            if (voyageSelect) voyageSelect.value = activeVoyageID;
            if (voyageHidden) voyageHidden.value = activeVoyageID;
        } else if (voyagesLoadingCargoSeizure) {
            voyagesLoadingCargoSeizure.then(() => {
                if (voyageSelect) voyageSelect.value = activeVoyageID;
                if (voyageHidden) voyageHidden.value = activeVoyageID;
            });
        }
    } else {
        // Show voyage selection section
        document.getElementById('activeVoyageContextCargoSeizure').style.display = 'none';
        document.getElementById('cs_voyageSelectionSection').style.display = 'block';
    }
}

// Clear active voyage
function clearActiveVoyageCargoSeizure() {
    localStorage.removeItem('activeVoyageID');
    localStorage.removeItem('activeVoyageNo');
    localStorage.removeItem('activeVesselID');
    checkActiveVoyageCargoSeizure();
}

// Load voyages for dropdown
async function loadVoyagesForCargoSeizure() {
    try {
        const response = await fetch('api/get_voyages.php');
        const data = await response.json();

        if (data.success && data.data) {
            const voyageSelect = document.getElementById('cs_CargoSeizureVoyageID');
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
                    const voyageHidden = document.getElementById('cs_CargoSeizureVoyageIDHidden');
                    if (voyageHidden) {
                        voyageHidden.value = this.value;
                    }
                });
                voyagesLoadedCargoSeizure = true;
            }
        }
    } catch (error) {
        console.error('Error loading voyages:', error);
    }
}

// Load ports for dropdown
function loadPortsForCargoSeizure() {
    fetch('api/get_ports.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const portSelect = document.getElementById('PortOfEntry');
                if (portSelect) {
                    portSelect.innerHTML = '<option value="">Select...</option>';

                    data.data.forEach(port => {
                        const option = document.createElement('option');
                        option.value = port.port_id;
                        option.textContent = `${port.port_name} - ${port.country}`;
                        portSelect.appendChild(option);
                    });
                }
            }
        })
        .catch(error => console.error('Error loading ports:', error));
}

// Load commodities for cargo seizure dropdown
function loadCommoditiesForCargoSeizure() {
    fetch('api/get_commodities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const commoditySelect = document.getElementById('cs_CommodityType');
                if (commoditySelect) {
                    commoditySelect.innerHTML = '<option value="">Select Commodity...</option>';

                    // Group commodities by CommodityGroupID
                    let currentGroup = '';
                    let optgroup = null;

                    data.data.forEach(commodity => {
                        // Create new optgroup if group changes
                        if (commodity.CommodityGroupID !== currentGroup) {
                            if (optgroup) {
                                commoditySelect.appendChild(optgroup);
                            }
                            optgroup = document.createElement('optgroup');
                            optgroup.label = formatGroupNameCargoSeizure(commodity.CommodityGroupID);
                            currentGroup = commodity.CommodityGroupID;
                        }

                        const option = document.createElement('option');
                        option.value = commodity.CommodityType;
                        option.textContent = commodity.CommodityType;
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
function formatGroupNameCargoSeizure(groupId) {
    const groupNames = {
        '1': '🐄 Animals and Animal Products',
        '2': '🌱 Plants and Plant Products',
        '3': '🚗 MEV (Machinery, Equipment, Vehicles)',
        '4': '📦 Other',
        '5': '⚠️ Pesticides'
    };
    return groupNames[groupId] || groupId;
}

// Load recent cargo seizures
function loadRecentCargoSeizures() {
    fetch('api/get_recent_cargo_seizures.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const container = document.getElementById('recentCargoSeizures');
                if (container) {
                    if (data.data.length === 0) {
                        container.innerHTML = '<p>No cargo seizures recorded yet.</p>';
                        return;
                    }

                    let html = '<table style="width: 100%; border-collapse: collapse;">';
                    html += '<thead><tr style="background: #e74c3c; color: white;">';
                    html += '<th style="padding: 10px; text-align: left;">ID</th>';
                    html += '<th style="padding: 10px; text-align: left;">Voyage</th>';
                    html += '<th style="padding: 10px; text-align: left;">Date</th>';
                    html += '<th style="padding: 10px; text-align: left;">Seizure No</th>';
                    html += '<th style="padding: 10px; text-align: left;">Importer</th>';
                    html += '<th style="padding: 10px; text-align: left;">Commodity</th>';
                    html += '<th style="padding: 10px; text-align: left;">Quantity</th>';
                    html += '<th style="padding: 10px; text-align: left;">Action</th>';
                    html += '</tr></thead><tbody>';

                    data.data.forEach((seizure, index) => {
                        const bgColor = index % 2 === 0 ? '#f8f9fa' : 'white';

                        html += `<tr style="background: ${bgColor};">`;
                        html += `<td style="padding: 8px;">${seizure.CargoSeizureID}</td>`;
                        html += `<td style="padding: 8px;">${seizure.VoyageNo || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${seizure.SeizureDate || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${seizure.SeizureNo || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${seizure.Importer || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${seizure.CommodityType || 'N/A'}</td>`;
                        html += `<td style="padding: 8px;">${seizure.Quantity || '0'} ${seizure.Unit || ''}</td>`;
                        html += `<td style="padding: 8px;">${seizure.ActionTaken || 'N/A'}</td>`;
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    container.innerHTML = html;
                }
            }
        })
        .catch(error => console.error('Error loading recent cargo seizures:', error));
}

// Handle cargo seizure form submission
const cargoSeizureForm = document.getElementById('cs_cargoSeizureForm');
if (cargoSeizureForm) {
    cargoSeizureForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const messageDiv = document.getElementById('cs_cargoSeizureMessage');

        // Get voyage ID from either the active voyage or the dropdown
        let voyageID = document.getElementById('cs_CargoSeizureVoyageIDHidden').value;
        if (!voyageID) {
            voyageID = document.getElementById('cs_CargoSeizureVoyageID').value;
        }

        if (!voyageID) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Error: Please select a voyage';
            messageDiv.style.display = 'block';
            return;
        }

        const formData = new FormData(this);
        // Ensure VoyageID is set
        formData.set('VoyageID', voyageID);

        fetch('api/submit_cargo_seizure.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.className = 'message success';
                messageDiv.textContent = data.message + ' (Cargo Seizure ID: ' + data.seizure_id + ')';
                messageDiv.style.display = 'block';

                // Reset form
                cargoSeizureForm.reset();

                // Reset seizure date to today
                const seizureDateField = document.getElementById('SeizureDate');
                if (seizureDateField) {
                    const today = new Date().toISOString().split('T')[0];
                    seizureDateField.value = today;
                }

                // Reload recent cargo seizures
                loadRecentCargoSeizures();

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
