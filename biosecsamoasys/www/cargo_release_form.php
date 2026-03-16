<div class="form-container">
    <h2>🚢 Cargo Release Form</h2>

    <!-- Active Voyage Context -->
    <div id="activeVoyageContextRelease" style="display: none; padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; margin-bottom: 20px; border-radius: 5px;">
        <h3 style="margin: 0 0 10px 0; color: #2e7d32;">✓ Active Voyage Selected</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <div><strong>Voyage ID:</strong> <span id="contextVoyageIDRelease"></span></div>
            <div><strong>Voyage No:</strong> <span id="contextVoyageNoRelease"></span></div>
            <div><strong>Vessel ID:</strong> <span id="contextVesselIDRelease"></span></div>
        </div>
        <button type="button" onclick="clearActiveVoyageRelease()" style="margin-top: 10px; padding: 5px 15px; background: #ff9800; color: white; border: none; border-radius: 3px; cursor: pointer;">
            Change Voyage
        </button>
    </div>

    <!-- Voyage Selection Section (hidden when active voyage is set) -->
    <div id="voyageSelectionSectionRelease">
        <div class="form-section">
            <h2>🚢 Select Voyage</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="ReleaseVoyageID">Voyage <span class="required">*</span></label>
                    <select id="ReleaseVoyageID" name="VoyageID" required>
                        <option value="">Select Voyage...</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div id="releaseMessage" class="message" style="display: none;"></div>

    <form id="cargoReleaseForm" method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" id="ReleaseVoyageIDHidden" name="VoyageID">

        <!-- Release Details Section -->
        <div class="form-section">
            <h2>📋 Release Details</h2>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="ReleaseNo">Release No</label>
                    <input type="text" id="ReleaseNo" name="ReleaseNo">
                </div>
                <div class="form-group">
                    <label for="ReleaseImporter">Importer</label>
                    <input type="text" id="ReleaseImporter" name="Importer">
                </div>
                <div class="form-group">
                    <label for="ReleaseType">Release Type</label>
                    <select id="ReleaseType" name="ReleaseType">
                        <option value="">Select...</option>
                        <option value="Commercial">Commercial</option>
                        <option value="Personal">Personal</option>
                        <option value="Government">Government</option>
                    </select>
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="ReleaseDate">Release Date <span class="required">*</span></label>
                    <input type="date" id="ReleaseDate" name="ReleaseDate" required>
                </div>
                <div class="form-group">
                    <label for="TotalCosts">Total Costs</label>
                    <input type="number" step="0.01" id="TotalCosts" name="TotalCosts" placeholder="0.00">
                </div>
            </div>
        </div>

        <!-- Release Items Section -->
        <div class="form-section">
            <h2>📦 Release Items</h2>
            <div style="margin-bottom: 15px;">
                <button type="button" onclick="addReleaseItem()" style="padding: 8px 15px; background: #4caf50; color: white; border: none; border-radius: 3px; cursor: pointer; font-weight: 600;">
                    + Add Release Item
                </button>
            </div>

            <div id="releaseItemsContainer">
                <!-- Release items will be added here dynamically -->
            </div>
        </div>

        <!-- Comments Section -->
        <div class="form-section">
            <h2>💬 Comments</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="ReleaseComments">Comments</label>
                    <textarea id="ReleaseComments" name="Comments" rows="4" placeholder="Enter any additional comments..."></textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="submit-btn">💾 Save Cargo Release</button>
            <button type="reset" class="reset-btn">🔄 Reset Form</button>
        </div>
    </form>

    <!-- Recent Cargo Releases -->
    <div class="form-section" style="margin-top: 30px;">
        <h2>📊 Recent Cargo Releases</h2>
        <div id="recentReleases">
            <p>Loading recent releases...</p>
        </div>
    </div>
</div>

<style>
.release-item {
    background: #f8f9fa;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    position: relative;
}

.release-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #ddd;
}

.release-item-header h3 {
    margin: 0;
    color: #2c3e50;
}

.remove-item-btn {
    padding: 5px 12px;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-weight: 600;
}

.remove-item-btn:hover {
    background: #c0392b;
}

.item-section {
    margin-bottom: 15px;
}

.item-section h4 {
    margin: 0 0 10px 0;
    color: #34495e;
    font-size: 16px;
    border-left: 3px solid #3498db;
    padding-left: 10px;
}
</style>
