-- Migration script: Convert VARCHAR columns to INT for numeric fields
-- This ensures data integrity and proper type validation

USE biosecurity_db;

-- Start transaction
START TRANSACTION;

-- ============================================
-- 1. voyage_container_counts table
-- ============================================

-- Convert count from VARCHAR to INT
ALTER TABLE voyage_container_counts
    MODIFY COLUMN count INT NULL;

-- ============================================
-- 2. passenger_inspection table
-- ============================================

-- Convert numeric fields from VARCHAR to INT
ALTER TABLE passenger_inspection
    MODIFY COLUMN LocationID INT NULL,
    MODIFY COLUMN NoOfConsignments INT NULL,
    MODIFY COLUMN NoOfNonCompliant INT NULL;

-- ============================================
-- 3. passenger_seizure table
-- ============================================

-- Convert numeric fields from VARCHAR to INT/DECIMAL
ALTER TABLE passenger_seizure
    MODIFY COLUMN PortOfEntry INT NULL,
    MODIFY COLUMN CountryOfOrigin INT NULL,
    MODIFY COLUMN Quantity DECIMAL(10,2) NULL,
    MODIFY COLUMN Volume DECIMAL(10,2) NULL;

-- ============================================
-- 4. cargo_seizure table
-- ============================================

-- Convert numeric fields from VARCHAR to INT/DECIMAL
ALTER TABLE cargo_seizure
    MODIFY COLUMN PortOfEntry INT NULL,
    MODIFY COLUMN CountryOfOrigin INT NULL,
    MODIFY COLUMN Quantity DECIMAL(10,2) NULL,
    MODIFY COLUMN VolumeKg DECIMAL(10,2) NULL;

-- ============================================
-- 5. cargo_release table
-- ============================================

-- Convert numeric fields from VARCHAR to DECIMAL
ALTER TABLE cargo_release
    MODIFY COLUMN TotalCosts DECIMAL(10,2) NULL;

-- ============================================
-- 6. release_items table
-- ============================================

-- Convert numeric fields from VARCHAR to INT/DECIMAL
ALTER TABLE release_items
    MODIFY COLUMN Quantity DECIMAL(10,2) NULL,
    MODIFY COLUMN Weight DECIMAL(10,2) NULL,
    MODIFY COLUMN CountryOfOrigin INT NULL;

-- Commit transaction
COMMIT;

-- Verification queries
SELECT 'Migration completed successfully!' AS status;
SELECT COUNT(*) AS total_voyages FROM voyage_details;
SELECT COUNT(*) AS total_inspections FROM passenger_inspection;
SELECT COUNT(*) AS total_seizures FROM passenger_seizure;
SELECT COUNT(*) AS total_cargo_seizures FROM cargo_seizure;
SELECT COUNT(*) AS total_cargo_releases FROM cargo_release;
SELECT COUNT(*) AS total_release_items FROM release_items;
