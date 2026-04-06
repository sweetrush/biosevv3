-- Create import permit templates table
CREATE TABLE IF NOT EXISTS import_permit_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    ira_reference VARCHAR(100) NOT NULL,
    in_use TINYINT(1) DEFAULT 1,
    commodity_1 VARCHAR(255) DEFAULT NULL,
    commodity_2 VARCHAR(255) DEFAULT NULL,
    import_requirements TEXT,
    modified_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modified_by VARCHAR(100) DEFAULT 'System'
);

-- Seed with existing template data
INSERT INTO import_permit_templates (template_name, ira_reference, in_use, commodity_1, commodity_2, import_requirements, modified_by) VALUES
('Agricultural Products', 'IRA-AG-001', 1, 'Agricultural Products', NULL, 'All agricultural products must be accompanied by phytosanitary certificates from country of origin. Products may be subject to inspection and treatment upon arrival.', 'System'),
('Animal Products', 'IRA-AN-001', 1, 'Animal Products', NULL, 'All animal products must be accompanied by health certificates. Products may be subject to veterinary inspection and quarantine.', 'System'),
('Plant Products', 'IRA-PL-001', 1, 'Plant Products', NULL, 'All plant materials must be free from pests and diseases. Products may be subject to inspection and treatment by biosecurity officers.', 'System'),
('Processed Foods', 'IRA-PF-001', 1, 'Processed Foods', NULL, 'Processed foods must have valid food safety certificates. Products may be sampled for testing.', 'System'),
('Machinery & Equipment', 'IRA-ME-001', 1, 'Machinery', 'Equipment', 'Machinery must be clean and free from soil and organic matter. May require fumigation treatment.', 'System');
