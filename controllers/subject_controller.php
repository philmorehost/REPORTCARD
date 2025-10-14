<?php
/**
 * subject_controller.php - Handles all CRUD operations for subjects.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only school admins can execute these actions
require_auth('school_admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$school_id = $_SESSION['school_id'];

switch ($action) {
    case 'create_bulk':
        handle_create_bulk_subjects($pdo, $school_id);
        break;
    case 'assign_teachers':
        handle_assign_teachers($pdo, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_create_bulk_subjects($pdo, $school_id) {
    $subject_names_raw = $_POST['subject_names'] ?? '';
    if (empty($subject_names_raw)) {
        redirect_with_message('No subject names provided.', 'error');
    }

    $subject_names = array_filter(array_map('trim', explode("\n", $subject_names_raw)));

    if (empty($subject_names)) {
        redirect_with_message('No subject names provided.', 'error');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO subjects (school_id, subject_name) VALUES (:school_id, :subject_name)");
        foreach ($subject_names as $name) {
            $stmt->execute(['school_id' => $school_id, 'subject_name' => $name]);
        }
        $pdo->commit();
        redirect_with_message('Subjects created successfully.', 'success');
    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_assign_teachers($pdo, $school_id) {
    $subject_id = $_POST['subject_id'] ?? 0;
    $teacher_ids = $_POST['teacher_ids'] ?? [];

    if (empty($subject_id) || empty($teacher_ids)) {
        redirect_with_message('Subject and teachers must be selected.', 'error');
    }

    // Security check: ensure the subject belongs to the school
    $stmt_check = $pdo->prepare("SELECT id FROM subjects WHERE id = :subject_id AND school_id = :school_id");
    $stmt_check->execute(['subject_id' => $subject_id, 'school_id' => $school_id]);
    if (!$stmt_check->fetch()) {
        redirect_with_message('Invalid subject selected.', 'error');
    }

    $pdo->beginTransaction();
    try {
        // First, clear existing assignments for this subject
        $stmt_delete = $pdo->prepare("DELETE FROM subject_teacher_assignments WHERE subject_id = :subject_id");
        $stmt_delete->execute(['subject_id' => $subject_id]);

        // Then, insert the new assignments
        $stmt_insert = $pdo->prepare("INSERT INTO subject_teacher_assignments (subject_id, teacher_id) VALUES (:subject_id, :teacher_id)");
        foreach ($teacher_ids as $teacher_id) {
            $stmt_insert->execute(['subject_id' => $subject_id, 'teacher_id' => $teacher_id]);
        }

        $pdo->commit();
        redirect_with_message('Teachers assigned successfully.', 'success');
    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function redirect_with_message($message, $type, $location = '/views/school_admin/subject_management.php') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: " . $location);
    exit;
}
?>
