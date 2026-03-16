# BioSec Samoa System - Codebase Documentation

**Repository:** https://github.com/sweetrush/biosevv3
**Last Updated:** 2026-03-16

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Project Structure](#project-structure)
4. [Database Architecture](#database-architecture)
5. [Application Architecture](#application-architecture)
6. [Authentication & Security](#authentication--security)
7. [API Layer](#api-layer)
8. [Frontend Components](#frontend-components)
9. [Deployment](#deployment)
10. [Security Assessment Summary](#security-assessment-summary)

---

## System Overview

**BioSec Samoa** is a comprehensive biosecurity management system designed for the Samoa Biosecurity Authority. It manages maritime biosecurity operations at Samoan ports, providing end-to-end tracking of:

- Vessel voyages and arrivals
- Passenger inspections and compliance
- Cargo seizures and releases
- Port and location management
- Officer activity and audit trails

### Core Purpose

The system protects Samoa's borders from biosecurity threats by enabling officers to:
1. Record vessel arrival details
2. Inspect passengers and their goods
3. Seize prohibited or non-compliant items
4. Process cargo releases
5. Track compliance across all operations

---

## Technology Stack

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| **Backend** | PHP | 8.x | Server-side logic, API endpoints |
| **Database** | MySQL | 8.0 | Data persistence |
| **Web Server** | Lighttpd | Latest | HTTP request handling |
| **Frontend** | HTML5/CSS3/JavaScript | ES6+ | User interface |
| **Containerization** | Docker Compose | 3.8 | Service orchestration |
| **Authentication** | Session-based | - | User management |
| **Styling** | Bootstrap + Custom CSS | 5.x | Responsive design |

---

## Project Structure

```
biosecsamoasys/
├── www/                              # Web application root
│   ├── api/                          # REST API endpoints
│   │   ├── config.php               # Database & auth configuration
│   │   ├── auth_check.php           # Authentication middleware
│   │   ├── voyage_*.php             # Voyage management APIs
│   │   │   ├── voyage_crud.php      # Create, Read, Update, Delete
│   │   │   ├── voyage_details.php   # Get voyage details
│   │   │   └── voyage_status.php    # Workflow status management
│   │   ├── submit_*.php             # Form submission handlers
│   │   │   ├── submit_voyage.php    # New voyage creation
│   │   │   ├── submit_inspection.php
│   │   │   ├── submit_seizure.php
│   │   │   ├── submit_cargo_seizure.php
│   │   │   └── submit_cargo_release.php
│   │   ├── get_*.php                # Data retrieval endpoints
│   │   │   ├── get_voyages.php      # List voyages
│   │   │   ├── get_ports.php        # Port registry
│   │   │   ├── get_vessels.php      # Vessel registry
│   │   │   ├── get_locations.php    # Location data
│   │   │   └── get_recent_*.php     # Activity feeds
│   │   └── logout.php               # Session termination
│   ├── js/
│   │   └── form-utils.js            # Shared JavaScript utilities
│   ├── *.php                        # Main application pages
│   │   ├── login.php                # Authentication page
│   │   ├── index.php                # Main dashboard
│   │   ├── voyagement.php           # Multi-step workflow container
│   │   ├── voyage_management.php    # Voyage list/search
│   │   ├── voyage_form.php          # Voyage data entry (Step 1)
│   │   ├── inspection_form.php      # Passenger inspection (Step 2)
│   │   ├── seizure_form.php         # Passenger seizure (Step 3)
│   │   ├── cargo_seizure_form.php   # Cargo seizure (Step 4)
│   │   ├── cargo_release_form.php   # Cargo release (Step 5)
│   │   ├── location_management.php  # Port/location admin
│   │   ├── user_management.php      # User administration
│   │   ├── import_permits.php       # Permit management
│   │   ├── unified_seizure.php      # Seizure overview
│   │   └── settings.php             # User preferences
│   ├── *.js                         # Page-specific JavaScript
│   └── styles.css                   # Global styles
├── sql/                              # Database scripts
│   ├── init.sql                     # Complete database initialization
│   ├── add_comprehensive_locations.sql
│   ├── add_voyage_management_tables.sql
│   ├── create_countries_table.sql
│   └── create_missing_status_records.sql
├── lighttpd/
│   └── lighttpd.conf                # Web server configuration
├── database/
│   └── schema.sql                   # Database schema reference
├── SECURITY_PATCHES/                 # Security remediation scripts
│   ├── 01_fix_api_authentication.php
│   ├── 02_apply_auth_to_endpoints.php
│   ├── 03_environment_variables.php
│   ├── 04_session_security.php
│   └── 05_csrf_protection.php
├── docker-compose.yml                # Container orchestration
├── Dockerfile.php                    # PHP container build
└── *.md                             # Documentation files
```

---

## Database Architecture

### 13 Core Tables

#### Primary Tables

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `voyage_details` | Main voyage records | VoyageID (PK), VoyageNo, VesselID, ArrivalDate, PortOfArrivalID |
| `voyage_status` | Workflow state tracking | status_id (PK), VoyageID (FK), current_step, status |
| `passenger_inspection` | Passenger compliance | PassengerInspectionID (PK), VoyageID (FK), CommodityTypeID |
| `passenger_seizure` | Seized passenger items | PassengerSeizureID (PK), VoyageID (FK), ItemDescription |
| `cargo_seizure` | Commercial cargo seizures | CargoSeizureID (PK), VoyageID (FK), ContainerCargoRefNo |
| `cargo_release` | Cargo release authorizations | CargoReleaseID (PK), VoyageID (FK), ReleaseNo |
| `voyage_container_counts` | Container type counts per voyage | id (PK), VoyageID (FK), container_type_code |
| `voyage_audit_trail` | Change tracking | audit_id (PK), VoyageID (FK), action, performed_by |

#### Reference Tables

| Table | Purpose | Records |
|-------|---------|---------|
| `ports` | Port registry | 3 sample ports (Apia, Pago Pago, Nuku'alofa) |
| `vessels` | Vessel registry | 3 sample vessels |
| `locations` | Terminal/facility locations | 3 sample locations |
| `container_types` | 19 cargo categories | Cars, Frozen/Fresh products, etc. |
| `commodity_groups` | 5 commodity categories | Animals, Plants, MEV, Other, Pesticides |
| `commodity_types` | 29 specific commodities | Live animals, Beef, Vegetables, etc. |
| `users` | System users | Admin + Officer accounts |

### Key Design Decisions

1. **All form inputs stored as VARCHAR/string** - Even numeric fields use VARCHAR for consistency
2. **Auto-increment VoyageID** - Never user-input, always database-generated
3. **Foreign key constraints** - Cascading deletes on voyage_details
4. **Timestamps** - `created_at` and `updated_at` on all tables

### Entity Relationships

```
voyage_details (1) -----> (N) voyage_container_counts
voyage_details (1) -----> (N) passenger_inspection
voyage_details (1) -----> (N) passenger_seizure
voyage_details (1) -----> (N) cargo_seizure
voyage_details (1) -----> (N) cargo_release
voyage_details (1) -----> (1) voyage_status
voyage_details (1) -----> (N) voyage_audit_trail

container_types (1) -----> (N) voyage_container_counts
commodity_types (1) -----> (N) passenger_inspection
ports (1) ---------------> (N) voyage_details (via PortOfArrivalID)
vessels (1) -------------> (N) voyage_details (via VesselID)
```

---

## Application Architecture

### Request Flow

```
Browser Request
    |
    v
Lighttpd (Port 8081)
    |
    v
PHP Processing
    |
    ├── Page Request (index.php, voyagement.php, etc.)
    │       |
    │       ├── auth_check.php (session validation)
    │       ├── HTML/CSS/JS rendering
    │       └── API calls via JavaScript fetch()
    │
    └── API Request (api/*.php)
            |
            ├── CSRF validation (POST requests)
            ├── Database query via PDO
            └── JSON response
    |
    v
MySQL (Port 3307)
```

### Multi-Step Workflow

The system implements a 5-step voyage processing workflow managed by `voyagement.php`:

```
Step 1: Voyage Details (voyage_form.php)
    |
    v
Step 2: Passenger Inspection (inspection_form.php)
    |
    v
Step 3: Passenger Seizure (seizure_form.php)
    |
    v
Step 4: Cargo Seizure (cargo_seizure_form.php)
    |
    v
Step 5: Cargo Release (cargo_release_form.php)
    |
    v
Completed
```

Each step:
- Validates user input (client and server-side)
- Submits data via POST to corresponding API endpoint
- Updates `voyage_status` table via `voyage_status.php`
- Records action in `voyage_audit_trail`

### State Management

The `voyage_status` table tracks workflow state:

| Field | Purpose |
|-------|---------|
| `current_step` | Which step the user is on |
| `status` | draft, in_progress, completed, archived |
| `voyage_details_complete` | Boolean flag |
| `passenger_inspection_complete` | Boolean flag |
| `passenger_seizure_complete` | Boolean flag |
| `cargo_seizure_complete` | Boolean flag |
| `cargo_release_complete` | Boolean flag |

---

## Authentication & Security

### Authentication Flow

```
User Login (login.php)
    |
    ├── Validates username/password against users table
    ├── Uses password_verify() for hash comparison
    ├── Sets session variables (user_id, username, access_level, etc.)
    └── Regenerates session ID for security
    |
    v
Session-Based Auth
    |
    ├── auth_check.php - Protects page routes
    ├── config.php - isLoggedIn(), requireAuth(), getCurrentUser()
    └── Session variables checked on each request
```

### Role-Based Access Control

| Role | Level | Permissions |
|------|-------|-------------|
| `viewer` | 0 | Read-only access |
| `officer` | 1 | Standard operations (CRUD) |
| `admin` | 2 | Full system access + user management |

### Security Measures (Implemented)

1. **Password Hashing** - `password_hash()` with bcrypt
2. **Session Regeneration** - `session_regenerate_id(true)` on login
3. **CSRF Protection** - Token validation on POST requests
4. **PDO Prepared Statements** - SQL injection prevention
5. **Output Encoding** - `htmlspecialchars()` on user output
6. **Input Validation** - Required field checks

### Security Measures (In Progress)

Per `SECURITY_ASSESSMENT_SUMMARY.md`:

- [ ] Authentication on all API endpoints (25+ currently unprotected)
- [ ] Session hardening (secure cookies, timeouts)
- [ ] Environment variable migration for credentials
- [ ] Complete CSRF protection on all mutation endpoints

---

## API Layer

### Configuration (`config.php`)

Central configuration provides:

```php
// Database constants
DB_HOST = 'biosec_mysql'
DB_NAME = 'biosecurity_db'
DB_USER = 'biosec_user'
DB_PASS = 'biosec_pass'

// Key functions
getDBConnection()    // Returns PDO object
isLoggedIn()         // Checks $_SESSION['user_id']
requireAuth($role)   // Validates role hierarchy
getCurrentUser()     // Returns user session data
logout()             // Clears session
```

### API Endpoint Patterns

**Data Retrieval (GET):**
```
get_voyages.php      - List all voyages
get_ports.php        - List ports
get_vessels.php      - List vessels
get_locations.php    - List locations
get_commodities.php  - List commodity types
get_recent_*.php     - Activity feeds
```

**Form Submission (POST):**
```
submit_voyage.php        - Create new voyage
submit_inspection.php    - Record inspection
submit_seizure.php       - Record passenger seizure
submit_cargo_seizure.php - Record cargo seizure
submit_cargo_release.php - Record cargo release
```

**Status Management (POST/GET):**
```
voyage_status.php    - Update workflow state
voyage_crud.php      - Voyage CRUD operations
voyage_details.php   - Get single voyage
```

### Response Format

All API endpoints return JSON:

```json
{
  "success": true|false,
  "message": "Description of result",
  "data": { ... },
  "voyage_id": 123
}
```

---

## Frontend Components

### Theme System

Four themes available via `settings.php` and localStorage:

| Theme | CSS Class | Color Scheme |
|-------|-----------|--------------|
| Purple (Default) | `theme-purple` | #667eea → #764ba2 |
| Green | `theme-green` | #27ae60 → #229954 |
| Blue | `theme-blue` | #3498db → #2980b9 |
| Office | `theme-office` | #6c757d → #495057 |

### Page Layout Structure

```
┌─────────────────────────────────────────────┐
│ Sidebar (250px)          │ Main Content     │
│                          │                  │
│ [Logo]                   │ Welcome Header   │
│ [User Profile]           │                  │
│                          │ Stats Grid       │
│ Navigation:              │                  │
│  - Dashboard             │ Quick Actions    │
│  - Voyage Management     │                  │
│  - Location Management   │ Content Area     │
│  - Seizure Management    │                  │
│  - Settings              │                  │
│                          │                  │
│ Permits:                 │                  │
│  - Import Permits        │                  │
│  - Export Certificates   │                  │
│                          │                  │
│ Resources:               │                  │
│  - Documentation         │                  │
│  - Help                  │                  │
│                          │                  │
│ [Logout]                 │                  │
└─────────────────────────────────────────────┘
```

### JavaScript Patterns

1. **Dynamic Dropdowns** - Populated via fetch() from get_* APIs
2. **Container Count Inputs** - 19 cargo types loaded dynamically
3. **Theme Switching** - localStorage persistence
4. **Mobile Menu** - Hamburger toggle for responsive design
5. **Form Validation** - Client-side before submission
6. **Debounced Search** - Real-time filtering on lists

---

## Deployment

### Docker Services

| Service | Container | Port Mapping | Purpose |
|---------|-----------|--------------|---------|
| MySQL | biosec_mysql | 3307:3306 | Database server |
| Lighttpd | biosec_lighttpd | 8081:80 | Web server |
| PHP | biosec_php | Shared volume | PHP-FPM processing |

### Starting the Application

```bash
# Start all services
docker-compose up -d

# Access the application
# Web: http://localhost:8081
# DB: mysql -h localhost -P 3307 -u biosec_user -p

# Stop services
docker-compose down

# Restart after changes (REQUIRED)
docker-compose restart

# Rebuild containers
docker-compose up -d --build
```

### Default Credentials

| Account | Username | Password | Role |
|---------|----------|----------|------|
| Admin | admin | (set in DB) | admin |
| Officer | bio_officer | (set in DB) | officer |

### Environment Variables

Currently hardcoded in:
- `config.php` - Database credentials
- `docker-compose.yml` - MySQL root password

**Note:** Security assessment recommends migrating to environment variables.

---

## Security Assessment Summary

**Assessment Date:** November 11, 2024
**Overall Risk Level:** CRITICAL (5.1/10 improving)

### Vulnerability Breakdown

| Category | Critical | High | Medium | Low | Total |
|----------|----------|------|--------|-----|-------|
| Authentication | 12 | 5 | 0 | 0 | 22 |
| Session Management | 0 | 3 | 2 | 1 | 8 |
| SQL Injection | 0 | 1 | 2 | 0 | 4 |
| XSS Prevention | 0 | 0 | 2 | 2 | 4 |
| CSRF Protection | 0 | 5 | 1 | 0 | 7 |
| Input Validation | 0 | 0 | 6 | 0 | 6 |
| Infrastructure | 0 | 2 | 2 | 4 | 10 |

### Key Findings

1. **25+ API endpoints lack authentication** - Complete data exposure risk
2. **CSRF tokens missing on 5 mutation endpoints** - State change vulnerability
3. **Session cookies not secured** - No httponly/secure flags
4. **Hardcoded database credentials** - Should use environment variables

### Remediation Status

| Item | Status |
|------|--------|
| CSRF Protection on core endpoints | Complete |
| Authentication middleware created | Complete |
| Apply auth to all APIs | In Progress |
| Session hardening | In Progress |
| Environment variable migration | Pending |

---

## Development Guidelines

### Branch Strategy

1. Create feature branch from `main`
2. Implement and test changes
3. Get user confirmation
4. Merge to `main`

### Critical Rules

- **ALWAYS** restart Docker after code changes
- **ALWAYS** test before merging
- **NEVER** skip platform restart

### Common Tasks

```bash
# Access MySQL
docker exec -it biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db

# Run SQL script
docker exec -i biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db < script.sql

# View logs
docker-compose logs -f
```

---

## File Reference

### Key Files and Their Line Counts

| File | Lines | Purpose |
|------|-------|---------|
| `www/voyagement.php` | 1,484 | Multi-step workflow container |
| `www/voyage_management.php` | 1,350 | Voyage list/search UI |
| `www/index.php` | 617 | Main dashboard |
| `www/login.php` | 400 | Authentication page |
| `www/api/config.php` | 76 | Database & auth config |
| `sql/init.sql` | 330 | Complete DB schema + seed data |

---

*This documentation was generated by analyzing the complete codebase at `/home/ssuser/dev/sbsplatformdev/biosevv3/biosecsamoasys/`*
