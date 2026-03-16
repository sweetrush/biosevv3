<div id="seizureMessage" class="message"></div>

<!-- Active Voyage Context -->
<div id="activeVoyageContextSeizure" style="display: none; padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; margin-bottom: 20px; border-radius: 5px;">
    <h3 style="margin: 0 0 10px 0; color: #2e7d32;">✓ Active Voyage Selected</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
        <div><strong>Voyage ID:</strong> <span id="contextVoyageIDSeizure"></span></div>
        <div><strong>Voyage No:</strong> <span id="contextVoyageNoSeizure"></span></div>
        <div><strong>Vessel ID:</strong> <span id="contextVesselIDSeizure"></span></div>
    </div>
    <button type="button" id="clearVoyageBtnSeizure" style="margin-top: 10px; padding: 5px 15px; background: #ff9800; color: white; border: none; border-radius: 3px; cursor: pointer;">
        Change Voyage
    </button>
</div>

<form id="ps_seizureForm" method="POST" action="api/submit_seizure.php">

    <!-- Voyage Selection -->
    <div class="form-section" id="ps_voyageSelectionSection">
        <h2>🚢 Voyage Selection</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="ps_SeizureVoyageID">Voyage ID <span class="required">*</span></label>
                <select id="ps_SeizureVoyageID" name="VoyageID" required>
                    <option value="">Select Voyage...</option>
                </select>
                <small>Select from existing voyages or create a new voyage in the Voyage Details tab</small>
            </div>
        </div>
    </div>

    <!-- Seizure Details -->
    <div class="form-section">
        <h2>📋 Seizure Details</h2>
        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="ps_SeizureDate">Seizure Date <span class="required">*</span></label>
                <input type="date" id="ps_SeizureDate" name="SeizureDate" required>
            </div>
            <div class="form-group">
                <label for="ps_SeizureNo">Seizure No</label>
                <input type="text" id="ps_SeizureNo" name="SeizureNo" placeholder="e.g., SZ2025-001">
            </div>
            <div class="form-group">
                <label for="ps_Importer">Importer</label>
                <input type="text" id="ps_Importer" name="Importer" placeholder="Importer name">
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="ps_DetectionMethod">Detection Method</label>
                <select id="ps_DetectionMethod" name="DetectionMethod">
                    <option value="">Select...</option>
                    <option value="X-Ray">X-Ray</option>
                    <option value="Physical Inspection">Physical Inspection</option>
                    <option value="Dog Detection">Dog Detection</option>
                    <option value="Declaration">Declaration</option>
                    <option value="Random Check">Random Check</option>
                </select>
            </div>
            <div class="form-group">
                <label for="ps_PortOfEntry">Port of Entry</label>
                <select id="ps_PortOfEntry" name="PortOfEntry">
                    <option value="">Select...</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="ps_GoodsDeclared">Goods Declared?</label>
                <div style="display: flex; gap: 20px; align-items: center; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="radio" name="GoodsDeclared" value="Yes"> Yes
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="radio" name="GoodsDeclared" value="No" checked> No
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Description of Material Seized -->
    <div class="form-section">
        <h2>📦 Description of Material Seized</h2>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="ps_CountryOfOrigin">Country of Origin</label>
                <select id="ps_CountryOfOrigin" name="CountryOfOrigin">
                    <option value="">Select a country...</option>
                    <!-- Options will be populated dynamically via JavaScript -->
                </select>
            </div>
            <div class="form-group">
                <label for="ps_CommodityType">Commodity Type</label>
                <select id="ps_CommodityType" name="CommodityType">
                    <option value="">Select Commodity...</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group full-width">
                <label for="ps_Description">Description</label>
                <textarea id="ps_Description" name="Description" rows="3" placeholder="Detailed description of seized materials..."></textarea>
            </div>
        </div>
        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="ps_Quantity">Quantity</label>
                <input type="number" id="ps_Quantity" name="Quantity" min="0" step="0.01" placeholder="0">
            </div>
            <div class="form-group">
                <label for="ps_Unit">Unit</label>
                <select id="ps_Unit" name="Unit">
                    <option value="">Select...</option>
                    <option value="kg">Kilograms (kg)</option>
                    <option value="g">Grams (g)</option>
                    <option value="lbs">Pounds (lbs)</option>
                    <option value="pcs">Pieces (pcs)</option>
                    <option value="litres">Litres</option>
                    <option value="units">Units</option>
                </select>
            </div>
            <div class="form-group">
                <label for="ps_Volume">Volume (kg)</label>
                <input type="number" id="ps_Volume" name="Volume" min="0" step="0.01" placeholder="0.00">
            </div>
        </div>
    </div>

    <!-- Action -->
    <div class="form-section">
        <h2>⚡ Action</h2>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="ps_OfficerName">Officer's Name</label>
                <input type="text" id="ps_OfficerName" name="OfficerName" placeholder="Officer name">
            </div>
            <div class="form-group">
                <label for="ps_ActionTaken">Action Taken</label>
                <select id="ps_ActionTaken" name="ActionTaken">
                    <option value="">Select...</option>
                    <option value="Seized and Destroyed">Seized and Destroyed</option>
                    <option value="Seized and Returned">Seized and Returned</option>
                    <option value="Seized for Testing">Seized for Testing</option>
                    <option value="Warning Issued">Warning Issued</option>
                    <option value="Fine Imposed">Fine Imposed</option>
                </select>
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="ps_ActionOfficer">Action Officer</label>
                <input type="text" id="ps_ActionOfficer" name="ActionOfficer" placeholder="Action officer name">
            </div>
            <div class="form-group">
                <label for="ps_DateActionCompleted">Date Action Completed</label>
                <input type="date" id="ps_DateActionCompleted" name="DateActionCompleted">
            </div>
        </div>
    </div>

    <!-- Comments -->
    <div class="form-section">
        <h2>💬 Comments</h2>
        <div class="form-row">
            <div class="form-group full-width">
                <label for="ps_Comments">Comments</label>
                <textarea id="ps_Comments" name="Comments" rows="4" placeholder="Additional comments or notes..."></textarea>
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="form-actions">
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" class="btn btn-primary">
            <span>💾</span> Submit Passenger Seizure Record
        </button>
        <button type="reset" class="btn btn-secondary">
            <span>🔄</span> Reset Form
        </button>
    </div>
</form>

<!-- Recent Seizures -->
<div class="form-section">
    <h2>📋 Recent Passenger Seizures</h2>
    <div id="ps_recentSeizures">
        <p>Loading recent passenger seizures...</p>
    </div>
</div>

<script>
// Country dropdown functionality for passenger seizure form
async function loadCountryDropdownForSeizure() {
    const countrySelect = document.getElementById('ps_CountryOfOrigin');
    if (!countrySelect || countrySelect.options.length > 1) {
        return;
    }

    try {
        const response = await fetch('api/get_countries.php');
        const result = await response.json();

        if (result.success && result.data) {
            countrySelect.innerHTML = `<option value="">Select a country...</option>`;
            result.data.forEach(country => {
                const option = document.createElement('option');
                option.value = country.CountryID;
                option.textContent = country.CountryName;
                countrySelect.appendChild(option);
            });
        } else {
            countrySelect.innerHTML = `<option value="">Error loading countries</option>`;
        }
    } catch (error) {
        console.error('Error loading countries:', error);
        countrySelect.innerHTML = `<option value="">Error loading countries</option>`;
    }
}

// Single initialization on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    loadCountryDropdownForSeizure();
});
</script>
