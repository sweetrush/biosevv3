-- Add authorising_officer role to users table
ALTER TABLE users
    MODIFY COLUMN access_level ENUM('admin', 'officer', 'viewer', 'authorising_officer') DEFAULT 'officer';
