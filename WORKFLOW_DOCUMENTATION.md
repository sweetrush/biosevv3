# BioSec Samoa System - Workflow Documentation

**Repository:** https://github.com/sweetrush/biosevv3
**Last Updated:** 2026-03-16

---

## Table of Contents

1. [Overview](#overview)
2. [Master Workflow: 5-Step Voyage Processing](#master-workflow-5-step-voyage-processing)
3. [Step 1: Voyage Details](#step-1-voyage-details)
4. [Step 2: Passenger Inspection](#step-2-passenger-inspection)
5. [Step 3: Passenger Seizure](#step-3-passenger-seizure)
6. [Step 4: Cargo Seizure](#step-4-cargo-seizure)
7. [Step 5: Cargo Release](#step-5-cargo-release)
8. [State Management & Persistence](#state-management--persistence)
9. [Cross-Step Data Flow](#cross-step-data-flow)
10. [API Interactions](#api-interactions)
11. [JavaScript Integration](#javascript-integration)
12. [Audit Trail](#audit-trail)

---

## Overview

The BioSec Samoa system implements a **5-step sequential workflow** for processing vessel voyages at Samoan ports. Each voyage must progress through all 5 steps to be marked as complete. The workflow is managed by `voyagement.php`, which serves as a container page with tab navigation.

### Workflow Philosophy

```
Voyage Arrives → Record Details → Inspect Passengers → Seize Non-Compliant Items →
Process Cargo Seizures → Release Compliant Cargo → Voyage Complete
```

### Key Design Principles

1. **Sequential Processing** - Steps should be completed in order (1→2→3→4→5)
2. **Context Persistence** - Active voyage is shared across all tabs via localStorage
3. **Independent Submissions** - Each step can be submitted independently
4. **Status Tracking** - `voyage_status` table tracks completion of each step
5. **Audit Trail** - All actions are logged to `voyage_audit_trail`

---

## Master Workflow: 5-Step Voyage Processing

### Workflow Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        VOYAGEMENT WORKFLOW                                  │
│                    (voyagement.php - Container)                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐│
│  │  STEP 1  │───▶│  STEP 2  │───▶│  STEP 3  │───▶│  STEP 4  │───▶│  STEP 5  ││
│  │  Voyage  │    │ Passenger│    │ Passenger│    │  Cargo   │    │  Cargo   ││
│  │ Details  │    │Inspection│    │ Seizure  │    │ Seizure  │    │ Release  ││
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘    └──────────┘│
│       │               │               │               │               │     │
│       ▼               ▼               ▼               ▼               ▼     │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐│
│  │ submit_  │    │ submit_  │    │ submit_  │    │ submit_  │    │ submit_  ││
│  │ voyage   │    │inspection│    │ seizure  │    │  cargo_  │    │  cargo_  ││
│  │   .php   │    │   .php   │    │   .php   │    │ seizure  │    │ release  ││
│  └──────────┘    └──────────┘    └──────────┘    │   .php   │    │   .php   ││
│       │               │               │          └──────────┘    └──────────┘│
│       │               │               │               │               │     │
│       └───────────────┴───────────────┴───────────────┴───────────────┘     │
│                                      │                                      │
│                                      ▼                                      │
│                            ┌──────────────────┐                              │
│                            │ voyage_status.php │                              │
│                            │ (Status Updates) │                              │
│                            └──────────────────┘                              │
│                                      │                                      │
│                                      ▼                                      │
│                            ┌──────────────────┐                              │
│                            │voyage_audit_trail│                              │
│                            │  (Audit Logs)    │                              │
│                            └──────────────────┘                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Tab Navigation Structure

The `voyagement.php` file renders 5 tabs, each containing a form:

| Tab | Step | Icon | Form File | API Endpoint |
|-----|------|------|-----------|--------------|
| 1 | Voyage Details | 🚢 | `voyage_form.php` | `submit_voyage.php` |
| 2 | Passenger Inspection | 🔍 | `inspection_form.php` | `submit_inspection.php` |
| 3 | Passenger Seizure | ⚠️ | `seizure_form.php` | `submit_seizure.php` |
| 4 | Cargo Seizure | 🚨 | `cargo_seizure_form.php` | `submit_cargo_seizure.php` |
| 5 | Cargo Release | 📦 | `cargo_release_form.php` | `submit_cargo_release.php` |

---

## Step 1: Voyage Details

**File:** `voyage_form.php`
**Script:** `script.js`
**API:** `submit_voyage.php`

### Purpose
Record all information about an arriving vessel including voyage number, ports, passenger/crew counts, cargo details, and biosecurity compliance data.

### Form Fields

#### Voyage & Vessel Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `VoyageNo` | Text | Yes | Voyage identifier (e.g., V2025-001) |
| `VesselID` | Text | Yes | Vessel identifier |
| `AirOfSea` | Select | No | Transport mode (Sea/Air) |

#### Port & Location Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `PortOfLoadingID` | Select | No | Country of origin (loaded from `get_countries.php`) |
| `LastPortID` | Select | No | Last port of call |
| `PortOfArrivalID` | Select | Yes | Destination port (loaded from `get_ports.php`) |
| `LocationID` | Select | Yes | Terminal/facility location |
| `ArrivalDate` | Date | Yes | Date of arrival |
| `PortAuthority` | Text | No | Port authority name |

#### Passenger & Crew Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `Pax` | Number | No | Number of passengers |
| `Crew` | Number | No | Number of crew members |
| `CrewSearched` | Number | No | Number of crew searched |
| `TotalDischarged` | Number | No | Total items discharged |

#### Vessel Inspection Details
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `RoomSealed` | Select | No | Room sealing status (Yes/No) |
| `NoXRated` | Text | No | X-ray inspection count |

#### Animal & Biosecurity Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `BondedAnimals` | Select | No | Bonded animals present (Yes/No) |
| `BondedAnimalsDescription` | Textarea | No | Description of bonded animals |
| `AnimalHealthCertificate` | Select | No | Health certificate status (Yes/No/N/A) |

#### Cargo Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `NumberContainers` | Number | No | Total container count |
| `NumberCargosDischarged` | Number | No | Cargos discharged count |

#### Cargo Type Counts (Dynamic)
19 cargo types loaded from `container_types` table via `get_container_types.php`:

**Vehicles:**
- Cars, Carsandparts, CarsandPersonalEffects

**Frozen Products:**
- FrozenBeef, FrozenPork, FrozenChicken, FrozenLamb, FrozenTurky, FrozenVegies, FrozenFruits, FrozenMix

**Fresh Products:**
- FreshBeef, FreshPork, FreshChicken, FreshLamb, FreshTurky, FreshVegies, FreshFruits, FreshMix

#### Record Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `ModifiedBy` | Text | No | Officer making the entry |
| `ModifiedDate` | Date | No | Date of modification |

### Workflow Steps

```
1. Page loads → DOMContentLoaded event fires
2. script.js initializes:
   - loadContainerTypes() fetches cargo types from API
   - Auto-populates ArrivalDate and ModifiedDate with today
3. Dropdowns populated via async fetch:
   - Locations: get_locations.php → grouped by region
   - Countries: get_countries.php → grouped by first letter
   - Ports: get_ports.php → grouped by country
4. User fills form fields
5. User clicks "Submit Voyage Details"
6. Form submits via fetch() to submit_voyage.php
7. API validates CSRF token and required fields
8. API inserts into voyage_details table
9. API inserts container counts into voyage_container_counts
10. API calls voyage_status.php to mark step as complete
11. JavaScript stores voyage context in localStorage:
    - activeVoyageID
    - activeVoyageNo
    - activeVesselID
12. Success message displayed, form resets
```

### Data Flow

```
voyage_form.php
      │
      ├──▶ get_locations.php (async)
      │         └──▶ locations table
      │
      ├──▶ get_countries.php (async)
      │         └──▶ countries table
      │
      ├──▶ get_ports.php (async)
      │         └──▶ ports table
      │
      ├──▶ get_container_types.php (async)
      │         └──▶ container_types table
      │
      └──▶ submit_voyage.php (POST)
                │
                ├──▶ INSERT voyage_details
                ├──▶ INSERT voyage_container_counts (multiple)
                └──▶ voyage_status.php (complete_step)
                          └──▶ UPDATE voyage_status
                          └──▶ INSERT voyage_audit_trail
```

---

## Step 2: Passenger Inspection

**File:** `inspection_form.php`
**Script:** `inspection_script.js`
**API:** `submit_inspection.php`

### Purpose
Record passenger compliance inspections, tracking consignments inspected and non-compliant items found.

### Form Fields

#### Voyage Selection
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `VoyageID` | Select | Yes | Associated voyage (auto-filled if active voyage) |
| `LocationID` | Select | No | Inspection location |

#### Commodity Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `CommodityTypeID` | Select | Yes | Commodity type being inspected |

#### Inspection Results
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `NoOfConsignments` | Number | No | Total consignments inspected |
| `NoOfNonCompliant` | Number | No | Non-compliant consignments found |

#### Record Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `ModifiedBy` | Text | No | Officer name |
| `ModifiedDate` | Date | No | Date of inspection |

### Workflow Steps

```
1. Tab activated → checkActiveVoyage() called
2. If active voyage exists in localStorage:
   - Show active voyage context banner
   - Hide voyage selection dropdown
   - Auto-select voyage in dropdown
3. inspection_script.js initializes:
   - loadVoyages() fetches all voyages
   - loadCommodities() fetches commodity types (grouped)
   - loadLocations() fetches locations
   - loadRecentInspections() displays recent records
4. User selects commodity type → showCommodityInfo() displays details
5. User enters consignment counts → calculateCompliance() shows rate
   - ≥95%: Green (compliant)
   - 80-94%: Yellow (warning)
   - <80%: Red (non-compliant)
6. User submits form
7. API validates and inserts into passenger_inspection table
8. voyage_status.php called to mark step complete
9. Recent inspections table refreshed
```

### Compliance Calculation

```javascript
function calculateCompliance() {
    const consignments = parseInt(NoOfConsignments) || 0;
    const nonCompliant = parseInt(NoOfNonCompliant) || 0;

    if (consignments > 0) {
        const compliantCount = consignments - nonCompliant;
        const rate = (compliantCount / consignments * 100).toFixed(1);
        // Display with color coding
    }
}
```

### Commodity Grouping

Commodities are grouped by `CommodityGroupID`:

| Group ID | Name | Icon |
|----------|------|------|
| 1 | Animals and Animal Products | 🐄 |
| 2 | Plants and Plant Products | 🌱 |
| 3 | MEV (Machinery, Equipment, Vehicles) | 🚗 |
| 4 | Other | 📦 |
| 5 | Pesticides | ⚠️ |

---

## Step 3: Passenger Seizure

**File:** `seizure_form.php`
**Script:** `seizure_script.js`
**API:** `submit_seizure.php`

### Purpose
Record items seized from passengers during inspection, including details about the seizure, items seized, and actions taken.

### Form Fields

#### Voyage Selection
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `VoyageID` | Select | Yes | Associated voyage |

#### Seizure Details
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `SeizureDate` | Date | Yes | Date of seizure |
| `SeizureNo` | Text | No | Seizure reference number |
| `Importer` | Text | No | Importer name |
| `DetectionMethod` | Select | No | How items were detected |
| `PortOfEntry` | Select | No | Port where seizure occurred |
| `GoodsDeclared` | Radio | No | Were goods declared? (Yes/No) |

#### Detection Methods
- X-Ray
- Physical Inspection
- Dog Detection
- Declaration
- Random Check

#### Description of Material Seized
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `CountryOfOrigin` | Select | No | Country of origin |
| `CommodityType` | Select | No | Type of commodity |
| `Description` | Textarea | No | Detailed description |
| `Quantity` | Number | No | Quantity seized |
| `Unit` | Select | No | Unit of measurement |
| `Volume` | Number | No | Volume in kg |

#### Units Available
- Kilograms (kg)
- Grams (g)
- Pounds (lbs)
- Pieces (pcs)
- Litres
- Units

#### Action Section
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `OfficerName` | Text | No | Seizing officer's name |
| `ActionTaken` | Select | No | Action taken on seized items |
| `ActionOfficer` | Text | No | Action officer name |
| `DateActionCompleted` | Date | No | Date action completed |

#### Actions Available
- Seized and Destroyed
- Seized and Returned
- Seized for Testing
- Warning Issued
- Fine Imposed

#### Comments
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `Comments` | Textarea | No | Additional notes |

### Workflow Steps

```
1. Tab activated → checkActiveVoyageSeizure() called
2. Active voyage context displayed if localStorage has voyage
3. seizure_script.js initializes:
   - loadVoyagesForSeizure() fetches voyages
   - loadPortsForSeizure() fetches ports
   - loadCommoditiesForSeizure() fetches commodities
   - loadRecentSeizures() displays recent records
4. User selects country via loadCountryDropdownForSeizure()
5. User fills seizure details
6. Form submitted via fetch() to submit_seizure.php
7. API validates and inserts into passenger_seizure table
8. voyage_status.php marks step complete
9. Recent seizures table refreshed
```

### Country Loading

```javascript
async function loadCountryDropdownForSeizure() {
    const response = await fetch('api/get_countries.php');
    const result = await response.json();
    // Populate select with country options
}
```

---

## Step 4: Cargo Seizure

**File:** `cargo_seizure_form.php`
**Script:** `cargo_seizure_script.js`
**API:** `submit_cargo_seizure.php`

### Purpose
Record commercial cargo seizures, tracking container references, importers, seized materials, and disposition actions.

### Form Fields

#### Voyage Selection
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `VoyageID` | Select | Yes | Associated voyage |

#### Seizure Details
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `ContainerCargoRefNo` | Text | No | Container/cargo reference number |
| `Importer` | Text | No | Importer name |
| `DepotName` | Text | No | Depot location |
| `CargoDescription` | Textarea | No | Description of cargo |
| `DetectionMethod` | Select | No | Detection method |
| `PortOfEntry` | Select | No | Port of entry |

#### Detection Methods
- Manual Inspection
- X-Ray
- K9 Detection
- Random Check
- Intelligence

#### Description of Material Seized
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `SeizureDate` | Date | Yes | Date of seizure |
| `SeizureNo` | Text | No | Seizure number |
| `CountryOfOrigin` | Select | No | Country of origin |
| `CommodityType` | Select | No | Commodity type |
| `Description` | Textarea | No | Detailed description |
| `Quantity` | Text | No | Quantity |
| `Unit` | Select | No | Unit of measurement |
| `VolumeKg` | Text | No | Volume in kg |

#### Units Available
- Bottles
- Boxes
- Cartons
- Containers
- Kg
- Litres
- Pieces
- Packets

#### Action Section
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `SeizingOfficerName` | Text | No | Officer's name |
| `ActionTaken` | Select | No | Disposition action |
| `ActionOfficer` | Text | No | Action officer |
| `DateActionCompleted` | Date | No | Completion date |

#### Actions Available
- Fumigate
- Destroy
- Re-export
- Detained
- Released
- Quarantine

### Workflow Steps

```
1. Tab activated → checkActiveVoyageCargoSeizure() called
2. Active voyage context displayed if available
3. cargo_seizure_script.js initializes:
   - loadVoyagesForCargoSeizure()
   - loadPortsForCargoSeizure()
   - loadCommoditiesForCargoSeizure()
   - loadRecentCargoSeizures()
4. User fills cargo seizure details
5. Form submitted to submit_cargo_seizure.php
6. API inserts into cargo_seizure table
7. voyage_status.php marks step complete
8. Recent cargo seizures refreshed
```

### Styling

Cargo seizure form uses red-themed styling to emphasize severity:
- Submit button: Red gradient (#e74c3c → #c0392b)
- Section headers: Red color (#c0392b)

---

## Step 5: Cargo Release

**File:** `cargo_release_form.php`
**Script:** `cargo_release_script.js`
**API:** `submit_cargo_release.php`

### Purpose
Process and authorize release of cargo that has passed inspection or completed required treatments.

### Form Fields

#### Voyage Selection
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `VoyageID` | Select | Yes | Associated voyage |

#### Release Details
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `ReleaseNo` | Text | No | Release reference number |
| `Importer` | Text | No | Importer name |
| `ReleaseType` | Select | No | Type of release |
| `ReleaseDate` | Date | Yes | Date of release |
| `TotalCosts` | Number | No | Total costs associated |

#### Release Types
- Commercial
- Personal
- Government

#### Release Items (Dynamic)
Users can add multiple release items dynamically:
- Click "+ Add Release Item" button
- Each item gets its own section
- Items can be removed individually

#### Comments
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `Comments` | Textarea | No | Additional notes |

### Workflow Steps

```
1. Tab activated → checkActiveVoyageRelease() called
2. Active voyage context displayed if available
3. cargo_release_script.js initializes:
   - loadVoyagesForRelease()
   - loadRecentReleases()
4. User adds release items dynamically:
   - addReleaseItem() creates new item section
   - Each item can be removed with removeReleaseItem()
5. Form submitted to submit_cargo_release.php
6. API inserts into cargo_release table
7. voyage_status.php marks step complete (final step)
8. If all steps complete, voyage marked as 'completed'
```

### Dynamic Item Management

```javascript
function addReleaseItem() {
    const container = document.getElementById('releaseItemsContainer');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'release-item';
    itemDiv.innerHTML = `
        <div class="release-item-header">
            <h3>Item #${itemCount}</h3>
            <button type="button" class="remove-item-btn"
                    onclick="removeReleaseItem(this)">Remove</button>
        </div>
        <!-- Item fields here -->
    `;
    container.appendChild(itemDiv);
}

function removeReleaseItem(button) {
    button.closest('.release-item').remove();
    // Renumber remaining items
}
```

---

## State Management & Persistence

### localStorage Keys

The system uses localStorage to maintain voyage context across tabs:

| Key | Description | Set By |
|-----|-------------|--------|
| `activeVoyageID` | Current voyage ID | Step 1 submission |
| `activeVoyageNo` | Voyage number | Step 1 submission |
| `activeVesselID` | Vessel identifier | Step 1 submission |
| `selectedTheme` | User's theme preference | Settings page |

### Context Flow

```
Step 1 Submission
      │
      ▼
localStorage.setItem('activeVoyageID', voyage_id)
localStorage.setItem('activeVoyageNo', voyageNo)
localStorage.setItem('activeVesselID', vesselID)
      │
      ▼
Tab Switch to Step 2
      │
      ▼
checkActiveVoyage() reads localStorage
      │
      ├──▶ Show active voyage context banner
      ├──▶ Hide voyage selection section
      └──▶ Auto-select voyage in dropdown
```

### Clearing Context

Each step has a "Change Voyage" button that clears localStorage:

```javascript
function clearActiveVoyage() {
    localStorage.removeItem('activeVoyageID');
    localStorage.removeItem('activeVoyageNo');
    localStorage.removeItem('activeVesselID');
    checkActiveVoyage(); // Refresh UI
}
```

---

## Cross-Step Data Flow

### Database Relationships

```
voyage_details (Created in Step 1)
      │
      ├──▶ voyage_container_counts (Step 1 - cargo type counts)
      │
      ├──▶ passenger_inspection (Step 2)
      │         └──▶ commodity_types (reference)
      │
      ├──▶ passenger_seizure (Step 3)
      │
      ├──▶ cargo_seizure (Step 4)
      │
      ├──▶ cargo_release (Step 5)
      │
      ├──▶ voyage_status (Updated throughout)
      │
      └──▶ voyage_audit_trail (Logged throughout)
```

### Voyage Status Table

Tracks completion of each step:

| Field | Type | Description |
|-------|------|-------------|
| `status_id` | INT | Primary key |
| `VoyageID` | INT | Foreign key to voyage_details |
| `current_step` | VARCHAR | Current workflow step |
| `status` | ENUM | Overall status (draft/in_progress/completed/archived) |
| `voyage_details_complete` | BOOLEAN | Step 1 complete |
| `passenger_inspection_complete` | BOOLEAN | Step 2 complete |
| `passenger_seizure_complete` | BOOLEAN | Step 3 complete |
| `cargo_seizure_complete` | BOOLEAN | Step 4 complete |
| `cargo_release_complete` | BOOLEAN | Step 5 complete |

### Status Transitions

```
draft → in_progress → completed
  │          │              │
  │          │              └── All 5 steps marked complete
  │          └── At least one step submitted
  └── Initial state
```

---

## API Interactions

### Form Submission APIs

| Endpoint | Method | Request | Response |
|----------|--------|---------|----------|
| `submit_voyage.php` | POST | FormData | `{success, message, voyage_id, containers_inserted}` |
| `submit_inspection.php` | POST | FormData | `{success, message, inspection_id}` |
| `submit_seizure.php` | POST | FormData | `{success, message, seizure_id}` |
| `submit_cargo_seizure.php` | POST | FormData | `{success, message, seizure_id}` |
| `submit_cargo_release.php` | POST | FormData | `{success, message, release_id}` |

### Data Retrieval APIs

| Endpoint | Method | Response |
|----------|--------|----------|
| `get_voyages.php` | GET | `{success, data: [{VoyageID, VoyageNo, VesselID, ArrivalDate, PortOfArrivalID}]}` |
| `get_ports.php` | GET | `{success, data: [{port_id, port_name, country}]}` |
| `get_vessels.php` | GET | `{success, data: [{vessel_id, vessel_name, vessel_type}]}` |
| `get_locations.php` | GET | `{success, data: [{location_id, location_name, region, location_type, is_active}]}` |
| `get_countries.php` | GET | `{success, data: [{CountryID, CountryName}]}` |
| `get_commodities.php` | GET | `{success, data: [{CommodityTypeID, CommodityType, CommodityGroupID, SortOrderNumber}]}` |
| `get_container_types.php` | GET | `{success, data: [{container_type_code, container_type_name, description}]}` |
| `get_recent_inspections.php` | GET | `{success, data: [...]}` |
| `get_recent_seizures.php` | GET | `{success, data: [...]}` |
| `get_recent_cargo_seizures.php` | GET | `{success, data: [...]}` |
| `get_recent_releases.php` | GET | `{success, data: [...]}` |

### Status Management API

**Endpoint:** `voyage_status.php`

**POST Actions:**
| Action | Parameters | Description |
|--------|------------|-------------|
| `start_step` | VoyageID, step | Mark step as in progress |
| `complete_step` | VoyageID, step | Mark step as complete |
| `update_status` | VoyageID, status | Update overall status |
| `reset_step` | VoyageID, step | Reset step completion |

**GET Request:**
```
voyage_status.php?id={VoyageID}
```
Returns current voyage status.

---

## JavaScript Integration

### Global Functions

#### Tab Navigation
```javascript
function openTab(evt, tabName) {
    // Hide all tab content
    // Remove active class from all tabs
    // Show selected tab
    // Trigger tab-specific initialization
}
```

#### Tab-Specific Triggers
When switching tabs, the system calls:
- `inspectionTab` → `checkActiveVoyage()`
- `seizureTab` → `checkActiveVoyageSeizure()`
- `cargoSeizureTab` → `checkActiveVoyageCargoSeizure()`
- `cargoReleaseTab` → `checkActiveVoyageRelease()`

### Edit Mode

When accessing `voyagement.php?voyage_id={id}`, the system enters edit mode:

```javascript
function initializeEditMode(voyageId) {
    window.currentVoyageId = voyageId;
    loadVoyageData(voyageId);  // Fetch and populate all forms
    loadVoyageStatus(voyageId); // Set appropriate tab and indicators
}
```

### Status Indicators

Completed steps show a green checkmark (✓) on their tab:

```javascript
function updateStatusIndicators(status) {
    // For each step:
    //   if step complete → add ✓ icon
    //   if current_step → highlight tab
}
```

### Message Display

All forms use a consistent message pattern:

```javascript
function showMessage(text, type) {
    messageDiv.textContent = text;
    messageDiv.className = 'message ' + type; // success, error, info
    messageDiv.style.display = 'block';

    // Auto-hide success after 5 seconds
    if (type === 'success') {
        setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
    }
}
```

---

## Audit Trail

### Recording Actions

Every state-changing operation logs to `voyage_audit_trail`:

```sql
INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
VALUES (:VoyageID, :Action, :ActionDetails, :PerformedBy)
```

### Logged Actions

| Action | Trigger | Details |
|--------|---------|---------|
| `complete_step` | Form submission | "Completed step: voyage_details" |
| `start_step` | Tab navigation | "Started step: passenger_inspection" |
| `reset_step` | Admin action | "Reset step: cargo_seizure" |
| `update_status` | Status change | "Status updated to: completed" |

### Audit Fields

| Field | Description |
|-------|-------------|
| `audit_id` | Auto-increment primary key |
| `VoyageID` | Associated voyage |
| `action` | Action type |
| `action_details` | Human-readable description |
| `performed_by` | Officer username/name |
| `performed_at` | Timestamp |
| `previous_values` | Before state (JSON) |
| `new_values` | After state (JSON) |

---

## Error Handling

### Client-Side

```javascript
fetch(url, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Success!', 'success');
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    });
```

### Server-Side

```php
try {
    // Validate CSRF
    // Validate required fields
    // Execute database operations
    // Return success response
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
```

---

## Summary

The BioSec Samoa workflow system provides a structured, auditable process for managing vessel voyages from arrival to cargo release. Key features include:

1. **Sequential 5-step process** ensuring all required data is captured
2. **Persistent context** via localStorage for seamless tab navigation
3. **Real-time compliance tracking** with visual indicators
4. **Comprehensive audit trail** for regulatory compliance
5. **Dynamic form elements** for flexible data entry
6. **Consistent error handling** across all steps

*This documentation was generated by analyzing the complete workflow implementation in `/home/ssuser/dev/sbsplatformdev/biosevv3/biosecsamoasys/`*
