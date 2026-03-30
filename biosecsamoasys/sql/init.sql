-- Create biosecurity database
CREATE DATABASE IF NOT EXISTS biosecurity_db;
USE biosecurity_db;

-- Create voyage_details table
CREATE TABLE IF NOT EXISTS voyage_details (
    VoyageID INT AUTO_INCREMENT PRIMARY KEY,
    VoyageNo VARCHAR(255),
    PortOfLoadingID INT NULL,
    LastPortID INT NULL,
    PortOfArrivalID INT NULL,
    LocationID INT NULL,
    ArrivalDate VARCHAR(255),
    Pax INT NULL,
    Crew INT NULL,
    CrewSearched INT NULL,
    TotalDischarged INT NULL,
    VesselID VARCHAR(255),
    RoomSealed VARCHAR(255),
    AirOfSea VARCHAR(255),
    NoXRated INT NULL,
    BondedAnimals VARCHAR(255),
    BondedAnimalsDescription TEXT,
    AnimalHealthCertificate VARCHAR(255),
    NumberContainers INT NULL,
    NumberCargosDischarged INT NULL,
    PortAuthority VARCHAR(255),
    ModifiedBy VARCHAR(255),
    ModifiedDate VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_voyage_no (VoyageNo),
    INDEX idx_vessel_id (VesselID),
    INDEX idx_arrival_date (ArrivalDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ports reference table
CREATE TABLE IF NOT EXISTS ports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    port_id VARCHAR(255) UNIQUE,
    port_name VARCHAR(255),
    country VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create vessels reference table
CREATE TABLE IF NOT EXISTS vessels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vessel_id VARCHAR(255) UNIQUE,
    vessel_name VARCHAR(255),
    vessel_type VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create locations reference table
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_id VARCHAR(255) UNIQUE,
    location_name VARCHAR(255),
    region VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create container types reference table
CREATE TABLE IF NOT EXISTS container_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    container_type_code VARCHAR(50) UNIQUE,
    container_type_name VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create voyage container counts table (links voyages with container types)
CREATE TABLE IF NOT EXISTS voyage_container_counts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NOT NULL,
    container_type_code VARCHAR(50) NOT NULL,
    count INT NULL,
    ModifiedByOfficerID VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    FOREIGN KEY (container_type_code) REFERENCES container_types(container_type_code) ON DELETE CASCADE,
    UNIQUE KEY unique_voyage_container (VoyageID, container_type_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
INSERT INTO ports (port_id, port_name, country) VALUES
('PORT001', 'Apia Port', 'Samoa'),
('PORT002', 'Pago Pago', 'American Samoa'),
('PORT003', 'Nuku''alofa', 'Tonga');

INSERT INTO vessels (vessel_id, vessel_name, vessel_type) VALUES
('VESSEL001', 'MV Pacific Star', 'Cargo Ship'),
('VESSEL002', 'SS Ocean Voyager', 'Passenger Ship'),
('VESSEL003', 'MS Island Express', 'Ferry');

INSERT INTO locations (location_id, location_name, region) VALUES
('LOC001', 'Main Wharf', 'Apia'),
('LOC002', 'Container Terminal', 'Apia'),
('LOC003', 'Passenger Terminal', 'Apia');

-- Insert cargo/container types as per requirements
INSERT INTO container_types (container_type_code, container_type_name, description) VALUES
('Cars', 'Cars', 'Vehicles - Cars'),
('Carsandparts', 'Cars and Parts', 'Vehicles with spare parts'),
('CarsandPersonalEffects', 'Cars and Personal Effects', 'Vehicles with personal belongings'),
('FrozenBeef', 'Frozen Beef', 'Frozen beef products'),
('FrozenPork', 'Frozen Pork', 'Frozen pork products'),
('FrozenChicken', 'Frozen Chicken', 'Frozen chicken products'),
('FrozenLamb', 'Frozen Lamb', 'Frozen lamb products'),
('FrozenTurky', 'Frozen Turkey', 'Frozen turkey products'),
('FrozenVegies', 'Frozen Vegetables', 'Frozen vegetable products'),
('FrozenFruits', 'Frozen Fruits', 'Frozen fruit products'),
('FrozenMix', 'Frozen Mix', 'Mixed frozen products'),
('FreshBeef', 'Fresh Beef', 'Fresh beef products'),
('FreshPork', 'Fresh Pork', 'Fresh pork products'),
('FreshChicken', 'Fresh Chicken', 'Fresh chicken products'),
('FreshLamb', 'Fresh Lamb', 'Fresh lamb products'),
('FreshTurky', 'Fresh Turkey', 'Fresh turkey products'),
('FreshVegies', 'Fresh Vegetables', 'Fresh vegetable products'),
('FreshFruits', 'Fresh Fruits', 'Fresh fruit products'),
('FreshMix', 'Fresh Mix', 'Mixed fresh products');

-- Create CommodityGroup table as per Thingstodo specification
CREATE TABLE IF NOT EXISTS commodity_groups (
    CommodityGroupID VARCHAR(50) PRIMARY KEY,
    CommodityGroup VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create CommodityType table as per Thingstodo specification
CREATE TABLE IF NOT EXISTS commodity_types (
    CommodityTypeID INT AUTO_INCREMENT PRIMARY KEY,
    CommodityType VARCHAR(255),
    CommodityGroupID VARCHAR(50),
    SortOrderNumber INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CommodityGroupID) REFERENCES commodity_groups(CommodityGroupID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create passenger inspection table
CREATE TABLE IF NOT EXISTS passenger_inspection (
    PassengerInspectionID INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NOT NULL,
    CommodityTypeID INT NULL,
    LocationID INT NULL,
    NoOfConsignments INT NULL,
    NoOfNonCompliant INT NULL,
    ModifiedBy VARCHAR(255),
    ModifiedDate VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    FOREIGN KEY (CommodityTypeID) REFERENCES commodity_types(CommodityTypeID) ON DELETE SET NULL,
    INDEX idx_voyage_id (VoyageID),
    INDEX idx_commodity_type (CommodityTypeID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create voyage status management table
CREATE TABLE IF NOT EXISTS voyage_status (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NOT NULL,
    current_step VARCHAR(50) DEFAULT 'voyage_details',
    status ENUM('draft', 'in_progress', 'completed', 'archived') DEFAULT 'draft',
    voyage_details_complete BOOLEAN DEFAULT FALSE,
    passenger_inspection_complete BOOLEAN DEFAULT FALSE,
    passenger_seizure_complete BOOLEAN DEFAULT FALSE,
    cargo_seizure_complete BOOLEAN DEFAULT FALSE,
    cargo_release_complete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    UNIQUE KEY unique_voyage_status (VoyageID),
    INDEX idx_status (status),
    INDEX idx_current_step (current_step)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create passenger seizure table
CREATE TABLE IF NOT EXISTS passenger_seizure (
    PassengerSeizureID INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NOT NULL,
    SeizureDate VARCHAR(255),
    SeizureNo VARCHAR(255),
    Importer VARCHAR(255),
    DetectionMethod VARCHAR(255),
    PortOfEntry INT NULL,
    GoodsDeclared VARCHAR(255),
    CountryOfOrigin INT NULL,
    CommodityType VARCHAR(255),
    Description TEXT,
    Quantity DECIMAL(10,2) NULL,
    Unit VARCHAR(255),
    Volume DECIMAL(10,2) NULL,
    OfficerName VARCHAR(255),
    ActionTaken TEXT,
    ActionOfficer VARCHAR(255),
    DateActionCompleted VARCHAR(255),
    Comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    INDEX idx_voyage_id (VoyageID),
    INDEX idx_seizure_date (SeizureDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cargo seizure table
CREATE TABLE IF NOT EXISTS cargo_seizure (
    CargoSeizureID INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NOT NULL,
    ContainerCargoRefNo VARCHAR(255),
    Importer VARCHAR(255),
    CargoDescription TEXT,
    DepotName VARCHAR(255),
    DetectionMethod VARCHAR(255),
    PortOfEntry INT NULL,
    SeizureDate VARCHAR(255),
    SeizureNo VARCHAR(255),
    CountryOfOrigin INT NULL,
    CommodityType VARCHAR(255),
    Description TEXT,
    Quantity DECIMAL(10,2) NULL,
    Unit VARCHAR(255),
    VolumeKg DECIMAL(10,2) NULL,
    SeizingOfficerName VARCHAR(255),
    ActionTaken TEXT,
    ActionOfficer VARCHAR(255),
    DateActionCompleted VARCHAR(255),
    Comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    INDEX idx_voyage_id (VoyageID),
    INDEX idx_seizure_date (SeizureDate),
    INDEX idx_container_ref (ContainerCargoRefNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cargo release table
CREATE TABLE IF NOT EXISTS cargo_release (
    CargoReleaseID INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NOT NULL,
    ReleaseNo VARCHAR(255),
    Importer VARCHAR(255),
    ReleaseType VARCHAR(255),
    ReleaseDate VARCHAR(255),
    TotalCosts DECIMAL(10,2) NULL,
    Comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    INDEX idx_voyage_id (VoyageID),
    INDEX idx_release_date (ReleaseDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create audit trail table for voyage management
CREATE TABLE IF NOT EXISTS voyage_audit_trail (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NULL,
    action VARCHAR(100) NOT NULL,
    action_details TEXT,
    performed_by VARCHAR(255),
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    previous_values TEXT,
    new_values TEXT,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    INDEX idx_voyage_id (VoyageID),
    INDEX idx_action (action),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user table for officer management
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'officer', 'viewer') DEFAULT 'officer',
    department VARCHAR(255),
    email VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT INTO users (username, full_name, role, department, email) VALUES
('admin', 'System Administrator', 'admin', 'IT', 'admin@biosecurity.gov.ws'),
('bio_officer', 'Biosecurity Officer', 'officer', 'Biosecurity', 'officer@biosecurity.gov.ws');

-- Insert actual CommodityGroups
INSERT INTO commodity_groups (CommodityGroupID, CommodityGroup) VALUES
('1', 'Animals and Animal Products'),
('2', 'Plants and Plant Products'),
('3', 'MEV'),
('4', 'Other'),
('5', 'Pesticides');

-- Insert actual CommodityTypes from business requirements
INSERT INTO commodity_types (CommodityType, CommodityGroupID, SortOrderNumber) VALUES
('Nursery Stock', '2', 0),
('Other', '4', 22),
('Empty bottles', '4', 0),
('Pesticides', '5', 23),
('Tinned Meat', '1', 7),
('Live Animal(s)', '1', 1),
('Beef', '1', 3),
('Poultry', '1', 4),
('Pork', '1', 5),
('Lamb', '1', 6),
('Animal Products', '1', 2),
('Vegetables & Fruits', '2', 16),
('Seeds', '2', 14),
('Timber', '2', 15),
('Flowers', '2', 12),
('Equipment', '3', 20),
('Machinery', '3', 19),
('Stock Feed', '2', 18),
('Vehicle(s)', '3', 21),
('Plant Products', '2', 11),
('seafood', '1', 22),
('Tyres', '4', 0),
('Furnitures', '4', 0);
