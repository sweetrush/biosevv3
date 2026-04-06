<?php
define('CSS_CACHE_BUST', 2);
require_once 'api/auth_check.php';
$pageTitle = 'Seizure Management - Samoa Biosecurity System';
$currentPage = 'unified_seizure';
?>
<style>
    .seizure-header {
        background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
        color: white;
        padding: 40px;
    }

    .seizure-header h1 { font-size: 2.5em; margin-bottom: 10px; }
    .seizure-header p { font-size: 1.1em; opacity: 0.9; }

    .form-section {
        margin: 30px;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .edit-modal {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        z-index: 10000;
        animation: fadeIn 0.3s ease;
    }

    .modal-backdrop {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }

    .modal-dialog {
        position: relative;
        max-width: 900px;
        max-height: 95vh;
        margin: 2vh auto;
        overflow: hidden;
        animation: slideUp 0.4s ease;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-content {
        background: white;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        padding: 24px 32px;
        color: white;
        display: flex; align-items: center; justify-content: space-between;
    }

    .passenger-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .cargo-header { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .modal-header h3 { margin: 0; font-size: 1.5rem; font-weight: 600; }

    .close-btn {
        background: rgba(255, 255, 255, 0.2); border: none; color: white;
        font-size: 24px; cursor: pointer;
        width: 40px; height: 40px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s ease;
    }

    .close-btn:hover { background: rgba(255, 255, 255, 0.3); transform: rotate(90deg); }

    .modal-body { padding: 0; overflow-y: auto; flex: 1; max-height: calc(95vh - 160px); }
    .seizure-form { padding: 32px; }
    .form-section-inner { margin-bottom: 32px; }
    .form-section-inner h4 {
        margin: 0 0 16px 0; color: #374151; font-size: 1.1rem; font-weight: 600;
        padding-bottom: 8px; border-bottom: 2px solid #e5e7eb;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-field { display: flex; flex-direction: column; }
    .form-field.full-width { grid-column: 1 / -1; }
    .form-field label { margin-bottom: 6px; font-weight: 500; color: #374151; font-size: 0.95rem; }

    .form-field input, .form-field select, .form-field textarea {
        padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px;
        font-size: 0.95rem; transition: all 0.3s ease; background: white; box-sizing: border-box;
    }

    .form-field input:focus, .form-field select:focus, .form-field textarea:focus {
        outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-field textarea { resize: vertical; min-height: 80px; }

    .required { color: #ef4444; font-weight: 600; }

    .modal-footer {
        padding: 24px 32px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex; justify-content: flex-end; gap: 12px;
    }

    .btn {
        padding: 12px 24px; border: none; border-radius: 8px;
        font-size: 0.95rem; font-weight: 500; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.3s ease; text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4); }
    .btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white; box-shadow: 0 4px 15px rgba(107, 114, 128, 0.2);
    }
    .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(107, 114, 128, 0.3); }

    .edit-btn {
        padding: 6px 12px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white; border: none; border-radius: 6px; cursor: pointer;
        font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
    }
    .edit-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4); }

    .go-to-btn {
        display: inline-block; padding: 15px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white; text-decoration: none; border-radius: 8px; font-weight: 600;
    }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(50px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .modal-dialog { margin: 0; max-height: 100vh; border-radius: 0; }
        .modal-header { padding: 20px 24px; }
        .seizure-form { padding: 24px; }
        .form-grid { grid-template-columns: 1fr; }
        .modal-footer { padding: 20px 24px; flex-direction: column; }
        .btn { width: 100%; justify-content: center; }
        .form-section { margin: 15px; padding: 20px; }
    }
</style>
<?php include 'includes/layout-start.php'; ?>
                <div class="seizure-header">
                    <h1>&#x1F4E6; Unified Seizure Management</h1>
                    <p>Combined view of passenger and cargo seizures</p>
                </div>

                <div class="form-section">
                    <h2>&#x1F4CB; Recent Seizures (All Types)</h2>
                    <div id="seizureListContainer" style="margin-top: 20px;">
                        <p>Loading all seizures...</p>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <a href="voyagement.php" class="go-to-btn">
                        &#x1F6A2; Go to Voyagement to Add/Edit Seizures
                    </a>
                </div>

<!-- Passenger Seizure Edit Modal -->
<div id="passengerSeizureModal" class="edit-modal">
    <div class="modal-backdrop" onclick="closePassengerModal()"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header passenger-header">
                <h3>&#x1F464; Edit Passenger Seizure</h3>
                <button class="close-btn" onclick="closePassengerModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="passengerSeizureForm" class="seizure-form">
                    <input type="hidden" id="passengerSeizureId" name="PassengerSeizureID">
                    <input type="hidden" id="passengerVoyageId" name="VoyageID">
                    <div class="seizure-form">
                        <div class="form-section-inner">
                            <h4>&#x1F4CB; Basic Information</h4>
                            <div class="form-grid">
                                <div class="form-field">
                                    <label for="passengerSeizureDate">Seizure Date <span class="required">*</span></label>
                                    <input type="date" id="passengerSeizureDate" name="SeizureDate" required>
                                </div>
                                <div class="form-field">
                                    <label for="passengerSeizureNo">Seizure Number <span class="required">*</span></label>
                                    <input type="text" id="passengerSeizureNo" name="SeizureNo" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-section-inner">
                            <h4>&#x1F464; Passenger Details</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="passengerImporter">Importer/Passenger <span class="required">*</span></label>
                                    <input type="text" id="passengerImporter" name="Importer" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-section-inner">
                            <h4>&#x1F4E6; Item Details</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="passengerCommodityType">Commodity Type <span class="required">*</span></label>
                                    <select id="passengerCommodityType" name="CommodityType" required>
                                        <option value="">Select commodity...</option>
                                    </select>
                                </div>
                                <div class="form-field full-width">
                                    <label for="passengerDescription">Description</label>
                                    <input type="text" id="passengerDescription" name="Description">
                                </div>
                                <div class="form-field">
                                    <label for="passengerQuantity">Quantity</label>
                                    <input type="text" id="passengerQuantity" name="Quantity">
                                </div>
                                <div class="form-field">
                                    <label for="passengerUnit">Unit</label>
                                    <input type="text" id="passengerUnit" name="Unit">
                                </div>
                            </div>
                        </div>
                        <div class="form-section-inner">
                            <h4>&#x1F50D; Detection & Action</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="passengerDetectionMethod">Detection Method</label>
                                    <input type="text" id="passengerDetectionMethod" name="DetectionMethod">
                                </div>
                                <div class="form-field full-width">
                                    <label for="passengerOfficerName">Officer Name</label>
                                    <input type="text" id="passengerOfficerName" name="OfficerName">
                                </div>
                                <div class="form-field full-width">
                                    <label for="passengerActionTaken">Action Taken</label>
                                    <input type="text" id="passengerActionTaken" name="ActionTaken">
                                </div>
                            </div>
                        </div>
                        <div class="form-section-inner">
                            <h4>&#x1F4DD; Comments</h4>
                            <div class="form-field full-width">
                                <label for="passengerComments">Additional Comments</label>
                                <textarea id="passengerComments" name="Comments" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePassengerModal()">
                    <span>&#x274C;</span> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="savePassengerSeizure()">
                    <span>&#x1F4BE;</span> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cargo Seizure Edit Modal -->
<div id="cargoSeizureModal" class="edit-modal">
    <div class="modal-backdrop" onclick="closeCargoModal()"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header cargo-header">
                <h3>&#x1F4E6; Edit Cargo Seizure</h3>
                <button class="close-btn" onclick="closeCargoModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="cargoSeizureForm" class="seizure-form">
                    <input type="hidden" id="cargoSeizureId" name="CargoSeizureID">
                    <input type="hidden" id="cargoVoyageId" name="VoyageID">
                    <div class="seizure-form">
                        <div class="form-section-inner">
                            <h4>&#x1F4CB; Basic Information</h4>
                            <div class="form-grid">
                                <div class="form-field">
                                    <label for="cargoSeizureDate">Seizure Date <span class="required">*</span></label>
                                    <input type="date" id="cargoSeizureDate" name="SeizureDate" required>
                                </div>
                                <div class="form-field">
                                    <label for="cargoSeizureNo">Seizure Number <span class="required">*</span></label>
                                    <input type="text" id="cargoSeizureNo" name="SeizureNo" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-section-inner">
                            <h4>&#x1F6A2; Container & Cargo Details</h4>
                            <div class="form-grid">
                                <div class="form-field">
                                    <label for="cargoContainerRef">Container/Cargo Ref No</label>
                                    <input type="text" id="cargoContainerRef" name="ContainerCargoRefNo">
                                </div>
                                <div class="form-field">
                                    <label for="cargoImporter">Importer <span class="required">*</span></label>
                                    <input type="text" id="cargoImporter" name="Importer" required>
                                </div>
                                <div class="form-field full-width">
                                    <label for="cargoDescription">Cargo Description</label>
                                    <input type="text" id="cargoDescription" name="CargoDescription">
                                </div>
                                <div class="form-field">
                                    <label for="cargoDepotName">Depot Name</label>
                                    <input type="text" id="cargoDepotName" name="DepotName">
                                </div>
                            </div>
                        </div>
                        <div class="form-section-inner">
                            <h4>&#x1F4E6; Item Details</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="cargoCommodityType">Commodity Type <span class="required">*</span></label>
                                    <select id="cargoCommodityType" name="CommodityType" required>
                                        <option value="">Select commodity...</option>
                                    </select>
                                </div>
                                <div class="form-field full-width">
                                    <label for="cargoDetectionMethod">Detection Method</label>
                                    <input type="text" id="cargoDetectionMethod" name="DetectionMethod">
                                </div>
                                <div class="form-field">
                                    <label for="cargoQuantity">Quantity</label>
                                    <input type="text" id="cargoQuantity" name="Quantity">
                                </div>
                                <div class="form-field">
                                    <label for="cargoUnit">Unit</label>
                                    <input type="text" id="cargoUnit" name="Unit">
                                </div>
                                <div class="form-field">
                                    <label for="cargoVolume">Volume (kg)</label>
                                    <input type="text" id="cargoVolume" name="VolumeKg">
                                </div>
                            </div>
                        </div>
                        <div class="form-section-inner">
                            <h4>&#x1F46E; Officer & Action Details</h4>
                            <div class="form-grid">
                                <div class="form-field full-width">
                                    <label for="cargoSeizingOfficerName">Seizing Officer Name</label>
                                    <input type="text" id="cargoSeizingOfficerName" name="SeizingOfficerName">
                                </div>
                                <div class="form-field full-width">
                                    <label for="cargoActionTaken">Action Taken</label>
                                    <input type="text" id="cargoActionTaken" name="ActionTaken">
                                </div>
                                <div class="form-field full-width">
                                    <label for="cargoActionOfficer">Action Officer</label>
                                    <input type="text" id="cargoActionOfficer" name="ActionOfficer">
                                </div>
                            </div>
                        </div>
                        <div class="form-section-inner">
                            <h4>&#x1F4DD; Comments</h4>
                            <div class="form-field full-width">
                                <label for="cargoComments">Additional Comments</label>
                                <textarea id="cargoComments" name="Comments" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCargoModal()">
                    <span>&#x274C;</span> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveCargoSeizure()">
                    <span>&#x1F4BE;</span> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAllSeizures();
});

async function loadAllSeizures() {
    const container = document.getElementById('seizureListContainer');
    container.innerHTML = '<p>Loading all seizures...</p>';
    try {
        const [passengerResponse, cargoResponse] = await Promise.all([
            fetch('api/get_recent_seizures.php'),
            fetch('api/get_recent_cargo_seizures.php')
        ]);
        const passengerResult = await passengerResponse.json();
        const cargoResult = await cargoResponse.json();

        let allSeizures = [];
        if (passengerResult.success && passengerResult.data) {
            allSeizures = allSeizures.concat(passengerResult.data.map(seizure => ({
                ...seizure,
                type: 'passenger',
                date: seizure.SeizureDate || seizure.created_at,
                number: seizure.SeizureNo || `PS-${seizure.PassengerSeizureID}`,
                mainId: seizure.PassengerSeizureID
            })));
        }
        if (cargoResult.success && cargoResult.data) {
            allSeizures = allSeizures.concat(cargoResult.data.map(seizure => ({
                ...seizure,
                type: 'cargo',
                date: seizure.SeizureDate || seizure.created_at,
                number: seizure.SeizureNo || `CS-${seizure.CargoSeizureID}`,
                mainId: seizure.CargoSeizureID
            })));
        }

        allSeizures.sort((a, b) => {
            const dateA = new Date(a.date || '1970-01-01');
            const dateB = new Date(b.date || '1970-01-01');
            return dateB - dateA;
        });

        displaySeizures(allSeizures);
    } catch (error) {
        console.error('Error loading seizures:', error);
        container.innerHTML = '<p style="color: #e74c3c;">Error loading seizures. Please try again.</p>';
    }
}

function displaySeizures(seizures) {
    const container = document.getElementById('seizureListContainer');
    if (seizures.length === 0) {
        container.innerHTML = '<p>No seizures found.</p>';
        return;
    }

    container.innerHTML = `
        <div style="overflow-x: auto; max-height: 600px; overflow-y: auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9em; background: white;">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Type</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Seizure No</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Voyage</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Date</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Importer</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Commodity/Item</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Quantity</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Unit</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Officer</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Action</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${seizures.map(seizure => `
                        <tr style="border-left: 4px solid ${seizure.type === 'passenger' ? '#f5576c' : '#fdbb2d'}; ${seizure.type === 'passenger' ? 'background: #fff5f7;' : 'background: #fffef5;'}">
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">
                                <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: 600; color: white; background: ${seizure.type === 'passenger' ? 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' : 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'};">
                                    ${seizure.type === 'passenger' ? '&#x1F464;' : '&#x1F4E6;'} ${seizure.type}
                                </span>
                            </td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;"><strong>${seizure.number || 'N/A'}</strong></td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">
                                ${seizure.VoyageNo || 'Voyage #' + seizure.VoyageID || 'N/A'}
                            </td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${formatDate(seizure.date)}</td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.Importer || seizure.PassengerID || 'N/A'}</td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.CommodityType || seizure.ItemDescription || seizure.CargoDescription || 'N/A'}</td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.Quantity || '-'}</td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.Unit || '-'}</td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.SeizingOfficerName || seizure.ActionOfficer || 'N/A'}</td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">${seizure.ActionTaken || 'N/A'}</td>
                            <td style="padding: 10px 12px; border-bottom: 1px solid #e0e0e0;">
                                <button onclick="editSeizure('${seizure.type}', ${seizure.mainId})" class="edit-btn" title="Edit ${seizure.type} seizure">
                                    &#x270F;&#xFE0F; Edit
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function formatDate(dateString) {
    if (!dateString || dateString === 'N/A') return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    } catch (e) { return dateString; }
}

async function editSeizure(type, id) {
    try {
        const endpoint = type === 'passenger' ? `api/get_passenger_seizure.php?id=${id}` :
                          type === 'cargo' ? `api/get_cargo_seizure.php?id=${id}` : null;
        if (!endpoint) return;

        const response = await fetch(endpoint);
        const result = await response.json();
        if (result.success && result.data) {
            if (type === 'passenger') openPassengerModal(result.data);
            else if (type === 'cargo') openCargoModal(result.data);
        } else { alert('Failed to load seizure details: ' + result.message); }
    } catch (error) {
        console.error('Error loading seizure details:', error);
        alert('Error loading seizure details. Please try again.');
    }
}

async function loadCommodities() {
    try {
        const response = await fetch('api/get_commodities.php');
        const result = await response.json();
        if (result.success && result.data) {
            const passengerSelect = document.getElementById('passengerCommodityType');
            const cargoSelect = document.getElementById('cargoCommodityType');
            [passengerSelect, cargoSelect].forEach(select => {
                if (!select) return;
                select.innerHTML = '<option value="">Select commodity...</option>';
                result.data.forEach(commodity => {
                    const option = document.createElement('option');
                    option.value = commodity.CommodityType;
                    option.textContent = commodity.CommodityType;
                    select.appendChild(option);
                });
            });
        }
    } catch (error) { console.error('Error loading commodities:', error); }
}

function openPassengerModal(seizure) {
    loadCommodities().then(() => {
        document.getElementById('passengerSeizureId').value = seizure.PassengerSeizureID;
        document.getElementById('passengerVoyageId').value = seizure.VoyageID;
        document.getElementById('passengerSeizureDate').value = seizure.SeizureDate || '';
        document.getElementById('passengerSeizureNo').value = seizure.SeizureNo || '';
        document.getElementById('passengerImporter').value = seizure.Importer || '';
        document.getElementById('passengerCommodityType').value = seizure.CommodityType || '';
        document.getElementById('passengerDescription').value = seizure.Description || '';
        document.getElementById('passengerDetectionMethod').value = seizure.DetectionMethod || '';
        document.getElementById('passengerQuantity').value = seizure.Quantity || '';
        document.getElementById('passengerUnit').value = seizure.Unit || '';
        document.getElementById('passengerOfficerName').value = seizure.OfficerName || '';
        document.getElementById('passengerActionTaken').value = seizure.ActionTaken || '';
        document.getElementById('passengerComments').value = seizure.Comments || '';
        document.getElementById('passengerSeizureModal').style.display = 'block';
    });
}

function openCargoModal(seizure) {
    loadCommodities().then(() => {
        document.getElementById('cargoSeizureId').value = seizure.CargoSeizureID;
        document.getElementById('cargoVoyageId').value = seizure.VoyageID;
        document.getElementById('cargoSeizureDate').value = seizure.SeizureDate || '';
        document.getElementById('cargoSeizureNo').value = seizure.SeizureNo || '';
        document.getElementById('cargoContainerRef').value = seizure.ContainerCargoRefNo || '';
        document.getElementById('cargoImporter').value = seizure.Importer || '';
        document.getElementById('cargoDescription').value = seizure.CargoDescription || '';
        document.getElementById('cargoDepotName').value = seizure.DepotName || '';
        document.getElementById('cargoCommodityType').value = seizure.CommodityType || '';
        document.getElementById('cargoDetectionMethod').value = seizure.DetectionMethod || '';
        document.getElementById('cargoQuantity').value = seizure.Quantity || '';
        document.getElementById('cargoUnit').value = seizure.Unit || '';
        document.getElementById('cargoVolume').value = seizure.VolumeKg || '';
        document.getElementById('cargoSeizingOfficerName').value = seizure.SeizingOfficerName || '';
        document.getElementById('cargoActionTaken').value = seizure.ActionTaken || '';
        document.getElementById('cargoActionOfficer').value = seizure.ActionOfficer || '';
        document.getElementById('cargoComments').value = seizure.Comments || '';
        document.getElementById('cargoSeizureModal').style.display = 'block';
    });
}

function closePassengerModal() { document.getElementById('passengerSeizureModal').style.display = 'none'; }
function closeCargoModal() { document.getElementById('cargoSeizureModal').style.display = 'none'; }

async function savePassengerSeizure() {
    const formData = new FormData(document.getElementById('passengerSeizureForm'));
    const seizureId = formData.get('PassengerSeizureID');
    const data = Object.fromEntries(formData);
    try {
        const response = await fetch(`api/update_passenger_seizure.php?id=${seizureId}`, {
            method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) { alert('Passenger seizure updated successfully!'); closePassengerModal(); loadAllSeizures(); }
        else { alert('Failed to update passenger seizure: ' + result.message); }
    } catch (error) { alert('Error updating passenger seizure. Please try again.'); }
}

async function saveCargoSeizure() {
    const formData = new FormData(document.getElementById('cargoSeizureForm'));
    const seizureId = formData.get('CargoSeizureID');
    const data = Object.fromEntries(formData);
    try {
        const response = await fetch(`api/update_cargo_seizure.php?id=${seizureId}`, {
            method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) { alert('Cargo seizure updated successfully!'); closeCargoModal(); loadAllSeizures(); }
        else { alert('Failed to update cargo seizure: ' + result.message); }
    } catch (error) { alert('Error updating cargo seizure. Please try again.'); }
}

window.onclick = function(event) {
    const passengerModal = document.getElementById('passengerSeizureModal');
    const cargoModal = document.getElementById('cargoSeizureModal');
    if (event.target === passengerModal) closePassengerModal();
    if (event.target === cargoModal) closeCargoModal();
}
</script>
<script src="script.js?v=<?php echo CSS_CACHE_BUST; ?>"></script>
<?php include 'includes/layout-end.php'; ?>
