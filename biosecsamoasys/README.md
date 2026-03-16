# Biosecurity System - Samoa (biosecsamoasys)

A comprehensive biosecurity management system for tracking vessel voyage details, port arrivals, and compliance monitoring for the Samoa Biosecurity Authority.

## 🌟 Overview

The **biosecsamoasys** system is a specialized web application designed to manage maritime biosecurity operations at Samoan ports. It provides end-to-end tracking of vessel voyages, passenger inspections, cargo seizures, and compliance monitoring to protect Samoa's borders from biosecurity threats.

## 🚀 Features

### Core Modules
- **Voyage Management**: Track vessel arrivals, departures, and voyage details
- **Passenger Inspection**: Monitor passenger compliance and biosecurity risks
- **Cargo Seizure Management**: Record and manage seized goods and violations
- **Cargo Release System**: Process and authorize cargo releases
- **Location Management**: Manage ports, terminals, and inspection locations
- **Commodity Tracking**: Categorize and monitor various types of goods and products

### Key Capabilities
- Real-time voyage status tracking
- Multi-step workflow management
- Comprehensive audit trails
- User role-based access control
- Mobile-responsive interface
- Automated reporting capabilities

## 🏗️ Architecture

### Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap
- **Backend**: PHP 8.x
- **Database**: MySQL 8.0
- **Web Server**: Lighttpd
- **Containerization**: Docker & Docker Compose
- **Authentication**: Session-based user management

### System Components

#### 1. Database Schema
The system uses 13 core tables:

**Primary Tables:**
- `voyage_details` - Main voyage information and vessel data
- `voyage_status` - Workflow state management
- `passenger_inspection` - Passenger compliance records
- `passenger_seizure` - Seized items from passengers
- `cargo_seizure` - Commercial cargo seizure records
- `cargo_release` - Cargo release authorizations

**Reference Tables:**
- `ports` - Port registry and information
- `vessels` - Vessel registry
- `locations` - Terminal and facility locations
- `container_types` - 19 cargo/container categories
- `commodity_groups` - 5 commodity categories
- `commodity_types` - 29 specific commodity types
- `users` - System user management

#### 2. API Layer (`/www/api/`)
RESTful API endpoints for:
- Voyage CRUD operations
- Status management
- Data retrieval (ports, vessels, locations, commodities)
- Form submissions (inspections, seizures, releases)
- Recent activity feeds

#### 3. Web Interface (`/www/`)
Modular PHP pages:
- `index.php` - Main dashboard with navigation
- `voyage_management.php` - Comprehensive voyage operations
- `voyage_form.php` - Voyage data entry
- `inspection_form.php` - Passenger inspection interface
- `seizure_form.php` - Seizure recording
- `cargo_seizure_form.php` - Commercial cargo seizures
- `cargo_release_form.php` - Cargo release processing
- `location_management.php` - Port and location management

## 📋 Data Model

### Voyage Management
Each voyage tracks:
- **Vessel Information**: ID, name, type
- **Journey Details**: Voyage number, loading/arrival ports, dates
- **People**: Passenger count, crew details, search status
- **Cargo**: Container counts, cargo types, discharge volumes
- **Biosecurity**: Room sealing, animal certificates, compliance status
- **Audit**: Modified by, timestamps, change history

### Cargo Categories (19 Types)
**Frozen Products**: Beef, Pork, Chicken, Lamb, Turkey, Vegetables, Fruits, Mixed
**Fresh Products**: Beef, Pork, Chicken, Lamb, Turkey, Vegetables, Fruits, Mixed
**Vehicles**: Cars, Cars & Parts, Cars & Personal Effects

### Commodity Types (29 Categories)
**Animals & Animal Products**: Live animals, beef, poultry, pork, lamb, seafood, animal products, tinned meat
**Plants & Plant Products**: Nursery stock, vegetables & fruits, seeds, timber, flowers, plant products, stock feed
**Machinery & Equipment (MEV)**: Equipment, machinery, vehicles
**Other**: Empty bottles, tyres, furniture, miscellaneous items
**Pesticides**: Chemical treatments and pesticides

## 🛠️ Installation & Setup

### Prerequisites
- Docker & Docker Compose
- Git
- Modern web browser

### Quick Start

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd biosecsamoasys
   ```

2. **Start the System**
   ```bash
   docker-compose up -d
   ```

3. **Access the Application**
   - Web Interface: http://localhost:8081
   - MySQL Database: localhost:3307
   - Default Admin: username: `admin`, password: (configured in docker-compose)

### Docker Services

#### MySQL Container
- **Port**: 3307 (external) → 3306 (internal)
- **Database**: biosecurity_db
- **Credentials**: Configured in docker-compose.yml
- **Data Volume**: Persistent storage in `mysql_data`

#### Lighttpd Container
- **Port**: 8081 (external) → 80 (internal)
- **Document Root**: `./www` directory
- **Configuration**: `./lighttpd/lighttpd.conf`

#### PHP Container
- Custom build from `Dockerfile.php`
- Shared volume with Lighttpd for PHP processing
- MySQL connectivity enabled

## 📁 Project Structure

```
biosecsamoasys/
├── www/                          # Web application root
│   ├── api/                      # REST API endpoints
│   │   ├── config.php           # Database configuration
│   │   ├── voyage_*.php         # Voyage management APIs
│   │   ├── submit_*.php         # Form submission handlers
│   │   └── get_*.php            # Data retrieval endpoints
│   ├── *.php                    # Main application pages
│   ├── *.js                     # JavaScript functionality
│   └── styles.css               # Application styling
├── sql/                          # Database scripts
│   ├── init.sql                 # Initial database setup
│   ├── add_*.sql               # Additional data scripts
│   └── create_*.sql            # Table creation scripts
├── lighttpd/                     # Web server configuration
│   └── lighttpd.conf           # Lighttpd settings
├── docker-compose.yml            # Container orchestration
├── Dockerfile.php               # PHP container build
└── README.md                    # This documentation
```

## 🔧 Configuration

### Database Settings
Edit `docker-compose.yml` to modify:
- MySQL credentials
- Database name
- Port mappings

### Web Server
Edit `lighttpd/lighttpd.conf` for:
- URL rewriting
- PHP handling
- Security settings

### Application Settings
Edit `www/api/config.php` for:
- Database connection parameters
- API configuration
- Session settings

## 🚦 Development Workflow

### Branch Strategy
1. Create feature branch from `main`
2. Implement changes
3. Test thoroughly
4. Get user confirmation
5. Merge to `main`

### Critical Development Rules
- **ALWAYS** restart the platform after making changes
- **ALWAYS** reload due to port mapping conflicts
- **ALWAYS** test all changes before merging
- **NEVER** skip platform restart after modifications

### Common Commands

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart after changes (REQUIRED)
docker-compose restart

# View logs
docker-compose logs -f

# Rebuild containers
docker-compose up -d --build

# Access MySQL
docker exec -it biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db

# Run SQL script
docker exec -i biosec_mysql mysql -u root -pbiosec_root_pass biosecurity_db < script.sql
```

## 🔐 Security Status

- [x] CSRF Protection: Implemented on core submission endpoints
- [x] Input Validation: Client and server-side validation active
- [x] Database Security: Using PDO with prepared statements
- [ ] Authentication: In progress (Centralized middleware implementation)
- [ ] Session Hardening: In progress (Secure cookie flags and timeouts)
- [ ] API Security: In progress (Authentication requirement for all endpoints)

## 📊 Reporting & Analytics

The system provides:
- Recent voyage activity feeds
- Seizure and release statistics
- Compliance violation tracking
- Officer activity reports
- Historical data analysis

## 🌐 Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile responsive design

## 📞 Support

For technical support or questions:
- Check the audit logs in the system
- Review container logs: `docker-compose logs`
- Consult the database schema in `sql/init.sql`
- Refer to this documentation

## 📝 Version History

- **v1.0**: Initial implementation with core voyage management
- **v1.1**: Added passenger inspection and seizure modules
- **v1.2**: Enhanced cargo release functionality
- **v1.3**: Improved UI/UX and mobile responsiveness
- **Current**: Feature-complete biosecurity management system

## 🏢 Government Integration

This system is designed for integration with:
- Samoa Biosecurity Authority operations
- Port authority management systems
- Customs and border control agencies
- International maritime biosecurity standards

---

**Note**: This is a specialized government system for biosecurity compliance. All user activities are logged and monitored for regulatory compliance purposes.
