commi# Database Information - biosecsamoasys

## Database Overview

**Database Name**: biosecurity_db
**Database Engine**: MySQL 8.0
**Charset**: utf8mb4
**Port Configuration**: External port 3307 (internal port 3306)
**Connection**: PDO with utf8mb4 charset and exception mode enabled

## Database Architecture

The biosecsamoasys system consists of **13 core tables** organized into two categories:

### Primary Tables (8 tables)
- These tables store the main voyage workflow data and transactional records

### Reference Tables (5 tables)
- These tables store lookup data, master data, and configuration information

---

## Primary Tables

### 1. voyage_details
**Purpose**: Main voyage information and vessel data
**Primary Key**: VoyageID (INT, auto-increment)

#### Fields:
| Field Name | Type | Description | Required |
|------------|------|-------------|----------|
| VoyageID | INT (AI) | Primary key (NOT user input) | Auto |
| VoyageNo | VARCHAR | Voyage number (user input string) | Yes |
| PortOfLoadingID | VARCHAR | Loading port reference | Yes |
| LastPortID | VARCHAR | Last port of call | Yes |
| PortOfArrivalID | VARCHAR | Arrival port reference | Yes |
| LocationID | VARCHAR | Terminal/facility location | Yes |
| ArrivalDate | VARCHAR | Date of arrival | Yes |
| ModifiedDate | VARCHAR | Last modification date | Yes |
| Pax | VARCHAR | Number of passengers | Yes |
| Crew | VARCHAR | Number of crew members | Yes |
| CrewSearched | VARCHAR | Number of crew searched | Yes |
| TotalDischarged | VARCHAR | Total cargo discharged | Yes |
| NumberContainers | VARCHAR | Number of containers | Yes |
| NumberCargosDischarged | VARCHAR | Number of cargo items discharged | Yes |
| VesselID | VARCHAR | Vessel reference | Yes |
| AirOfSea | VARCHAR | Air or sea transport indicator | Yes |
| RoomSealed | VARCHAR | Room sealed status | Yes |
| NoXRated | VARCHAR | X-rated material status | Yes |
| BondedAnimals | VARCHAR | Bonded animals indicator | Yes |
| BondedAnimalsDescription | VARCHAR | Description of bonded animals | No |
| AnimalHealthCertificate | VARCHAR | Animal health certificate status | Yes |
| PortAuthority | VARCHAR | Port authority compliance | Yes |
| ModifiedBy | VARCHAR | User who last modified | Yes |

**Critical Note**: All form inputs (except VoyageID) are stored as VARCHAR/string type in the database, even numeric fields. This is intentional and should not be changed.

### 2. voyage_status
**Purpose**: Workflow state management
**Workflow**: 5-step process
- Step 1: VoyageDetails
- Step 2: Inspection
- Step 3: PassengerSeizure
- Step 4: CargoSeizure
- Step 5: CargoRelease

#### Key Features:
- Tracks current step in the workflow
- Manages state transitions between steps
- Ensures proper sequencing of voyage processing

### 3. passenger_inspection
**Purpose**: Passenger compliance records
**Relationship**: Linked to voyage_details via VoyageID

#### Key Features:
- Records passenger inspection results
- Tracks compliance status
- Links to specific voyage

### 4. passenger_seizure
**Purpose**: Seized items from passengers
**Relationship**: Linked to voyage_details via VoyageID

#### Key Features:
- Records items seized from passengers
- Documentation for regulatory compliance
- Links to inspection records

### 5. cargo_seizure
**Purpose**: Commercial cargo seizure records
**Relationship**: Linked to voyage_details via VoyageID

#### Key Features:
- Records cargo seizures
- Commercial compliance tracking
- Regulatory documentation

### 6. cargo_release
**Purpose**: Cargo release authorizations
**Relationship**: Linked to voyage_details via VoyageID

#### Key Features:
- Manages cargo release approvals
- Final step in cargo workflow
- Compliance documentation

### 7. voyage_container_counts
**Purpose**: Links voyages to container types with count data
**Relationship**: Linked to voyage_details via VoyageID

#### Key Features:
- Tracks multiple cargo types per voyage
- Links to container_types table
- Stores ModifiedByOfficerID for audit trail
- Supports 19 different cargo/container categories

#### 19 Cargo Types Supported:
1. Cars
2. Carsandparts
3. CarsandPersonalEffects
4. FrozenBeef
5. FrozenPork
6. FrozenChicken
7. FrozenLamb
8. FrozenTurky
9. FrozenVegies
10. FrozenFruits
11. FrozenMix
12. FreshBeef
13. FreshPork
14. FreshChicken
15. FreshLamb
16. FreshTurky
17. FreshVegies
18. FreshFruits
19. FreshMix

### 8. voyage_audit_trail
**Purpose**: Tracks all changes for compliance
**Relationship**: Linked to voyage_details via VoyageID

#### Key Features:
- Complete audit trail of all changes
- Regulatory compliance requirement
- User activity tracking
- Change history preservation

---

## Reference Tables

### 1. ports
**Purpose**: Port registry with country associations
**Key Features**:
- Master list of all ports
- Country associations
- Port identification data

### 2. vessels
**Purpose**: Vessel registry with type information
**Key Features**:
- Master vessel data
- Vessel type classifications
- Vessel identification

### 3. locations
**Purpose**: Terminal and facility locations with region data
**Key Features**:
- Terminal and facility listings
- Regional associations
- Location categorization

### 4. container_types
**Purpose**: 19 cargo/container categories
**Key Features**:
- Dynamically loaded in forms via JavaScript
- Reference for voyage_container_counts
- Category definitions

### 5. commodity_groups
**Purpose**: 5 commodity categories
**Key Features**:
- High-level commodity grouping
- Category classification
- Reference for commodity types

### 6. commodity_types
**Purpose**: 29 specific commodity types
**Key Features**:
- Detailed commodity classification
- Links to commodity groups
- Specific commodity definitions

### 7. users
**Purpose**: System user management
**Key Features**:
- User authentication
- Role-based access
- User activity tracking

---

## Database Relationships

### Primary Relationships:
- **voyage_details** → All workflow tables (via VoyageID)
- **voyage_details** → **voyage_container_counts** (one-to-many)
- **voyage_container_counts** → **container_types** (via container type foreign key)
- **voyage_details** → **ports** (PortOfLoadingID, LastPortID, PortOfArrivalID)
- **voyage_details** → **locations** (LocationID)
- **voyage_details** → **vessels** (VesselID)

### Workflow Flow:
```
voyage_details → voyage_status → passenger_inspection
                                      ↓
cargo_release ← cargo_seizure ← passenger_seizure
     ↑              ↑
voyage_container_counts (links to container_types)
```

---

## Data Type Conventions

### Critical Data Type Rule:
**All form inputs (except VoyageID) are stored as VARCHAR/string type in the database, even numeric fields.**

This includes:
- Passenger counts (Pax, Crew, CrewSearched)
- Cargo quantities (TotalDischarged, NumberContainers, NumberCargosDischarged)
- Date fields (ArrivalDate, ModifiedDate)
- All identifier fields (PortOfLoadingID, LastPortID, etc.)

**Reason**: The database schema is intentionally configured this way. Do not change data types.

---

## Database Access

### Connection Parameters:
- **Host**: localhost
- **Port**: 3307 (external) / 3306 (internal)
- **Database**: biosecurity_db
- **Charset**: utf8mb4
- **Connection Method**: PDO

### Example Connection String:
```php
$pdo = new PDO("mysql:host=localhost;port=3307;dbname=biosecurity_db;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
```

### Access Commands:
```bash
# MySQL container access
docker exec -it biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db

# Regular user access
docker exec -it biosec_mysql mysql -u biosec_user -pbiosec_pass biosecurity_db

# Run SQL scripts
docker exec -i biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db < script.sql
```

---

## API Integration

All database operations are handled through the API layer located in `/www/api/`:

### API Patterns:
- **get_*.php** - Data retrieval endpoints
  - get_voyages.php
  - get_ports.php
  - get_locations.php
  - etc.

- **submit_*.php** - Form submission handlers
  - submit_voyage.php
  - submit_inspection.php
  - etc.

- **voyage_*.php** - Voyage management
  - voyage_crud.php
  - voyage_details.php
  - voyage_status.php

- **config.php** - Database configuration and connection handler

### Database Connection Function:
```php
function getDBConnection() {
    // Returns PDO object with utf8mb4 charset
    // Exception mode enabled
    // Associative fetch mode
}
```

---

## Multi-Step Workflow System

The database supports a 5-step voyage processing workflow:

1. **Voyage Details** (voyage_form.php)
2. **Passenger Inspection** (inspection_form.php)
3. **Passenger Seizure** (seizure_form.php)
4. **Cargo Seizure** (cargo_seizure_form.php)
5. **Cargo Release** (cargo_release_form.php)

Each step:
- Stores data in its respective table
- Updates voyage_status table
- Maintains audit trail in voyage_audit_trail
- Links back to voyage_details via VoyageID

---

## Security and Compliance

### Audit Trail:
- All changes tracked in voyage_audit_trail
- User activity recorded
- Change history preserved

### Data Integrity:
- Foreign key relationships maintained
- Referential integrity enforced
- VARCHAR data type for all inputs ensures data consistency

### Compliance:
- Regulatory requirements met through audit trail
- Port authority compliance tracking
- Biosecurity compliance documentation

---

## Best Practices

1. **Always use VARCHAR for form inputs** - Database schema requirement
2. **Maintain audit trail** - All changes must be logged
3. **Use PDO for database access** - Security and performance
4. **Follow workflow sequencing** - 5-step process must be maintained
5. **Validate data before submission** - Client and server-side validation
6. **Use prepared statements** - SQL injection prevention
7. **Handle exceptions properly** - Error handling and logging
8. **Test all changes** - Ensure data integrity and workflow compliance

---

## Port Configuration

- **Database Port**: 3307 (external) / 3306 (internal)
- **Web Server Port**: 8081 (external) / 80 (internal)

**Important**: Always restart the platform after making database schema changes to avoid port conflicts.

---

*Last Updated: 2026-02-08*
*System: biosecsamoasys v1.1-security-ready*