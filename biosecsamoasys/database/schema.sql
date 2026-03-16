-- Import Permit Officers Table
CREATE TABLE IF NOT EXISTS officers (
    officer_id INT PRIMARY KEY AUTO_INCREMENT,
    officer_name VARCHAR(255) NOT NULL,
    officer_email VARCHAR(255) NOT NULL,
    officer_role VARCHAR(100) NOT NULL,
    department VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(50),
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1,
    INDEX idx_officer_active (officer_role, is_active),
    INDEX idx_officer_email (officer_email, is_active)
);

-- Create Import Permits Table
CREATE TABLE IF NOT EXISTS import_permits (
    permit_id INT PRIMARY KEY AUTO_INCREMENT,
    permit_number VARCHAR(50) NOT NULL UNIQUE,
    ira_reference VARCHAR(100) NOT NULL,
    issuing_officer_id INT NOT NULL,
    issue_date DATE NOT NULL,
    permit_validity DATE NOT NULL,
    port_of_entry VARCHAR(100) NOT NULL,
    importer VARCHAR(255) NOT NULL,
    importer_address TEXT NOT NULL,
    exporter VARCHAR(255) NOT NULL,
    exporter_address TEXT NOT NULL,
    authorized_officer VARCHAR(255) NOT NULL,
    end_use TEXT NOT NULL,
    means_of_conveyance VARCHAR(50) NOT NULL,
    template_type VARCHAR(50) NOT NULL,
    commodity TEXT NOT NULL,
    import_requirements TEXT,
    import_requirements_linked BOOLEAN DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected', 'expired') DEFAULT 'pending',
    created_by VARCHAR(100) DEFAULT 'Bio Officer',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_by VARCHAR(100) DEFAULT 'Bio Officer',
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (issuing_officer_id) REFERENCES officers(officer_id),
    INDEX idx_permit_number (permit_number),
    INDEX idx_ira_reference (ira_reference),
    INDEX idx_status (status),
    INDEX idx_importer (importer),
    INDEX idx_created_date (created_date),
    INDEX idx_modified_date (modified_date)
);

-- Add foreign key constraint
ALTER TABLE import_permits ADD CONSTRAINT fk_permits_officer FOREIGN KEY (issuing_officer_id) REFERENCES officers(officer_id);

-- Add sample data if tables are empty
INSERT IGNORE INTO officers (officer_id, officer_name, officer_email, officer_role, department, is_active) VALUES
(1, 'Bio Officer One', 'bio_officer1@samoa.gov.ws', 'Biosecurity Officer', 'Biosecurity Enforcement', 1),
(2, 'Bio Officer Two', 'bio_officer2@samoa.gov.ws', 'Senior Biosecurity Officer', 'Biosecurity Enforcement', 1),
(3, 'Chief Biosecurity Officer', 'chief_biosecurity@samoa.gov.ws', 'Chief Biosecurity Officer', 'Biosecurity Enforcement', 1);