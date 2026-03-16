# Biosec Samoa System Architecture

This document provides a comprehensive overview of the application's page structure, data flow, and specialized components.

## 🚀 System Overview
**biosecsamoasys** is a modular biosecurity management system built on a PHP/MySQL/Docker stack. It follows a multi-step workflow pattern for managing maritime arrivals.

## 📁 Directory Structure

```
biosecsamoasys/
├── www/                          # Web application root
│   ├── api/                      # REST API endpoints
│   ├── js/                       # Modular JavaScript components
│   ├── styles/                   # CSS and theme definitions
│   └── *.php                    # Core application pages
├── sql/                          # Database initialization and data scripts
├── lighttpd/                     # Web server configuration
└── docker/                       # Container orchestration settings
```

## 🗺️ Page Hierarchy & Workflow

### 1. Main Dashboard (`index.php`)
- Entry point with high-level statistics
- Recent activity feed for voyages, seizures, and inspections
- Quick-access navigation to core modules

### 2. Voyage Management (`voyage_management.php`)
- Central hub for all voyage records
- Search, filter, and status tracking
- Action menu for editing, viewing, or archiving voyages

### 3. Multi-Step Workflow (`voyagement.php`)
The core operational logic follows a 5-step sequence:
1.  **Voyage Details** (`voyage_form.php`)
2.  **Passenger Inspection** (`inspection_form.php`)
3.  **Passenger Seizure** (`seizure_form.php`)
4.  **Cargo Seizure** (`cargo_seizure_form.php`)
5.  **Cargo Release** (`cargo_release_form.php`)

### 4. Specialized Management
- **Location Management** (`location_management.php`): Port and terminal registry
- **Import Permits** (`import_permits.php`): Regulatory document management
- **User Management** (`user_management.php`): RBAC and user administration

## 🔌 API Layer Architecture

The system uses a standardized API pattern for all data operations:

- **Data Retrieval**: `get_*.php` (e.g., `get_voyages.php`, `get_ports.php`)
- **Submission**: `submit_*.php` (e.g., `submit_voyage.php`)
- **Life-cycle**: `voyage_crud.php`, `voyage_status.php`
- **Configuration**: `config.php` (Centralized DB and Auth logic)

## 🎨 Theme & UI System
Supported themes are managed via `settings.php` and applied globally:
- **Clear Skies (Purple)**: Default professional theme
- **Green Environment**: Sustainability focus
- **Blue Ocean**: Maritime/Oceanic focus

## 🔐 Security Framework
- **Session Management**: Centralized in `config.php`
- **CSRF Protection**: Token-based validation on all state-changing endpoints
- **Authentication**: Role-based access control (Admin, Officer, Viewer)
- **Data Integrity**: Prepared statements with PDO for all database interactions

---
*Last Updated: 2026-02-08*
