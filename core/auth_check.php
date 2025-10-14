<?php
/**
 * auth_check.php - Session and Role Verification
 *
 * This script checks if a user is logged in and has the required role
 * to access a specific page. It should be included at the top of any
 * protected administrative page.
 */

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Requires a user to be authenticated with a specific role.
 *
 * @param string $required_role The role required to access the page (e.g., 'super_admin').
 */
function require_auth($required_role) {
    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        // User is not logged in, redirect to the login page with an error message.
        // For now, we assume the only login is for super admin. This can be enhanced later.
        header('Location: /views/super_admin/login.php?error=' . urlencode('Please log in to access this page.'));
        exit;
    }

    // 2. Check if user has the correct role
    if ($_SESSION['user_role'] !== $required_role) {
        // User is logged in but has the wrong role.
        // Destroy the session for security and redirect to login.
        session_unset();
        session_destroy();
        header('Location: /views/super_admin/login.php?error=' . urlencode('You do not have permission to access this page.'));
        exit;
    }

    // If both checks pass, the script continues execution.
}
?>