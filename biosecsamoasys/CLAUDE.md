# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**biosecsamoasys** is a biosecurity system for managing vessel voyage details and port arrival information. The system tracks vessel information, crew, passengers, cargo, and biosecurity compliance data.

## Technology Stack

- **Backend**: PHP 8.x with PDO for database access
- **Web Server**: Lighttpd
- **Database**: MySQL 8.0
- **Containerization**: Docker (MySQL, Lighttpd, and PHP-FPM containers)
- **Frontend**: JavaScript, Bootstrap, custom CSS

## Development Workflow

### Branch Strategy
- Always create a new branch when implementing new features
- Test all changes thoroughly before merging
- Only merge to `main` after user confirmation that changes work correctly
- Always recheck and test all changes made

### Platform Restart Protocol
- **CRITICAL**: After making any changes to the platform, always restart it
- **CRITICAL**: Always reload the platform after changes because it uses ports that are already mapped
- Failure to restart can cause port conflicts and platform instability

### Database Port Configuration
- External port: 3307 (internal MySQL port: 3306)
- Web server external port: 8081 (internal: 80)
- These port mappings are fixed in docker-compose.yml

## Core Data Model

### Database Architecture (13 Core Tables)

**Primary Tables:**
1. **voyage_details** - Main voyage information and vessel data
2. **voyage_status** - Workflow state management (5-step process: VoyageDetails → Inspection → PassengerSeizure → CargoSeizure → CargoRelease)
3. **passenger_inspection** - Passenger compliance records
4. **passenger_seizure** - Seized items from passengers
5. **cargo_seizure** - Commercial cargo seizure records
6. **cargo_release** - Cargo release authorizations
7. **voyage_container_counts** - Links voyages to container types with count data
8. **voyage_audit_trail** - Tracks all changes for compliance

**Reference Tables:**
1. **ports** - Port registry with country associations
2. **vessels** - Vessel registry with type information
3. **locations** - Terminal and facility locations with region data
4. **container_types** - 19 cargo/container categories (loaded dynamically)
5. **commodity_groups** - 5 commodity categories
6. **commodity_types** - 29 specific commodity types
7. **users** - System user management

### Voyage Details (Main Table)
The primary entity with the following fields:
- **VoyageID** (INT, auto-increment primary key - NOT user input)
- VoyageNo (user input string)
- Port information: PortOfLoadingID, LastPortID, PortOfArrivalID, LocationID
- Dates: ArrivalDate, ModifiedDate
- People: Pax (passengers), Crew, CrewSearched
- Cargo: TotalDischarged, NumberContainers, NumberCargosDischarged
- Vessel: VesselID, AirOfSea
- Biosecurity: RoomSealed, NoXRated, BondedAnimals, BondedAnimalsDescription, AnimalHealthCertificate
- Compliance: PortAuthority
- Metadata: ModifiedBy, ModifiedDate

**Critical Note**: All form inputs (except VoyageID) are stored as VARCHAR/string type in the database, even numeric fields. The database schema is configured this way intentionally - do not change data types.

### Container Type Counts
The system tracks cargo/container types per voyage:
- **voyage_container_counts** table links voyages with cargo types
- Multiple cargo types can be associated with each voyage via foreign keys
- **19 cargo types**: Cars, Carsandparts, CarsandPersonalEffects, FrozenBeef, FrozenPork, FrozenChicken, FrozenLamb, FrozenTurky, FrozenVegies, FrozenFruits, FrozenMix, FreshBeef, FreshPork, FreshChicken, FreshLamb, FreshTurky, FreshVegies, FreshFruits, FreshMix
- Each cargo type count is stored separately with a foreign key to VoyageID
- Includes ModifiedByOfficerID field to track who entered the cargo data
- Cargo types are dynamically loaded on the form via JavaScript from container_types table

## Common Commands

### Docker Operations
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart after changes (REQUIRED)
docker-compose restart

# View logs
docker-compose logs -f

# Rebuild containers
docker-compose up -d --build
```

### Database Access
```bash
# Access MySQL container (PostgreSQL container doesn't exist - use MySQL)
docker exec -it biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db

# Run SQL scripts
docker exec -i biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db < script.sql

# Access with regular user
docker exec -it biosec_mysql mysql -u biosec_user -pbiosec_pass biosecurity_db
```

## Application Architecture

### API Layer (`/www/api/`)
RESTful API endpoints follow consistent patterns:
- **get_*.php** - Data retrieval endpoints (get_voyages.php, get_ports.php, get_locations.php, etc.)
- **submit_*.php** - Form submission handlers (submit_voyage.php, submit_inspection.php, etc.)
- **voyage_*.php** - Voyage management (voyage_crud.php, voyage_details.php, voyage_status.php)
- **config.php** - Database configuration and connection handler

**Database Connection Pattern:**
All APIs use the getDBConnection() function from config.php which returns a PDO object with utf8mb4 charset, exception mode enabled, and associative fetch mode.

### Multi-Step Workflow System
The application implements a 5-step voyage processing workflow:
1. **Voyage Details** (voyage_form.php)
2. **Passenger Inspection** (inspection_form.php)
3. **Passenger Seizure** (seizure_form.php)
4. **Cargo Seizure** (cargo_seizure_form.php)
5. **Cargo Release** (cargo_release_form.php)

The **voyagement.php** file acts as the container page that manages tab navigation and state persistence across all 5 steps. Each step submits to its corresponding API endpoint.

### JavaScript Integration Patterns
- **Form Loading**: JavaScript functions load previous form data when editing existing voyages
- **Dynamic Dropdowns**: Country, port, vessel, and location dropdowns are populated from API calls
- **Container Counts**: 19 cargo types are dynamically loaded and displayed with count input fields
- **Real-time Search**: Location management and voyage listings include debounced search functionality
- **Theme System**: Purple (default), Green, and Blue themes are applied via CSS class switching

### Form Validation Notes
- All form fields are validated on the client side before submission
- Server-side validation occurs in submit_*.php endpoints
- **Critical**: All data is stored as VARCHAR/string type, even for numeric fields (matches database schema)
- Form submissions use both POST parameters and JSON encoding where appropriate

## 🔐 Security & Compliance

- **CSRF Token**: Every POST request must include a `csrf_token` validated against `$_SESSION['csrf_token']`.
- **Authentication**: Use `requireAuth()` from `api/config.php` for protected pages and APIs.
- **Data Integrity**: All form inputs are stored as VARCHAR/string in the database except for primary keys.
- **Audit Logging**: Ensure all state-changing operations are logged to `voyage_audit_trail`.
