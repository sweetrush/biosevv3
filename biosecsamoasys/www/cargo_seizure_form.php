<div class="form-container">
    <h2>📦 Cargo Seizure Form</h2>

    <!-- Active Voyage Context -->
    <div id="activeVoyageContextCargoSeizure" style="display: none; padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; margin-bottom: 20px; border-radius: 5px;">
        <h3 style="margin: 0 0 10px 0; color: #2e7d32;">✓ Active Voyage Selected</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <div><strong>Voyage ID:</strong> <span id="contextVoyageIDCargoSeizure"></span></div>
            <div><strong>Voyage No:</strong> <span id="contextVoyageNoCargoSeizure"></span></div>
            <div><strong>Vessel ID:</strong> <span id="contextVesselIDCargoSeizure"></span></div>
        </div>
        <button type="button" id="clearVoyageBtnCargoSeizure" style="margin-top: 10px; padding: 5px 15px; background: #ff9800; color: white; border: none; border-radius: 3px; cursor: pointer;">
            Change Voyage
        </button>
    </div>

    <!-- Voyage Selection Section (hidden when active voyage is set) -->
    <div id="cs_voyageSelectionSection">
        <div class="form-section">
            <h2>🚢 Select Voyage</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="cs_CargoSeizureVoyageID">Voyage <span class="required">*</span></label>
                    <select id="cs_CargoSeizureVoyageID" name="VoyageID" required>
                        <option value="">Select Voyage...</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div id="cs_cargoSeizureMessage" class="message" style="display: none;"></div>

    <form id="cs_cargoSeizureForm" method="POST">
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" id="cs_CargoSeizureVoyageIDHidden" name="VoyageID">

        <!-- New Record Banner -->
        <div style="padding: 12px; background: #e3f2fd; border-left: 4px solid #2196f3; margin-bottom: 20px; border-radius: 5px;">
            <strong>📝 New Cargo Seizure Record</strong> - This form creates a NEW cargo seizure record. Existing records are shown in "Recent Cargo Seizures" below.
        </div>

        <!-- Seizure Details Section -->
        <div class="form-section">
            <h2 style="color: #c0392b;">📋 Seizure Details</h2>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="cs_ContainerCargoRefNo">Container/Cargo Ref. No.</label>
                    <input type="text" id="cs_ContainerCargoRefNo" name="ContainerCargoRefNo">
                </div>
                <div class="form-group">
                    <label for="cs_Importer">Importer</label>
                    <input type="text" id="cs_Importer" name="Importer">
                </div>
                <div class="form-group">
                    <label for="cs_DepotName">Depot Name</label>
                    <input type="text" id="cs_DepotName" name="DepotName">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="cs_CargoDescription">Cargo Description</label>
                    <textarea id="cs_CargoDescription" name="CargoDescription" rows="2"></textarea>
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="cs_DetectionMethod">Detection Method</label>
                    <select id="cs_DetectionMethod" name="DetectionMethod">
                        <option value="">Select...</option>
                        <option value="Manual Inspection">Manual Inspection</option>
                        <option value="X-Ray">X-Ray</option>
                        <option value="K9 Detection">K9 Detection</option>
                        <option value="Random Check">Random Check</option>
                        <option value="Intelligence">Intelligence</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="PortOfEntry">Port of Entry</label>
                    <select id="PortOfEntry" name="PortOfEntry">
                        <option value="">Select...</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Description of Material Seized -->
        <div class="form-section">
            <h2 style="color: #c0392b;">📦 Description of Material Seized</h2>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="SeizureDate">Seizure Date <span class="required">*</span></label>
                    <input type="date" id="SeizureDate" name="SeizureDate" required>
                </div>
                <div class="form-group">
                    <label for="SeizureNo">Seizure No.</label>
                    <input type="text" id="SeizureNo" name="SeizureNo" readonly style="background: #e8f5e9; font-weight: bold;" placeholder="Auto-generated">
                    <small style="color: #666;">Automatically generated</small>
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="cs_CountryOfOrigin">Country of Origin</label>
                    <select id="cs_CountryOfOrigin" name="CountryOfOrigin">
                        <option value="">Select a country...</option>
                        <!-- Options will be populated dynamically via JavaScript -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="cs_CommodityType">Commodity Type</label>
                    <select id="cs_CommodityType" name="CommodityType">
                        <option value="">Select Commodity...</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="cs_Description">Description</label>
                    <textarea id="cs_Description" name="Description" rows="3"></textarea>
                </div>
            </div>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="cs_Quantity">Quantity</label>
                    <input type="number" id="cs_Quantity" name="Quantity" min="0" step="0.01" placeholder="0">
                </div>
                <div class="form-group">
                    <label for="cs_Unit">Unit</label>
                    <select id="cs_Unit" name="Unit">
                        <option value="">Select...</option>
                        <option value="Bottles">Bottles</option>
                        <option value="Boxes">Boxes</option>
                        <option value="Cartons">Cartons</option>
                        <option value="Containers">Containers</option>
                        <option value="Kg">Kg</option>
                        <option value="Litres">Litres</option>
                        <option value="Pieces">Pieces</option>
                        <option value="Packets">Packets</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cs_VolumeKg">Volume (kg)</label>
                    <input type="number" id="cs_VolumeKg" name="VolumeKg" min="0" step="0.01" placeholder="0.00">
                </div>
            </div>
        </div>

        <!-- Action Section -->
        <div class="form-section">
            <h2 style="color: #c0392b;">⚡ Action</h2>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="SeizingOfficerName">Seizing Officer's Name</label>
                    <input type="text" id="SeizingOfficerName" name="SeizingOfficerName" placeholder="Akerel Leau">
                </div>
                <div class="form-group">
                    <label for="ActionTaken">Action Taken</label>
                    <select id="ActionTaken" name="ActionTaken">
                        <option value="">Select...</option>
                        <option value="Fumigate">Fumigate</option>
                        <option value="Destroy">Destroy</option>
                        <option value="Re-export">Re-export</option>
                        <option value="Detained">Detained</option>
                        <option value="Released">Released</option>
                        <option value="Quarantine">Quarantine</option>
                    </select>
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="ActionOfficer">Action Officer</label>
                    <input type="text" id="ActionOfficer" name="ActionOfficer" placeholder="Anelika Faavesi">
                </div>
                <div class="form-group">
                    <label for="DateActionCompleted">Date Action Completed</label>
                    <input type="date" id="DateActionCompleted" name="DateActionCompleted">
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="form-section">
            <h2 style="color: #c0392b;">💬 Comments</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="Comments">Comments</label>
                    <textarea id="Comments" name="Comments" rows="4" placeholder="Enter any additional comments..."></textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="submit-btn">💾 Save Cargo Seizure</button>
            <button type="reset" class="reset-btn">🔄 Reset Form</button>
        </div>
    </form>

    <!-- Recent Cargo Seizures -->
    <div class="form-section" style="margin-top: 30px;">
        <h2 style="color: #c0392b;">📊 Recent Cargo Seizures</h2>
        <div id="recentCargoSeizures">
            <p>Loading recent cargo seizures...</p>
        </div>
    </div>
</div>

<style>
.submit-btn {
    padding: 15px 40px;
    font-size: 1.1em;
    font-weight: 600;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
}

.reset-btn {
    padding: 15px 40px;
    font-size: 1.1em;
    font-weight: 600;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #e2e8f0;
    color: #4a5568;
}

.reset-btn:hover {
    background: #cbd5e0;
}
</style>

<script>
// Country dropdown functionality for cargo seizure form
async function loadCountryDropdownForCargoSeizure() {
    const countrySelect = document.getElementById('cs_CountryOfOrigin');
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
    loadCountryDropdownForCargoSeizure();
});
</script>
