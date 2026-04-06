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
$userId = intval($_POST['user_id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; // Optional for updates
$accessLevel = $_POST['access_level'] ?? 'viewer';
$department = trim($_POST['department'] ?? '');

// Validation
$errors = [];

if ($userId <= 0) {
    $errors['user_id'] = 'Invalid user ID';
}

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

if (!empty($password) && strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters';
}

if (!in_array($accessLevel, ['admin', 'officer', 'viewer', 'authorising_officer'])) {
    $errors['access_level'] = 'Invalid access level';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Please correct the errors below', 'errors' => $errors]);
    exit;
}

try {
    $conn = getDBConnection();

    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Check for duplicate username (excluding current user)
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->execute([$username, $userId]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists', 'errors' => ['username' => 'This username is already taken']]);
        exit;
    }

    // Check for duplicate email (excluding current user)
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $userId]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists', 'errors' => ['email' => 'This email is already registered']]);
        exit;
    }

    // Update user
    if (!empty($password)) {
        // Update with new password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE users
            SET username = ?, first_name = ?, last_name = ?, email = ?, password_hash = ?,
                access_level = ?, department = ?
            WHERE user_id = ?
        ");

        $stmt->execute([
            $username,
            $firstName,
            $lastName,
            $email,
            $passwordHash,
            $accessLevel,
            $department,
            $userId
        ]);
    } else {
        // Update without changing password
        $stmt = $conn->prepare("
            UPDATE users
            SET username = ?, first_name = ?, last_name = ?, email = ?,
                access_level = ?, department = ?
            WHERE user_id = ?
        ");

        $stmt->execute([
            $username,
            $firstName,
            $lastName,
            $email,
            $accessLevel,
            $department,
            $userId
        ]);
    }

    // Log the update
    $stmt = $conn->prepare("
        INSERT INTO voyage_audit_trail (VoyageID, action, action_details, performed_by)
        VALUES (NULL, 'UPDATE_USER', ?, ?)
    ");
    $stmt->execute([
        "Updated user '{$username}' (ID: {$userId})",
        $_SESSION['user_id'] ?? 'System'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);

} catch (Exception $e) {
    error_log("Update user error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update user: ' . $e->getMessage()
    ]);
}
?>