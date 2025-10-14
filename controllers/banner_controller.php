<?php
/**
 * banner_controller.php - Handles CRUD operations for banner ads.
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
    case 'create_banner':
        handle_create_banner($pdo);
        break;
    case 'delete_banner':
        handle_delete_banner($pdo);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_create_banner($pdo) {
    // 1. Validate input
    if (empty($_FILES['banner_image']) || $_FILES['banner_image']['error'] !== UPLOAD_ERR_OK || empty($_POST['target_link'])) {
        redirect_with_message('Banner image and target link are required.', 'error');
    }

    $target_link = filter_var($_POST['target_link'], FILTER_VALIDATE_URL);
    if (!$target_link) {
        redirect_with_message('Invalid target link URL format.', 'error');
    }

    $expires_at = !empty($_POST['expires_at']) ? date('Y-m-d', strtotime($_POST['expires_at'])) : null;

    // 2. Handle file upload
    $upload_dir = 'uploads/banners/';
    if (!is_dir(APP_ROOT . '/' . $upload_dir)) {
        mkdir(APP_ROOT . '/' . $upload_dir, 0777, true);
    }
    $file_name = 'banner_' . time() . '_' . basename($_FILES['banner_image']['name']);
    $target_file = APP_ROOT . '/' . $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES['banner_image']['tmp_name'], $target_file)) {
        redirect_with_message('Failed to upload banner image.', 'error');
    }

    $image_url = $upload_dir . $file_name;

    // 3. Insert into database
    try {
        $sql = "INSERT INTO banner_ads (image_url, target_link, expires_at) VALUES (:image_url, :target_link, :expires_at)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':image_url' => $image_url,
            ':target_link' => $target_link,
            ':expires_at' => $expires_at
        ]);

        redirect_with_message('Banner ad created successfully.', 'success');
    } catch (PDOException $e) {
        // Clean up uploaded file if DB insert fails
        if (file_exists($target_file)) {
            unlink($target_file);
        }
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_delete_banner($pdo) {
    if (empty($_POST['banner_id'])) {
        redirect_with_message('Banner ID is missing.', 'error');
    }

    $banner_id = $_POST['banner_id'];

    try {
        // First, get the image_url to delete the file
        $stmt_get = $pdo->prepare("SELECT image_url FROM banner_ads WHERE id = :id");
        $stmt_get->execute([':id' => $banner_id]);
        $image_url = $stmt_get->fetchColumn();

        // Then, delete the record from the database
        $stmt_del = $pdo->prepare("DELETE FROM banner_ads WHERE id = :id");
        $stmt_del->execute([':id' => $banner_id]);

        // If deletion was successful and an image exists, delete the file
        if ($stmt_del->rowCount() > 0 && $image_url && file_exists(APP_ROOT . '/' . $image_url)) {
            unlink(APP_ROOT . '/' . $image_url);
        }

        redirect_with_message('Banner ad deleted successfully.', 'success');
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}


function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/super_admin/banner_management.php');
    exit;
}
?>
