<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

// Check authentication and admin access
if (!requireAuth('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$username = trim($_POST['username'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$accessLevel = $_POST['access_level'] ?? 'viewer';
$department = trim($_POST['department'] ?? '');

// Validation
$errors = [];

if (empty($username)) {
    $errors['username'] = 'Username is required';
} elseif (strlen($username) < 3) {
    $errors['username'] = 'Username must be at least 3 characters';
}

if (empty($firstName)) {
    $errors['first_name'] = 'First name is required';
}

if (empty($lastName)) {
    $errors['last_name'] = 'Last name is required';
}

if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
}

if (empty($password)) {
    $errors['password'] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters';
}

if (!in_array($accessLevel, ['admin', 'officer', 'viewer'])) {
    $errors['access_level'] = 'Invalid access level';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Please correct the errors below', 'errors' => $errors]);
    exit;
}

try {
    $conn = getDBConnection();

    // Check for duplicate username
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists', 'errors' => ['username' => 'This username is already taken']]);
        exit;
    }

    // Check for duplicate email
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists', 'errors' => ['email' => 'This email is already registered']]);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("
        INSERT INTO users (
            username, first_name, last_name, email, password_hash,
            access_level, department, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        $username,
        $firstName,
        $lastName,
        $email,
        $passwordHash,
        $accessLevel,
        $department
    ]);

    $newUserId = $conn->lastInsertId();

    // Log the creation
    $stmt = $conn->prepare("
        INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
        VALUES (NULL, 'CREATE_USER', ?, ?)
    ");
    $stmt->execute([
        "Created user '{$username}' ({$firstName} {$lastName}) with role '{$accessLevel}'",
        $_SESSION['user_id'] ?? 'System'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'user_id' => $newUserId
    ]);

} catch (Exception $e) {
    error_log("Create user error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create user: ' . $e->getMessage()
    ]);
}
?>