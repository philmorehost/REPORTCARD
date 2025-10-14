<?php
/**
 * school_settings_controller.php - Handles updating school-specific settings.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only school admins can execute these actions
require_auth('school_admin');

$action = $_POST['action'] ?? '';
$school_id = $_SESSION['school_id'];

switch ($action) {
    case 'update_settings':
        handle_update_settings($pdo, $school_id);
        break;
    case 'close_account':
        handle_close_account($pdo, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

/**
 * Updates the language preference and system URL for the school.
 *
 * @param PDO $pdo The database connection object.
 * @param int $school_id The ID of the school to update.
 */
function handle_update_settings($pdo, $school_id) {
    // --- Sanitize and Validate Language ---
    $language = $_POST['language'] ?? 'en';
    if (!preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $language)) { // Allow for locales like en_US
        redirect_with_message('Invalid language code provided.', 'error');
        return;
    }

    // --- Sanitize and Validate Custom Domain ---
    $custom_domain = trim($_POST['custom_domain'] ?? '');
    if (!empty($custom_domain) && !filter_var('http://' . $custom_domain, FILTER_VALIDATE_URL)) {
        redirect_with_message('Invalid domain name format provided.', 'error');
        return;
    }
    // If domain is empty, store it as NULL
    $custom_domain = !empty($custom_domain) ? $custom_domain : null;

    try {
        // Update language and custom domain in the `schools` table
        $stmt = $pdo->prepare("UPDATE schools SET language = :language, custom_domain = :custom_domain WHERE id = :id");
        $stmt->execute([
            'language' => $language,
            'custom_domain' => $custom_domain,
            'id' => $school_id
        ]);

        redirect_with_message('Settings updated successfully.', 'success');

    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

/**
 * Sets the school's status to 'closed'.
 *
 * @param PDO $pdo The database connection object.
 * @param int $school_id The ID of the school to close.
 */
function handle_close_account($pdo, $school_id) {
    try {
        $stmt = $pdo->prepare("UPDATE schools SET status = 'closed' WHERE id = :id");
        $stmt->execute(['id' => $school_id]);

        // Log the user out
        session_destroy();

        // Redirect to a confirmation page or the main site
        header('Location: /index.php?account_closed=true');
        exit;

    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}


/**
 * Redirects back to the school settings page with a message.
 */
function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/school_admin/school_settings.php');
    exit;
}
?>