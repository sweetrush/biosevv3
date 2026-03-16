<?php
/**
 * SECURITY PATCH 3: Move Database Credentials to Environment Variables
 * Priority: CRITICAL
 * Files to modify: config.php and all API files
 */

echo "=== SECURITY PATCH 3: ENVIRONMENT VARIABLES ===\n\n";

echo "FILE: www/api/config.php\n";
echo "BEFORE:\n";
echo "--------\n";
?>
define('DB_HOST', 'biosec_mysql');
define('DB_NAME', 'biosecurity_db');
define('DB_USER', 'biosec_user');
define('DB_PASS', 'biosec_pass');
<?php
echo "\nAFTER:\n";
echo "--------\n";
?>
<?php echo htmlspecialchars('<?php
// Database configuration from environment variables
define(\'DB_HOST\', $_ENV[\'DB_HOST\'] ?? getenv(\'DB_HOST\') ?: \'biosec_mysql\');
define(\'DB_NAME\', $_ENV[\'DB_NAME\'] ?? getenv(\'DB_NAME\') ?: \'biosecurity_db\');
define(\'DB_USER\', $_ENV[\'DB_USER\'] ?? getenv(\'DB_USER\') ?: \'biosec_user\');
define(\'DB_PASS\', $_ENV[\'DB_PASS\'] ?? getenv(\'DB_PASS\') ?: \'\');

// Function to get database connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Connection failed. Please try again later.");
    }
}
?>')?>

<?php
echo "\n\nFILE: docker-compose.yml\n";
echo "ADD environment section:\n";
echo "--------\n";
?>
services:
  mysql:
    image: mysql:8.0
    container_name: biosec_mysql
    environment:
      MYSQL_ROOT_PASSWORD: biosec_root_pass
      MYSQL_DATABASE: biosecurity_db
      MYSQL_USER: biosec_user
      MYSQL_PASSWORD: biosec_pass
      # Environment variables for application
      DB_HOST: biosec_mysql
      DB_NAME: biosecurity_db
      DB_USER: biosec_user
      DB_PASS: biosec_pass
    # ... rest of config
<?php

echo "\n\nFILE: .env.example (create this file)\n";
echo "--------\n";
?>
# Database Configuration
DB_HOST=biosec_mysql
DB_NAME=biosecurity_db
DB_USER=biosec_user
DB_PASS=your_secure_password_here

# Session Configuration
SESSION_LIFETIME=3600
SESSION_NAME=BIOSECSESSION

# Application Settings
APP_ENV=production
APP_DEBUG=false
<?php

echo "\n\nFILE: .env (create this file - DO NOT commit to git)\n";
echo "--------\n";
?>
# Database Configuration
DB_HOST=biosec_mysql
DB_NAME=biosecurity_db
DB_USER=biosec_user
DB_PASS=CHANGE_THIS_TO_A_SECURE_PASSWORD

# Session Configuration
SESSION_LIFETIME=3600
SESSION_NAME=BIOSECSESSION

# Application Settings
APP_ENV=production
APP_DEBUG=false

echo "\n\nACTION REQUIRED:\n";
echo "1. Update www/api/config.php with the new code above\n";
echo "2. Create .env file with actual passwords (add to .gitignore)\n";
echo "3. Update docker-compose.yml with environment variables\n";
echo "4. Remove hardcoded credentials from ALL other API files\n";
echo "5. Run: git update-index --assume-unchanged .env\n";

?>