-- Create missing status records for existing voyages
INSERT INTO voyage_status (VoyageID, status, current_step)
SELECT vd.VoyageID, 'draft', 'voyage_details'
FROM voyage_details vd
LEFT JOIN voyage_status vs ON vd.VoyageID = vs.VoyageID
WHERE vs.VoyageID IS NULL;