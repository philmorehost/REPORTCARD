<?php
/**
 * teacher_settings_controller.php - Handles saving teacher-specific settings.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only teachers can execute these actions
require_auth('teacher');

$action = $_POST['action'] ?? '';
$teacher_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];

switch ($action) {
    case 'save_comments':
        handle_save_comments($pdo, $teacher_id, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error', 'settings.php');
}

/**
 * Saves the teacher's personal comment bank.
 */
function handle_save_comments($pdo, $teacher_id, $school_id) {
    $teacher_comments_raw = $_POST['teacher_comments'] ?? '';
    // Split comments by newline, trim whitespace, and remove any empty lines
    $teacher_comments = array_filter(array_map('trim', explode("\n", $teacher_comments_raw)));

    $pdo->beginTransaction();
    try {
        // 1. Delete all existing 'teacher_general' comments for this specific user
        $stmt_delete = $pdo->prepare("DELETE FROM report_comments WHERE user_id = :user_id AND comment_type = 'teacher_general'");
        $stmt_delete->execute([':user_id' => $teacher_id]);

        // 2. Insert the new comments
        if (!empty($teacher_comments)) {
            $sql_insert = "INSERT INTO report_comments (school_id, user_id, comment_type, comment_text) VALUES (:school_id, :user_id, 'teacher_general', :comment_text)";
            $stmt_insert = $pdo->prepare($sql_insert);

            foreach ($teacher_comments as $comment) {
                $stmt_insert->execute([
                    ':school_id' => $school_id,
                    ':user_id' => $teacher_id,
                    ':comment_text' => $comment
                ]);
            }
        }

        $pdo->commit();
        redirect_with_message('Your comment bank has been saved successfully.', 'success', 'settings.php');

    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('A database error occurred: ' . $e->getMessage(), 'error', 'settings.php');
    }
}

/**
 * Redirects to a specified teacher page with a session message.
 */
function redirect_with_message($message, $type, $page) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: /views/teacher/{$page}");
    exit;
}
?>