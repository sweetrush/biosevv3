<div id="inspectionMessage" class="message"></div>

<!-- Active Voyage Context -->
<div id="activeVoyageContext" style="display: none; padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; margin-bottom: 20px; border-radius: 5px;">
    <h3 style="margin: 0 0 10px 0; color: #2e7d32;">✓ Active Voyage Selected</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
        <div><strong>Voyage ID:</strong> <span id="contextVoyageID"></span></div>
        <div><strong>Voyage No:</strong> <span id="contextVoyageNo"></span></div>
        <div><strong>Vessel ID:</strong> <span id="contextVesselID"></span></div>
    </div>
    <button type="button" onclick="clearActiveVoyage()" style="margin-top: 10px; padding: 5px 15px; background: #ff9800; color: white; border: none; border-radius: 3px; cursor: pointer;">
        Change Voyage
    </button>
</div>

<form id="inspectionForm" method="POST" action="api/submit_inspection.php">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <!-- Voyage Selection -->
    <div class="form-section" id="voyageSelectionSection">
        <h2>🚢 Voyage Selection</h2>
        <p class="section-description">Select the voyage this inspection is associated with</p>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="InspectionVoyageID">Voyage ID <span class="required">*</span></label>
                <select id="InspectionVoyageID" name="VoyageID" required>
                    <option value="">Select Voyage...</option>
                </select>
                <small>Select from existing voyages or create a new voyage in the Voyage Details tab</small>
            </div>
            <div class="form-group">
                <label for="InspectionLocationID">Location ID</label>
                <select id="InspectionLocationID" name="LocationID">
                    <option value="">Select Location...</option>
                </select>
                <small>Inspection location</small>
            </div>
        </div>
    </div>

    <!-- Commodity Information -->
    <div class="form-section">
        <h2>📦 Commodity Information</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="CommodityTypeID">Commodity Type <span class="required">*</span></label>
                <select id="CommodityTypeID" name="CommodityTypeID" required>
                    <option value="">Select Commodity...</option>
                </select>
                <small>Type of commodity being inspected (grouped by category)</small>
            </div>
        </div>
        <div class="form-row">
            <div id="commodityInfo" style="display: none; padding: 10px; background: #f8f9fa; border-radius: 5px; margin-top: 10px;">
                <strong>📋 Details:</strong> <span id="commodityDesc"></span>
            </div>
        </div>
    </div>

    <!-- Inspection Results -->
    <div class="form-section">
        <h2>🔍 Inspection Results</h2>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="NoOfConsignments">Number of Consignments</label>
                <input type="number" id="NoOfConsignments" name="NoOfConsignments" min="0" placeholder="0">
                <small>Total consignments inspected</small>
            </div>
            <div class="form-group">
                <label for="NoOfNonCompliant">Number of Non-Compliant</label>
                <input type="number" id="NoOfNonCompliant" name="NoOfNonCompliant" min="0" placeholder="0">
                <small>Non-compliant consignments found</small>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <div id="complianceStatus" style="padding: 10px; margin-top: 10px; border-radius: 5px; display: none;">
                    <strong>Compliance Rate: <span id="complianceRate">0%</span></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Information -->
    <div class="form-section">
        <h2>📝 Record Information</h2>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="InspectionModifiedBy">Modified By</label>
                <input type="text" id="InspectionModifiedBy" name="ModifiedBy" placeholder="Your name">
            </div>
            <div class="form-group">
                <label for="InspectionModifiedDate">Modified Date</label>
                <input type="date" id="InspectionModifiedDate" name="ModifiedDate">
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <span>💾</span> Submit Inspection Record
        </button>
        <button type="reset" class="btn btn-secondary">
            <span>🔄</span> Reset Form
        </button>
    </div>
</form>

<!-- Recent Inspections -->
<div class="form-section">
    <h2>📋 Recent Inspections</h2>
    <div id="recentInspections">
        <p>Loading recent inspections...</p>
    </div>
</div>
