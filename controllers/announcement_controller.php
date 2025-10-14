<?php
/**
 * announcement_controller.php - Handles creating and deleting announcements.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// This controller can be used by both Super Admins and School Admins,
// so we'll check roles on a per-action basis.

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create_platform_announcement':
        require_auth('super_admin');
        handle_create_announcement($pdo, 'platform');
        break;
    case 'create_school_announcement':
        require_auth('school_admin');
        handle_create_announcement($pdo, 'school');
        break;
    case 'delete':
        // A simple check to ensure only the creator can delete, or a super admin.
        handle_delete($pdo);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error', '/views/super_admin/dashboard.php');
}

/**
 * Handles creating a new announcement.
 *
 * @param PDO $pdo The database connection object.
 * @param string $type The type of announcement ('platform' or 'school').
 */
function handle_create_announcement($pdo, $type) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $redirect_path = ($type === 'platform') ? '/views/super_admin/announcements.php' : '/views/school_admin/announcements.php';

    if (empty($title) || empty($content)) {
        redirect_with_message('Title and content are required.', 'error', $redirect_path);
    }

    try {
        $sql = "INSERT INTO announcements (user_id, school_id, title, content, audience) VALUES (:user_id, :school_id, :title, :content, :audience)";
        $stmt = $pdo->prepare($sql);

        if ($type === 'platform') {
            $params = [
                ':user_id' => $_SESSION['user_id'],
                ':school_id' => null,
                ':title' => $title,
                ':content' => $content,
                ':audience' => 'all_schools'
            ];
        } else { // 'school' type
            $params = [
                ':user_id' => $_SESSION['user_id'],
                ':school_id' => $_SESSION['school_id'],
                ':title' => $title,
                ':content' => $content,
                ':audience' => 'all_teachers'
            ];
        }

        $stmt->execute($params);
        redirect_with_message('Announcement posted successfully.', 'success', $redirect_path);

    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error', $redirect_path);
    }
}

/**
 * Handles deleting an announcement.
 */
function handle_delete($pdo) {
    $announcement_id = $_GET['id'] ?? 0;
    $redirect_path = ($_SESSION['user_role'] === 'super_admin') ? '/views/super_admin/announcements.php' : '/views/school_admin/announcements.php';

    if (!$announcement_id) {
        redirect_with_message('Invalid announcement ID.', 'error', $redirect_path);
    }

    try {
        // Security check: ensure the user trying to delete is the one who created it,
        // or is a super admin. A school admin can delete any announcement from their own school.
        $stmt = $pdo->prepare("SELECT user_id, school_id FROM announcements WHERE id = :id");
        $stmt->execute([':id' => $announcement_id]);
        $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

        $is_creator = $announcement && $announcement['user_id'] == $_SESSION['user_id'];
        $is_super_admin = $_SESSION['user_role'] === 'super_admin';
        $is_school_admin_of_school = $_SESSION['user_role'] === 'school_admin' && $announcement['school_id'] == $_SESSION['school_id'];

        if ($is_creator || $is_super_admin || $is_school_admin_of_school) {
            $delete_stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
            $delete_stmt->execute([':id' => $announcement_id]);
            redirect_with_message('Announcement deleted successfully.', 'success', $redirect_path);
        } else {
            redirect_with_message('You do not have permission to delete this announcement.', 'error', $redirect_path);
        }
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error', $redirect_path);
    }
}


/**
 * Redirects to a specified page with a session message.
 */
function redirect_with_message($message, $type, $path) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: {$path}");
    exit;
}
?>