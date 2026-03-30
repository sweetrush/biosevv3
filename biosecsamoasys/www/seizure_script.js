// Track voyage loading state
let voyagesLoadedSeizure = false;
let voyagesLoadingSeizure = null;

// Load data when the seizure tab is accessed
document.addEventListener('DOMContentLoaded', function() {
    // Start loading voyages and store the promise
    voyagesLoadingSeizure = loadVoyagesForSeizure();

    loadPortsForSeizure();
    loadCommoditiesForSeizure();
    loadRecentSeizures();
    loadAutoSeizureNumber();

    // Check active voyage after voyages are loaded
    voyagesLoadingSeizure.then(() => {
        checkActiveVoyageSeizure();
    }).catch(error => {
        console.error('Error in voyage loading promise chain:', error);
    });

    // Add event listener for clear voyage button
    const clearBtn = document.getElementById('clearVoyageBtnSeizure');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearActiveVoyageSeizure);
    }

    // Set today's date for seizure date
    const seizureDateField = document.getElementById('ps_SeizureDate');
    if (seizureDateField) {
        const today = new Date().toISOString().split('T')[0];
        seizureDateField.value = today;
    }
});

// Load auto-generated seizure number
async function loadAutoSeizureNumber() {
    try {
        const response = await fetch('api/generate_seizure_number.php?type=passenger');
        const data = await response.json();
        if (data.success && data.seizure_no) {
            document.getElementById('ps_SeizureNo').value = data.seizure_no;
        }
    } catch (error) {
        console.error('Error loading seizure number:', error);
    }
}

// Check for active voyage in localStorage
function checkActiveVoyageSeizure() {
    const activeVoyageID = localStorage.getItem('activeVoyageID');
    const activeVoyageNo = localStorage.getItem('activeVoyageNo');
    const activeVesselID = localStorage.getItem('activeVesselID');

    if (activeVoyageID) {
        // Show active voyage context
        document.getElementById('activeVoyageContextSeizure').style.display = 'block';
        document.getElementById('contextVoyageIDSeizure').textContent = activeVoyageID;
        document.getElementById('contextVoyageNoSeizure').textContent = activeVoyageNo || 'N/A';
        document.getElementById('contextVesselIDSeizure').textContent = activeVesselID || 'N/A';

        // Hide the voyage selection dropdown section
        document.getElementById('ps_voyageSelectionSection').style.display = 'none';

        // Set the voyage ID in the select dropdown (wait for voyages to load if needed)
        const voyageSelect = document.getElementById('ps_SeizureVoyageID');
        if (voyageSelect) {
            if (voyagesLoadedSeizure) {
                voyageSelect.value = activeVoyageID;
            } else if (voyagesLoadingSeizure) {
                voyagesLoadingSeizure.then(() => {
                    voyageSelect.value = activeVoyageID;
                });
            }
        }
    } else {
        // Show voyage selection section
        document.getElementById('activeVoyageContextSeizure').style.display = 'none';
        document.getElementById('ps_voyageSelectionSection').style.display = 'block';
    }
}

// Clear active voyage
function clearActiveVoyageSeizure() {
    localStorage.removeItem('activeVoyageID');
    localStorage.removeItem('activeVoyageNo');
    localStorage.removeItem('activeVesselID');
    checkActiveVoyageSeizure();
}

// Load voyages for dropdown
async function loadVoyagesForSeizure() {
    try {
        const response = await fetch('api/get_voyages.php');
        const data = await response.json();

        if (data.success && data.data) {
            const voyageSelect = document.getElementById('ps_SeizureVoyageID');
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
                voyagesLoadedSeizure = true;
            }
        }
    } catch (error) {
        console.error('Error loading voyages:', error);
    }
}

// Load ports for dropdown
function loadPortsForSeizure() {
    fetch('api/get_ports.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const portSelect = document.getElementById('ps_PortOfEntry');
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

// Load commodities for seizure dropdown
function loadCommoditiesForSeizure() {
    fetch('api/get_commodities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const commoditySelect = document.getElementById('ps_CommodityType');
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
                            optgroup.label = formatGroupNameSeizure(commodity.CommodityGroupID);
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
function formatGroupNameSeizure(groupId) {
    const groupNames = {
        '1': '🐄 Animals and Animal Products',
        '2': '🌱 Plants and Plant Products',
        '3': '🚗 MEV (Machinery, Equipment, Vehicles)',
        '4': '📦 Other',
        '5': '⚠️ Pesticides'
    };
    return groupNames[groupId] || groupId;
}

// Load recent seizures
function loadRecentSeizures() {
    fetch('api/get_recent_seizures.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const container = document.getElementById('ps_recentSeizures');
                if (container) {
                    if (data.data.length === 0) {
                        container.innerHTML = '<p>No seizures recorded yet.</p>';
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
                        html += `<td style="padding: 8px;">${seizure.PassengerSeizureID}</td>`;
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
        .catch(error => console.error('Error loading recent seizures:', error));
}

// Handle seizure form submission
const seizureForm = document.getElementById('ps_seizureForm');
if (seizureForm) {
    seizureForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const messageDiv = document.getElementById('seizureMessage');

        fetch('api/submit_seizure.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.className = data.warning ? 'message warning' : 'message success';
                messageDiv.textContent = data.warning || (data.message + ' (Seizure ID: ' + data.seizure_id + ')');
                messageDiv.style.display = 'block';

                // Refresh progress indicators
                const vid = document.getElementById('ps_SeizureVoyageID');
                if (vid && typeof loadVoyageStatus === 'function') loadVoyageStatus(vid.value);

                // Reset form
                seizureForm.reset();

                // Reset seizure date to today
                const seizureDateField = document.getElementById('ps_SeizureDate');
                if (seizureDateField) {
                    const today = new Date().toISOString().split('T')[0];
                    seizureDateField.value = today;
                }

                // Reload new seizure number for next record
                loadAutoSeizureNumber();

                // Reload recent seizures
                loadRecentSeizures();

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
