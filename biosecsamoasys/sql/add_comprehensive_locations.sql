-- Add comprehensive location data for Samoa regions
-- This script enhances the locations table with more diverse port and border crossing points

-- Additional Apia Locations
INSERT INTO locations (location_id, location_name, region) VALUES
('LOC004', 'Fisheries Wharf', 'Apia'),
('LOC005', 'Fuel Jetty', 'Apia'),
('LOC006', 'Cargo Wharf', 'Apia'),
('LOC007', 'Marina Wharf', 'Apia'),
('LOC008', 'Customs Bonded Warehouse', 'Apia');

-- Upolu Island (Outside Apia)
INSERT INTO locations (location_id, location_name, region) VALUES
('LOC009', 'Mulifanua Wharf', 'Upolu'),
('LOC010', 'Salelologa Wharf', 'Upolu'),
('LOC011', 'Faleolo International Airport', 'Upolu'),
('LOC012', 'Faleolo Domestic Terminal', 'Upolu'),
('LOC013', 'Poutasi Wharf', 'Upolu'),
('LOC014', 'Satitoa Wharf', 'Upolu'),
('LOC015', 'Lalomanu Wharf', 'Upolu');

-- Savaii Island
INSERT INTO locations (location_id, location_name, region) VALUES
('LOC016', 'Salelologa Port', 'Savaii'),
('LOC017', 'Asau Wharf', 'Savaii'),
('LOC018', 'Saleaula Wharf', 'Savaii'),
('LOC019', 'Fagamalo Wharf', 'Savaii'),
('LOC020', 'Matautu Wharf', 'Savaii'),
('LOC021', 'Tuasivi Wharf', 'Savaii'),
('LOC022', 'Safotu Wharf', 'Savaii'),
('LOC023', 'Safotulafai Wharf', 'Savaii');

-- International Airports
INSERT INTO locations (location_id, location_name, region) VALUES
('LOC024', 'Faleolo International Airport - Cargo', 'Upolu'),
('LOC025', 'Fagalii Airport', 'Upolu'),
('LOC026', 'Maota Airport', 'Savaii');

-- Land Border Crossings (if applicable)
INSERT INTO locations (location_id, location_name, region) VALUES
('LOC027', 'Apolima Strait Crossing', 'Inter-Island'),
('LOC028', 'Manono Island Wharf', 'Inter-Island');

-- Additional Regional Locations
INSERT INTO locations (location_id, location_name, region) VALUES
('LOC029', 'Aiga-i-le-Tai District Wharf', 'Aiga-i-le-Tai'),
('LOC030', 'Aiga-i-tai District Wharf', 'Aiga-i-tai'),
('LOC031', 'Atua District Wharf', 'Atua'),
('LOC032', 'Va\\'a-o-Fonoti District Wharf', 'Va\\'a-o-Fonoti'),
('LOC033', 'Fa\\'asaleleaga District Wharf', 'Faasaleleaga'),
('LOC034', 'Gaga'emauga District Wharf', 'Gagaemauga'),
('LOC035', 'Gagaifomauga District Wharf', 'Gagaifomauga'),
('LOC036', 'Palauli District Wharf', 'Palauli'),
('LOC037', 'Satupaitea District Wharf', 'Satupaitea'),
('LOC038', 'Tuamasaga District Wharf', 'Tuamasaga');

-- Private and Commercial Ports
INSERT INTO locations (location_id, location_name, region) VALUES
('LOC039', 'Sinalei Resort Private Wharf', 'Upolu'),
('LOC040', 'Coconuts Beach Resort Wharf', 'Upolu'),
('LOC041', 'Aganoa Lodge Wharf', 'Upolu'),
('LOC042', 'Saletoga Sands Wharf', 'Upolu'),
('LOC043', 'Return to Paradise Wharf', 'Upolu');

-- Specialized Facilities
INSERT INTO locations (location_id, location_name, region) VALUES
('LOC044', 'Samoa Port Authority Headquarters', 'Apia'),
('LOC045', 'Ministry of Agriculture Wharf', 'Apia'),
('LOC046', 'Fisheries Division Wharf', 'Apia'),
('LOC047', 'Emergency Response Dock', 'Apia'),
('LOC048', 'Naval Base Wharf', 'Apia');

-- Check total count after insertion
SELECT
    COUNT(*) as total_locations,
    region,
    COUNT(CASE WHEN region = 'Apia' THEN 1 END) as apia_count,
    COUNT(CASE WHEN region = 'Upolu' THEN 1 END) as upolu_count,
    COUNT(CASE WHEN region = 'Savaii' THEN 1 END) as savaii_count,
    COUNT(CASE WHEN region LIKE 'Inter-Island' THEN 1 END) as inter_island_count
FROM locations;