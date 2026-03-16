<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voyage Detail Input - Biosecurity System</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <header>
            <h1>🚢 Biosecurity Voyage Detail Input Form</h1>
            <p>Vessel and Voyage Information for Biosecurity Compliance Tracking</p>
        </header>

        <div id="message" class="message"></div>

        <form id="voyageForm" method="POST" action="api/submit_voyage.php">

            <!-- Voyage & Vessel Information -->
            <div class="form-section">
                <h2>📋 Voyage & Vessel Information</h2>
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label for="VoyageNo">Voyage Number <span class="required">*</span></label>
                        <input type="text" id="VoyageNo" name="VoyageNo" required placeholder="e.g., V2025-001">
                    </div>
                    <div class="form-group">
                        <label for="VesselID">Vessel ID <span class="required">*</span></label>
                        <input type="text" id="VesselID" name="VesselID" required placeholder="e.g., VESSEL001">
                    </div>
                    <div class="form-group">
                        <label for="AirOfSea">Transport Mode</label>
                        <select id="AirOfSea" name="AirOfSea">
                            <option value="">Select...</option>
                            <option value="Sea">Sea</option>
                            <option value="Air">Air</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Port Information -->
            <div class="form-section">
                <h2>🏝️ Port & Location Information</h2>
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label for="PortOfLoadingID">Port of Loading ID</label>
                        <input type="text" id="PortOfLoadingID" name="PortOfLoadingID" placeholder="e.g., PORT001">
                    </div>
                    <div class="form-group">
                        <label for="LastPortID">Last Port ID</label>
                        <input type="text" id="LastPortID" name="LastPortID" placeholder="e.g., PORT002">
                    </div>
                    <div class="form-group">
                        <label for="PortOfArrivalID">Port of Arrival ID <span class="required">*</span></label>
                        <input type="text" id="PortOfArrivalID" name="PortOfArrivalID" required placeholder="e.g., PORT001">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="LocationID">Location ID</label>
                        <input type="text" id="LocationID" name="LocationID" placeholder="e.g., LOC001">
                    </div>
                    <div class="form-group">
                        <label for="ArrivalDate">Arrival Date <span class="required">*</span></label>
                        <input type="date" id="ArrivalDate" name="ArrivalDate" required>
                    </div>
                    <div class="form-group">
                        <label for="PortAuthority">Port Authority</label>
                        <input type="text" id="PortAuthority" name="PortAuthority" placeholder="Authority name">
                    </div>
                </div>
            </div>

            <!-- People Information -->
            <div class="form-section">
                <h2>👥 Passenger & Crew Information</h2>
                <div class="form-row form-row-4">
                    <div class="form-group">
                        <label for="Pax">Passengers (Pax)</label>
                        <input type="number" id="Pax" name="Pax" min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="Crew">Crew</label>
                        <input type="number" id="Crew" name="Crew" min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="CrewSearched">Crew Searched</label>
                        <input type="number" id="CrewSearched" name="CrewSearched" min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="TotalDischarged">Total Discharged</label>
                        <input type="number" id="TotalDischarged" name="TotalDischarged" min="0" placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Vessel Inspection -->
            <div class="form-section">
                <h2>🔍 Vessel Inspection Details</h2>
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label for="RoomSealed">Room Sealed</label>
                        <select id="RoomSealed" name="RoomSealed">
                            <option value="">Select...</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="NoXRated">X-Ray Inspection</label>
                        <input type="text" id="NoXRated" name="NoXRated" placeholder="Number of items">
                    </div>
                </div>
            </div>

            <!-- Animal & Biosecurity -->
            <div class="form-section">
                <h2>🐾 Animal & Biosecurity Information</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="BondedAnimals">Bonded Animals</label>
                        <select id="BondedAnimals" name="BondedAnimals">
                            <option value="">Select...</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="AnimalHealthCertificate">Animal Health Certificate</label>
                        <select id="AnimalHealthCertificate" name="AnimalHealthCertificate">
                            <option value="">Select...</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="BondedAnimalsDescription">Bonded Animals Description</label>
                        <textarea id="BondedAnimalsDescription" name="BondedAnimalsDescription" rows="3" placeholder="Describe any bonded animals..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Cargo Information -->
            <div class="form-section">
                <h2>📦 Cargo Information</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="NumberContainers">Number of Containers</label>
                        <input type="number" id="NumberContainers" name="NumberContainers" min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="NumberCargosDischarged">Number of Cargos Discharged</label>
                        <input type="number" id="NumberCargosDischarged" name="NumberCargosDischarged" min="0" placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Container Type Counts -->
            <div class="form-section">
                <h2>🚛 Cargo Type Counts</h2>
                <p class="section-description">Enter the quantity for each cargo type (leave blank if none)</p>

                <div class="cargo-categories">
                    <!-- Vehicles -->
                    <div class="cargo-category">
                        <h3>🚗 Vehicles</h3>
                        <div id="vehicleTypes" class="cargo-type-grid"></div>
                    </div>

                    <!-- Frozen Products -->
                    <div class="cargo-category">
                        <h3>❄️ Frozen Products</h3>
                        <div id="frozenTypes" class="cargo-type-grid"></div>
                    </div>

                    <!-- Fresh Products -->
                    <div class="cargo-category">
                        <h3>🌱 Fresh Products</h3>
                        <div id="freshTypes" class="cargo-type-grid"></div>
                    </div>
                </div>
            </div>

            <!-- Modification Details -->
            <div class="form-section">
                <h2>📝 Record Information</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ModifiedBy">Modified By</label>
                        <input type="text" id="ModifiedBy" name="ModifiedBy" placeholder="Your name">
                    </div>
                    <div class="form-group">
                        <label for="ModifiedDate">Modified Date</label>
                        <input type="date" id="ModifiedDate" name="ModifiedDate">
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Submit Voyage Details
                </button>
                <button type="reset" class="btn btn-secondary">
                    <span>🔄</span> Reset Form
                </button>
            </div>
        </form>
    </div>

    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
