<?php
/**
 * cms_controller.php - Handles updating the landing page content.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only super admins can execute these actions
require_auth('super_admin');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_content':
        handle_update_content($pdo);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

/**
 * Handles updating all CMS content fields.
 *
 * @param PDO $pdo The database connection object.
 */
function handle_update_content($pdo) {
    // List of all possible keys from the form
    $content_keys = [
        'hero_title', 'hero_subtitle',
        'feature1_title', 'feature1_text',
        'feature2_title', 'feature2_text',
        'feature3_title', 'feature3_text',
        'testimonial1_text', 'testimonial1_author',
        'testimonial2_text', 'testimonial2_author',
        'testimonial3_text', 'testimonial3_author',
        'contact_email', 'contact_phone'
    ];

    try {
        $stmt = $pdo->prepare("UPDATE cms_content SET content_value = :value WHERE content_key = :key");

        foreach ($content_keys as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute(['value' => $_POST[$key], 'key' => $key]);
            }
        }

        redirect_with_message('Landing page content updated successfully.', 'success');

    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}


/**
 * Redirects back to the CMS management page with a message.
 */
function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/super_admin/cms_management.php');
    exit;
}
?>