# SECURITY PATCHES - IMPLEMENTATION GUIDE

This directory contains security patches to remediate the 47 vulnerabilities identified in the security assessment.

## Quick Start: CRITICAL Fixes (Deploy Today)

### Patch 1: Add Authentication to All API Endpoints
**Priority:** CRITICAL
**Time:** 2 hours

```bash
# For each file in the list below, add authentication at the top:

# File: www/api/get_voyages.php
# Add after line 1 (after <?php):
session_start();
require_once 'config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
```

**Files to patch (18 files):**
1. get_voyages.php
2. get_ports.php
3. get_locations.php
4. get_vessels.php
5. voyage_crud.php
6. voyage_status.php
7. get_recent_cargo_seizures.php
8. get_cargo_seizure.php
9. get_officers.php
10. get_commodities.php
11. get_recent_seizures.php
12. get_recent_inspections.php
13. get_recent_releases.php
14. get_passenger_seizure.php
15. get_container_types.php
16. get_countries.php
17. get_permits.php
18. edit_location.php

### Patch 2: Add CSRF Protection
**Priority:** HIGH
**Time:** 1 hour

```php
// Add to ALL state-changing endpoints (after session_start):

// Check CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}
```

**Files to patch (6 files):**
1. submit_cargo_seizure.php
2. submit_cargo_release.php
3. delete_location.php
4. update_passenger_seizure.php
5. update_cargo_seizure.php
6. voyage_crud.php (already has auth, add CSRF)

### Patch 3: Remove Default Credentials
**Priority:** HIGH
**Time:** 5 minutes

**File:** www/login.php
**Lines:** 357-361

```html
<!-- REMOVE THIS SECTION ENTIRELY -->
<!--
<div class="default-credentials">
    <h4>Default Credentials:</h4>
    <p><strong>Administrator:</strong> <code>admin</code> / <code>admin123</code></p>
    <p><strong>Biosecurity Officer:</strong> <code>bio_officer</code> / <code>bio123</code></p>
</div>
-->
```

### Patch 4: Environment Variables
**Priority:** CRITICAL
**Time:** 30 minutes

**File:** www/api/config.php
**Change:**

```php
// BEFORE:
define('DB_HOST', 'biosec_mysql');
define('DB_NAME', 'biosecurity_db');
define('DB_USER', 'biosec_user');
define('DB_PASS', 'biosec_pass');

// AFTER:
define('DB_HOST', $_ENV['DB_HOST'] ?? 'biosec_mysql');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'biosecurity_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'biosec_user');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'biosec_pass');
```

**File:** docker-compose.yml
**Add:**

```yaml
environment:
  MYSQL_ROOT_PASSWORD: biosec_root_pass
  MYSQL_DATABASE: biosecurity_db
  MYSQL_USER: biosec_user
  MYSQL_PASSWORD: biosec_pass
  # Application environment variables
  DB_HOST: biosec_mysql
  DB_NAME: biosecurity_db
  DB_USER: biosec_user
  DB_PASS: biosec_pass
```

## Files in This Directory

1. **01_fix_api_authentication.php** - Authentication middleware
2. **02_apply_auth_to_endpoints.php** - Instructions for applying auth
3. **03_environment_variables.php** - Database credential protection
4. **04_session_security.php** - Session management configuration
5. **05_csrf_protection.php** - CSRF protection implementation
6. **06_docker_security.php** - Docker security hardening
7. **07_input_validation.php** - Input validation examples
8. **08_error_handling.php** - Secure error handling

## Testing Your Patches

After applying patches, test each endpoint:

```bash
# Test with authentication
curl -X POST http://localhost:8081/api/get_voyages.php \
  -H "Cookie: PHPSESSID=your_valid_session_id"

# Should return: {"success":true,"data":[...]}

# Test without authentication
curl -X POST http://localhost:8081/api/get_voyages.php

# Should return: {"success":false,"error":"Unauthorized"}
```

## Deployment Checklist

- [ ] All API endpoints require authentication
- [ ] All state-changing endpoints have CSRF protection
- [ ] Default credentials removed from login page
- [ ] Credentials moved to environment variables
- [ ] Session security configured
- [ ] Docker MySQL port removed (if not needed externally)

## Need Help?

See the full security assessment report:
- **Technical Details:** `/home/ssuser/dev/sbsplatformdev/biosevv3/biosecsamoasys/SECURITY_ASSESSMENT_REPORT.md`
- **Executive Summary:** `/home/ssuser/dev/sbsplatformdev/biosevv3/biosecsamoasys/SECURITY_ASSESSMENT_SUMMARY.md`

## Support

Contact the security team for questions:
- Email: security@company.com
- Slack: #security