<?php
/**
 * class_controller.php - Handles CRUD and assignment operations for classes.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only school admins can execute these actions
require_auth('school_admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$school_id = $_SESSION['school_id']; // All operations are scoped to the admin's school

switch ($action) {
    case 'create':
        handle_create_class($pdo, $school_id);
        break;
    case 'assign_teacher':
        handle_assign_teacher($pdo, $school_id);
        break;
    case 'unassign_teacher':
        handle_unassign_teacher($pdo, $school_id);
        break;
    case 'update_enrollments':
        handle_update_enrollments($pdo, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error', 'class_management.php');
}

function handle_create_class($pdo, $school_id) {
    if (empty($_POST['class_name']) || empty($_POST['academic_period'])) {
        redirect_with_message('Class name and academic period are required.', 'error', 'class_management.php');
    }

    try {
        $sql = "INSERT INTO classes (school_id, class_name, academic_period) VALUES (:school_id, :class_name, :academic_period)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':school_id' => $school_id,
            ':class_name' => $_POST['class_name'],
            ':academic_period' => $_POST['academic_period']
        ]);
        redirect_with_message('Class/Subject created successfully.', 'success', 'class_management.php');
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error', 'class_management.php');
    }
}

function handle_assign_teacher($pdo, $school_id) {
    $class_id = $_POST['class_id'] ?? 0;
    $teacher_id = $_POST['teacher_id'] ?? 0;

    if (!$class_id || !$teacher_id) {
        redirect_with_message('Missing class or teacher ID.', 'error', "manage_assignments.php?class_id=$class_id");
    }

    try {
        // First, remove any existing assignment for this class to prevent conflicts
        $stmt = $pdo->prepare("DELETE FROM teacher_assignments WHERE class_id = :class_id");
        $stmt->execute([':class_id' => $class_id]);

        // Then, create the new assignment
        $stmt = $pdo->prepare("INSERT INTO teacher_assignments (teacher_id, class_id) VALUES (:teacher_id, :class_id)");
        $stmt->execute([':teacher_id' => $teacher_id, ':class_id' => $class_id]);

        redirect_with_message('Teacher assigned successfully.', 'success', "manage_assignments.php?class_id=$class_id");
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error', "manage_assignments.php?class_id=$class_id");
    }
}

function handle_unassign_teacher($pdo, $school_id) {
    $class_id = $_GET['class_id'] ?? 0;
    $teacher_id = $_GET['teacher_id'] ?? 0;

    if (!$class_id || !$teacher_id) {
        redirect_with_message('Missing class or teacher ID.', 'error', "manage_assignments.php?class_id=$class_id");
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM teacher_assignments WHERE class_id = :class_id AND teacher_id = :teacher_id");
        $stmt->execute([':class_id' => $class_id, ':teacher_id' => $teacher_id]);
        redirect_with_message('Teacher unassigned successfully.', 'success', "manage_assignments.php?class_id=$class_id");
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error', "manage_assignments.php?class_id=$class_id");
    }
}

function handle_update_enrollments($pdo, $school_id) {
    $class_id = $_POST['class_id'] ?? 0;
    // Ensure the submitted list contains unique, integer IDs
    $new_student_ids = array_unique(array_map('intval', $_POST['enrolled_students'] ?? []));

    if (!$class_id) {
        redirect_with_message('Missing class ID.', 'error', 'class_management.php');
    }

    $redirect_url = "manage_assignments.php?class_id=$class_id";

    $pdo->beginTransaction();
    try {
        // 1. Get current student enrollments for the class
        $stmt_current = $pdo->prepare("SELECT student_id FROM enrollments WHERE class_id = :class_id");
        $stmt_current->execute([':class_id' => $class_id]);
        $current_student_ids = $stmt_current->fetchAll(PDO::FETCH_COLUMN, 0);

        // 2. Calculate differences
        $students_to_add = array_diff($new_student_ids, $current_student_ids);
        $students_to_remove = array_diff($current_student_ids, $new_student_ids);

        // 3. Add new students
        if (!empty($students_to_add)) {
            $sql_add = "INSERT INTO enrollments (class_id, student_id) VALUES ";
            $values_add = [];
            foreach ($students_to_add as $student_id) {
                $values_add[] = "(:class_id, :student_id_" . $student_id . ")";
            }
            $stmt_add = $pdo->prepare($sql_add . implode(', ', $values_add));
            $stmt_add->bindValue(':class_id', $class_id, PDO::PARAM_INT);
            foreach ($students_to_add as $student_id) {
                $stmt_add->bindValue(":student_id_" . $student_id, $student_id, PDO::PARAM_INT);
            }
            $stmt_add->execute();
        }

        // 4. Remove students who are no longer enrolled
        if (!empty($students_to_remove)) {
            // Using a placeholder for each ID to be safe
            $placeholders = implode(',', array_fill(0, count($students_to_remove), '?'));
            $sql_remove = "DELETE FROM enrollments WHERE class_id = ? AND student_id IN ($placeholders)";
            $stmt_remove = $pdo->prepare($sql_remove);
            // Bind class_id first, then all the student IDs
            $params = array_merge([$class_id], $students_to_remove);
            $stmt_remove->execute($params);
        }

        $pdo->commit();
        redirect_with_message('Student enrollments updated successfully.', 'success', $redirect_url);
    } catch (PDOException $e) {
        $pdo->rollBack();
        // Provide a more specific error message for debugging
        error_log("Enrollment update failed: " . $e->getMessage());
        redirect_with_message('Database error during enrollment update. Please check system logs.', 'error', $redirect_url);
    }
}

function redirect_with_message($message, $type, $page) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: /views/school_admin/{$page}");
    exit;
}
?>