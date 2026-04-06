# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**biosecsamoasys** is a biosecurity system for managing vessel voyage details and port arrival information. The system tracks vessel information, crew, passengers, cargo, and biosecurity compliance data for the Samoa Biosecurity Authority.

## Technology Stack

- **Backend**: PHP 8.1-FPM with PDO for database access (no framework - vanilla PHP)
- **Web Server**: Lighttpd with FastCGI routing to PHP-FPM on port 9000
- **Database**: MySQL 8.0 with utf8mb4 charset
- **Containerization**: Docker Compose (3 services: MySQL, Lighttpd, PHP-FPM on `biosec_network`)
- **Frontend**: Vanilla JavaScript (ES6+), Bootstrap CSS, custom themes (no build tools or package managers)
- **Testing**: No formal testing framework - manual testing via UI is the primary validation method

## Development Workflow

### Branch Strategy
- `master` is the main branch
- Always create a new branch when implementing new features
- Test all changes thoroughly before merging
- Only merge to `master` after user confirmation that changes work correctly

### Platform Restart Protocol
- **CRITICAL**: After making any changes, always restart Docker containers
- **CRITICAL**: Always reload the platform because it uses ports that are already mapped
- Failure to restart can cause port conflicts and platform instability
- Use `docker-compose restart` for code/logic changes, or `docker-compose up -d --build` if Dockerfile or docker-compose.yml changed

### Port Configuration
- MySQL external port: `3307` (internal: `3306`)
- Lighttpd external port: `8081` (internal: `80`)
- PHP-FPM port: `9000` (internal only, not exposed externally)
- All port mappings are fixed in docker-compose.yml

### Working Directory
All Docker commands should run from this directory: `/home/ssuser/dev/sbsplatformdev/biosevv3/biosecsamoasys/`

## Common Commands

### Docker Operations
```bash
# Navigate to project directory
cd /home/ssuser/dev/sbsplatformdev/biosevv3/biosecsamoasys/

# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart after code changes (REQUIRED)
docker-compose restart

# View logs (all containers)
docker-compose logs -f

# View logs (specific container)
docker-compose logs -f php
docker-compose logs -f lighttpd
docker-compose logs -f mysql

# Rebuild containers (after Dockerfile or docker-compose.yml changes)
docker-compose up -d --build
```

### Database Access
```bash
# Access MySQL as root
docker exec -it biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db

# Access MySQL as regular user
docker exec -it biosec_mysql mysql -u biosec_user -pbiosec_pass biosecurity_db

# Run SQL migration scripts
docker exec -i biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db < sql/migration.sql

# Useful debugging queries
SELECT * FROM voyage_details ORDER BY ModifiedDate DESC LIMIT 10;
SELECT * FROM voyage_status ORDER BY (SELECT MAX(ModifiedDate) FROM voyage_details) DESC LIMIT 10;
SELECT * FROM voyage_audit_trail ORDER BY changed_at DESC LIMIT 20;
```

## Core Data Model

### Database Architecture (13+ Core Tables)

**Primary Tables:**
1. **voyage_details** - Main voyage information and vessel data (VoyageID is auto-increment PK)
2. **voyage_status** - Workflow state tracking (5-step workflow boolean flags)
3. **passenger_inspection** - Passenger compliance records
4. **passenger_seizure** - Seized items from passengers
5. **cargo_seizure** - Commercial cargo seizure records
6. **cargo_release** - Cargo release authorizations
7. **voyage_container_counts** - Links voyages to container types with count data (one row per cargo type per voyage)
8. **voyage_audit_trail** - Full change tracking with before/after JSON values

**Reference Tables:**
- `ports` - Port registry with country associations
- `vessels` - Vessel registry with type information
- `locations` - Terminal and facility locations with region data
- `container_types` - 19 cargo/container categories (loaded dynamically on forms)
- `commodity_groups` - 5 commodity categories
- `commodity_types` - 29 specific commodity types
- `users` - System users with role-based access control

### Voyage Details (Main Table)
- **VoyageID** (INT, auto-increment PK - NOT user input)
- VoyageNo (user input string)
- Port IDs: PortOfLoadingID, LastPortID, PortOfArrivalID, LocationID
- Dates: ArrivalDate, ModifiedDate
- People: Pax (passengers), Crew, CrewSearched
- Cargo: TotalDischarged, NumberContainers, NumberCargosDischarged
- Vessel: VesselID, AirOfSea
- Biosecurity: RoomSealed, NoXRated, BondedAnimals, BondedAnimalsDescription, AnimalHealthCertificate
- Compliance: PortAuthority
- Metadata: ModifiedBy, ModifiedDate

**Critical**: All form inputs (except VoyageID) are stored as VARCHAR/string type in the database, even numeric fields. Do not change data types.

### Container Type Counts
- Each voyage has up to 19 rows in `voyage_container_counts` (one row per cargo type)
- **19 cargo types**: Cars, Carsandparts, CarsandPersonalEffects, FrozenBeef, FrozenPork, FrozenChicken, FrozenLamb, FrozenTurky, FrozenVegies, FrozenFruits, FrozenMix, FreshBeef, FreshPork, FreshChicken, FreshLamb, FreshTurky, FreshVegies, FreshFruits, FreshMix
- Cargo types are dynamically loaded from `container_types` table via JavaScript

### Role-Based Access Control
- **viewer** (level 0) - Read-only access
- **officer** (level 1) - Standard CRUD operations
- **admin** (level 2) - Full system access including user management
- Role hierarchy enforced via `requireAuth()` with numeric comparison

## Application Architecture

### API Layer (`/www/api/`)
RESTful API endpoints with consistent naming:
- **get_*.php** - Data retrieval (GET requests): get_voyages.php, get_ports.php, get_locations.php, etc.
- **submit_*.php** - Form submission (POST requests): submit_voyage.php, submit_inspection.php, submit_seizure.php, etc.
- **voyage_*.php** - Voyage management: voyage_crud.php, voyage_details.php, voyage_status.php
- **CRUD endpoints**: create_port.php, update_port.php, delete_port.php, create_user.php, etc.
- **Utility**: auth_check.php, logout.php, generate_seizure_number.php

**Database Connection Pattern:**
```php
require_once __DIR__ . '/config.php';
$conn = getDBConnection();
// Returns PDO with: utf8mb4 charset, exception mode, FETCH_ASSOC
```

**Response Format:**
```php
echo json_encode(['success' => true/false, 'message' => '...', 'data' => [...]]);
```

**config.php** contains `getDBConnection()`, `isLoggedIn()`, `requireAuth()`, `getCurrentUser()`, and `logout()` functions. DB credentials are currently hardcoded (not env vars).

### Multi-Step Workflow System
5-step voyage processing workflow managed by **voyagement.php** (tab-based container):
1. **Voyage Details** (voyage_form.php + script.js)
2. **Passenger Inspection** (inspection_form.php + inspection_script.js)
3. **Passenger Seizure** (seizure_form.php + seizure_script.js)
4. **Cargo Seizure** (cargo_seizure_form.php + cargo_seizure_script.js)
5. **Cargo Release** (cargo_release_form.php + cargo_release_script.js)

Each form file has a corresponding `*_script.js` file. State persisted via `localStorage` keys: `activeVoyageID`, `activeVoyageNo`, `activeVesselID`.

**Important**: `voyagement.php` is the multi-step workflow container (~53KB, largest file). `voyage_management.php` is the voyage list/search page. These are different files.

### JavaScript Conventions
- Each form has a dedicated script file: `{form_name}_script.js`
- No build process - JS served directly, no bundling or transpilation
- AJAX calls to `api/*.php` endpoints for data loading and form submission
- Dynamic dropdowns populated from `get_*.php` API endpoints
- Debounced search on location management and voyage listings
- Theme system: Purple (default), Green, Blue via CSS class switching

### PHP Conventions
- No framework - plain procedural PHP
- PDO prepared statements for all database operations (SQL injection prevention)
- Try-catch error handling with PDO exceptions
- Session-based authentication with `$_SESSION`
- CSRF token validation on all POST state-changing endpoints
- Audit logging via `voyage_audit_trail` with before/after JSON values
- JSON responses from API endpoints

### Form Submission Patterns
- POST method with CSRF token
- Form-encoded POST parameters
- Server-side validation in submit_*.php
- JSON response for client-side feedback
- **Critical**: All data stored as VARCHAR/string type (matches DB schema)

## Other Pages
- `index.php` - Main dashboard with stats and activity feeds
- `voyage_management.php` - Voyage list, search, and filtering
- `location_management.php` - Port/location CRUD admin
- `user_management.php` - User CRUD with RBAC
- `login.php` - User authentication
- `import_permits.php` / `print_permit.php` - Import permit management
- `unified_seizure.php` - Seizure overview dashboard
- `settings.php` - User preferences and theming
- `port_of_entry_management.php` - Port entry management

## Seizure Number Generation
Auto-generated via `generate_seizure_number.php` with formats like `PS-2024-001` (passenger seizure), `CS-2024-001` (cargo seizure).

## Migration Strategy
SQL migration scripts in `sql/` directory add tables and modify schema incrementally. Key migrations:
- `init.sql` - Initial database setup with seed data
- `add_voyage_management_tables.sql` - Additional voyage tables
- `add_comprehensive_locations.sql` - Enhanced location schema
- `migrate_varchar_to_int.sql` - Type migration
- `create_missing_status_records.sql` - Status cleanup

## Security & Compliance

- **CSRF Token**: Every POST request must include `csrf_token` validated against `$_SESSION['csrf_token']`
- **Authentication**: Use `requireAuth()` from `api/config.php` for protected pages and APIs
- **Data Integrity**: All form inputs stored as VARCHAR/string except primary keys
- **Audit Logging**: All state-changing operations logged to `voyage_audit_trail`
- **Known Security Gaps**: Some API endpoints lack authentication middleware; DB credentials hardcoded in config.php; missing secure cookie flags. Security patches exist in `/SECURITY_PATCHES/` directory

## Project Structure Notes

```
biosecsamoasys/
├── www/                          # Web application root (Lighttpd document root)
│   ├── api/                      # REST API endpoints (40+ files)
│   │   ├── config.php           # DB connection + auth helpers
│   │   ├── get_*.php            # Data retrieval endpoints
│   │   ├── submit_*.php         # Form submission handlers
│   │   └── voyage_*.php         # Voyage management
│   ├── *.php                    # Main application pages
│   ├── *_script.js              # Page-specific JavaScript
│   └── styles.css               # Global styles + themes
├── sql/                          # Database initialization and migrations
├── lighttpd/                     # Lighttpd config (lighttpd.conf)
├── SECURITY_PATCHES/             # Numbered security remediation scripts
├── database/                     # Reference schema (schema.sql)
├── ImageUpload/                  # Image upload handling
├── docker-compose.yml            # Container orchestration
└── Dockerfile.php                # PHP 8.1-FPM container build
```

## Key Implementation Notes

- **No CI/CD pipeline** - no automated testing, linting, or CI. All validation is manual via the web interface
- **No build process** - JavaScript and CSS served directly without minification or bundling
- **Container counts storage** - Each voyage has 19 separate rows in `voyage_container_counts` (not 19 columns)
- **Image uploads** stored in `imageuploads/` at the repository root level (`/home/ssuser/dev/sbsplatformdev/biosevv3/imageuploads/`)
- **Lighttpd config** routes `.php` requests to PHP-FPM on port 9000 via FastCGI module
