<?php
/**
 * SECURITY PATCH 1: Add Authentication to All API Endpoints
 * File: /www/api/auth_middleware.php
 * Description: Centralized authentication middleware for all API endpoints
 * Priority: CRITICAL
 * Status: PATCH
 */

// Create this new file to centralize authentication
header('Content-Type: application/json');

// Start session
session_start();

// Include config if not already included
if (!function_exists('isLoggedIn')) {
    require_once 'config.php';
}

/**
 * Check if user is authenticated
 * Returns JSON response and exits if not authenticated
 */
function requireAPIAuth() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized access - Please log in',
            'code' => 'UNAUTHORIZED'
        ]);
        exit;
    }
    return true;
}

/**
 * Check if user has required role
 * @param string $requiredRole Minimum role required (viewer, officer, admin)
 */
function requireAPIRole($requiredRole = 'viewer') {
    requireAPIAuth();

    $userRole = $_SESSION['access_level'] ?? '';
    $roleHierarchy = ['viewer' => 0, 'officer' => 1, 'admin' => 2];

    if (!isset($roleHierarchy[$userRole]) ||
        $roleHierarchy[$userRole] < $roleHierarchy[$requiredRole]) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Insufficient permissions',
            'code' => 'FORBIDDEN'
        ]);
        exit;
    }
}

/**
 * Get current authenticated user
 * @return array|null User data or null if not authenticated
 */
function getCurrentAPIUser() {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'access_level' => $_SESSION['access_level'] ?? null,
    ];
}
?>