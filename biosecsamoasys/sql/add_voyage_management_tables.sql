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
    PassengerID VARCHAR(255),
    SeizureDate VARCHAR(255),
    ItemDescription TEXT,
    Quantity VARCHAR(255),
    Unit VARCHAR(255),
    CountryOfOrigin VARCHAR(255),
    DetectionMethod VARCHAR(255),
    ActionTaken TEXT,
    SeizingOfficerName VARCHAR(255),
    ActionOfficer VARCHAR(255),
    DateActionCompleted VARCHAR(255),
    Comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    INDEX idx_voyage_id (VoyageID),
    INDEX idx_seizure_date (SeizureDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cargo seizure table (this will update the existing one with proper structure)
CREATE TABLE IF NOT EXISTS cargo_seizure (
    CargoSeizureID INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NOT NULL,
    ContainerCargoRefNo VARCHAR(255),
    Importer VARCHAR(255),
    CargoDescription TEXT,
    DepotName VARCHAR(255),
    DetectionMethod VARCHAR(255),
    PortOfEntry VARCHAR(255),
    SeizureDate VARCHAR(255),
    SeizureNo VARCHAR(255),
    CountryOfOrigin VARCHAR(255),
    CommodityType VARCHAR(255),
    Description TEXT,
    Quantity VARCHAR(255),
    Unit VARCHAR(255),
    VolumeKg VARCHAR(255),
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
    ContainerCargoRefNo VARCHAR(255),
    Importer VARCHAR(255),
    CargoDescription TEXT,
    DepotName VARCHAR(255),
    ReleaseDate VARCHAR(255),
    ReleaseNo VARCHAR(255),
    CountryOfOrigin VARCHAR(255),
    CommodityType VARCHAR(255),
    Description TEXT,
    Quantity VARCHAR(255),
    Unit VARCHAR(255),
    VolumeKg VARCHAR(255),
    ReleasingOfficerName VARCHAR(255),
    ReleaseOfficer VARCHAR(255),
    Comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (VoyageID) REFERENCES voyage_details(VoyageID) ON DELETE CASCADE,
    INDEX idx_voyage_id (VoyageID),
    INDEX idx_release_date (ReleaseDate),
    INDEX idx_container_ref (ContainerCargoRefNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create audit trail table for voyage management
CREATE TABLE IF NOT EXISTS voyage_audit_trail (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    VoyageID INT NOT NULL,
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
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    access_level ENUM('admin', 'officer', 'viewer') DEFAULT 'officer',
    department VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_access_level (access_level),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (only if they don't exist)
-- Default passwords: admin123 and bio123
INSERT IGNORE INTO users (username, password_hash, first_name, last_name, email, access_level, department) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin@biosecurity.gov.ws', 'admin', 'IT'),
('bio_officer', '$2y$10$92IXUNpkjOQ1YlJ4YkZ4u', 'Biosecurity', 'Officer', 'officer@biosecurity.gov.ws', 'officer', 'Biosecurity');