<?php
/**
 * template_controller.php - Handles uploading and deleting report card templates.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only super admins can execute these actions
require_auth('super_admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'upload':
        handle_upload($pdo);
        break;
    case 'delete':
        handle_delete($pdo);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_upload($pdo) {
    // --- Basic Validation ---
    if (empty($_POST['template_name']) || empty($_FILES['template_thumbnail']) || empty($_FILES['template_file'])) {
        redirect_with_message('All fields are required.', 'error');
    }
    if ($_FILES['template_thumbnail']['error'] !== UPLOAD_ERR_OK || $_FILES['template_file']['error'] !== UPLOAD_ERR_OK) {
        redirect_with_message('Error during file upload. Please try again.', 'error');
    }

    $template_name = $_POST['template_name'];
    $template_description = $_POST['template_description'] ?? '';

    // --- File Handling ---
    $upload_base = APP_ROOT . '/uploads/';
    $thumb_dir = 'uploads/templates/thumbnails/';
    $file_dir = 'uploads/templates/files/';

    // Create directories if they don't exist
    if (!is_dir($upload_base . 'templates/thumbnails')) mkdir($upload_base . 'templates/thumbnails', 0777, true);
    if (!is_dir($upload_base . 'templates/files')) mkdir($upload_base . 'templates/files', 0777, true);

    // Sanitize filenames and create unique paths
    $thumb_ext = pathinfo($_FILES['template_thumbnail']['name'], PATHINFO_EXTENSION);
    $file_ext = pathinfo($_FILES['template_file']['name'], PATHINFO_EXTENSION);
    $base_name = time() . '_' . preg_replace('/[^a-z0-9_]/i', '_', strtolower($template_name));

    $thumb_path = $thumb_dir . $base_name . '.' . $thumb_ext;
    $file_path = $file_dir . $base_name . '.' . $file_ext;

    $thumb_target = $upload_base . '../' . $thumb_path;
    $file_target = $upload_base . '../' . $file_path;

    // Move uploaded files
    if (!move_uploaded_file($_FILES['template_thumbnail']['tmp_name'], $thumb_target)) {
        redirect_with_message('Failed to move thumbnail file.', 'error');
    }
    if (!move_uploaded_file($_FILES['template_file']['tmp_name'], $file_target)) {
        redirect_with_message('Failed to move template PHP file.', 'error');
    }

    // --- Database Insert ---
    try {
        $sql = "INSERT INTO report_templates (name, description, thumbnail_url, file_path) VALUES (:name, :description, :thumb_url, :file_path)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $template_name,
            ':description' => $template_description,
            ':thumb_url' => $thumb_path,
            ':file_path' => $file_path
        ]);
        redirect_with_message('Template uploaded successfully.', 'success');
    } catch (PDOException $e) {
        // Clean up uploaded files on DB error
        if (file_exists($thumb_target)) unlink($thumb_target);
        if (file_exists($file_target)) unlink($file_target);
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_delete($pdo) {
    $template_id = $_GET['id'] ?? 0;
    if (!$template_id) {
        redirect_with_message('Invalid template ID.', 'error');
    }

    try {
        // Get file paths before deleting the record
        $stmt = $pdo->prepare("SELECT thumbnail_url, file_path FROM report_templates WHERE id = :id");
        $stmt->execute([':id' => $template_id]);
        $paths = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($paths) {
            // Delete the record from the database
            $delete_stmt = $pdo->prepare("DELETE FROM report_templates WHERE id = :id");
            $delete_stmt->execute([':id' => $template_id]);

            // Delete the physical files
            $thumb_target = APP_ROOT . '/' . $paths['thumbnail_url'];
            $file_target = APP_ROOT . '/' . $paths['file_path'];
            if (file_exists($thumb_target)) unlink($thumb_target);
            if (file_exists($file_target)) unlink($file_target);
        }

        redirect_with_message('Template deleted successfully.', 'success');

    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}


function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/super_admin/report_templates.php');
    exit;
}
?>