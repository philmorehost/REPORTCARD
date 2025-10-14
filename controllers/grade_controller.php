<?php
/**
 * grade_controller.php - Handles saving student grades.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';
require_once __DIR__ . '/../core/email.php';

// Ensure only teachers can execute these actions
require_auth('teacher');

$action = $_POST['action'] ?? '';
$teacher_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];

switch ($action) {
    case 'save_grades':
        handle_save_grades($pdo, $teacher_id, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error', 'dashboard.php');
}

/**
 * Handles saving or updating grades for multiple students in a class.
 *
 * @param PDO $pdo The database connection object.
 * @param int $teacher_id The ID of the logged-in teacher.
 * @param int $school_id The ID of the school.
 */
function handle_save_grades($pdo, $teacher_id, $school_id) {
    $class_id = $_POST['class_id'] ?? 0;
    $grades_data = $_POST['grades'] ?? [];

    if (!$class_id || empty($grades_data)) {
        redirect_with_message('No grades to save.', 'error', 'dashboard.php');
    }

    $redirect_url = "grade_entry.php?class_id=$class_id";

    // Verify that the teacher is actually assigned to this class before saving
    try {
        $stmt_verify = $pdo->prepare("SELECT 1 FROM teacher_assignments WHERE teacher_id = :teacher_id AND class_id = :class_id");
        $stmt_verify->execute(['teacher_id' => $teacher_id, 'class_id' => $class_id]);
        if ($stmt_verify->fetchColumn() === false) {
            throw new Exception("You are not authorized to save grades for this class.");
        }
    } catch (Exception $e) {
        redirect_with_message('Authorization error: ' . $e->getMessage(), 'error', $redirect_url);
    }

    $pdo->beginTransaction();
    try {
        $sql = "
            INSERT INTO grades (enrollment_id, grades)
            VALUES (:enrollment_id, :grades)
            ON DUPLICATE KEY UPDATE
                grades = VALUES(grades)
        ";
        $stmt = $pdo->prepare($sql);

        foreach ($grades_data as $enrollment_id => $grades) {
            // Basic sanitation: remove empty grade entries
            $filtered_grades = array_filter($grades, function($value) {
                return $value !== '' && $value !== null;
            });

            if (empty($filtered_grades)) {
                // If all grades for a student are empty, you might want to skip or delete the record.
                // For now, we'll just skip to avoid creating empty JSON objects.
                continue;
            }

            $stmt->execute([
                ':enrollment_id' => $enrollment_id,
                ':grades' => json_encode($filtered_grades)
            ]);
        }

        // Send notification email to school admin
        $stmt_admin = $pdo->prepare("SELECT email, full_name FROM users WHERE school_id = :school_id AND role = 'school_admin' LIMIT 1");
        $stmt_admin->execute(['school_id' => $school_id]);
        $admin_info = $stmt_admin->fetch(PDO::FETCH_ASSOC);

        if ($admin_info) {
            $teacher_name = $_SESSION['user_name'] ?? 'A teacher';
            $stmt_class = $pdo->prepare("SELECT class_name FROM classes WHERE id = :class_id");
            $stmt_class->execute(['class_id' => $class_id]);
            $class_name = $stmt_class->fetchColumn();

            $subject = "Grades Updated for " . htmlspecialchars($class_name);
            $body = "
                <p>Hi " . htmlspecialchars($admin_info['full_name']) . ",</p>
                <p>This is a notification to inform you that " . htmlspecialchars($teacher_name) . " has just updated the grades for the class: <strong>" . htmlspecialchars($class_name) . "</strong>.</p>
            ";
            send_email($pdo, $admin_info['email'], $subject, $body);
        }

        $pdo->commit();
        redirect_with_message('Grades saved successfully!', 'success', $redirect_url);

    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database error while saving grades: ' . $e->getMessage(), 'error', $redirect_url);
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