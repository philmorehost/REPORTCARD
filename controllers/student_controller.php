<?php
/**
 * student_controller.php - Handles CRUD operations for students.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';
require_once __DIR__ . '/../core/email.php';

// Ensure only school admins can execute these actions
require_auth('school_admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$school_id = $_SESSION['school_id']; // All operations are scoped to the admin's school

switch ($action) {
    case 'create':
        handle_create_student($pdo, $school_id);
        break;
    case 'update':
        handle_update_student($pdo, $school_id);
        break;
    case 'delete':
        handle_delete_student($pdo, $school_id);
        break;
    case 'bulk_import':
        handle_bulk_import($pdo, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_create_student($pdo, $school_id) {
    if (empty($_POST['full_name']) || empty($_POST['class'])) {
        redirect_with_message('Full name and class are required.', 'error');
    }
    try {
        // Check available slots and package type
        $stmt_check = $pdo->prepare(
            "SELECT
                (SELECT COUNT(id) FROM students WHERE school_id = :school_id AND status = 'active') as current_students,
                s.student_slots as total_slots,
                p.name as package_name,
                u.email as admin_email,
                u.full_name as admin_name
             FROM schools s
             LEFT JOIN packages p ON s.package_id = p.id
             JOIN users u ON s.id = u.school_id AND u.role = 'school_admin'
             WHERE s.id = :school_id"
        );
        $stmt_check->execute(['school_id' => $school_id]);
        $check_info = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($check_info && $check_info['current_students'] >= $check_info['total_slots']) {
            if (strtolower($check_info['package_name']) === 'freemium') {
                redirect_with_message('You have reached the student limit for your Freemium plan. Please upgrade to add more students.', 'error');
            } else {
                redirect_with_message('Cannot add new student. No available student slots. Please purchase more.', 'error');
            }
        }

        // Proceed with creating the student
        $sql = "INSERT INTO students (school_id, full_name, student_id_number, class, parent_name, parent_email, parent_phone_number) VALUES (:school_id, :full_name, :student_id, :class, :p_name, :p_email, :p_phone)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':school_id' => $school_id,
            ':full_name' => $_POST['full_name'],
            ':student_id' => $_POST['student_id_number'] ?? null,
            ':class' => $_POST['class'],
            ':p_name' => $_POST['parent_name'] ?? null,
            ':p_email' => $_POST['parent_email'] ?? null,
            ':p_phone' => $_POST['parent_phone_number'] ?? null
        ]);

        // Send notification email
        if ($check_info && $check_info['admin_email']) {
            $subject = "New Student Added: " . htmlspecialchars($_POST['full_name']);
            $body = "
                <p>Hi " . htmlspecialchars($check_info['admin_name']) . ",</p>
                <p>A new student, " . htmlspecialchars($_POST['full_name']) . ", has been added to your school.</p>
            ";
            send_email($pdo, $check_info['admin_email'], $subject, $body);
        }

        redirect_with_message('Student added successfully.', 'success');
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            redirect_with_message('A student with this ID number already exists.', 'error');
        }
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_update_student($pdo, $school_id) {
    if (empty($_POST['student_id']) || empty($_POST['full_name']) || empty($_POST['class'])) { redirect_with_message('Missing required fields for update.', 'error'); }
    try {
        $sql = "UPDATE students SET full_name = :full_name, student_id_number = :student_id_number, class = :class, parent_name = :p_name, parent_email = :p_email, parent_phone_number = :p_phone, status = :status WHERE id = :id AND school_id = :school_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':full_name' => $_POST['full_name'],
            ':student_id_number' => $_POST['student_id_number'],
            ':class' => $_POST['class'],
            ':p_name' => $_POST['parent_name'],
            ':p_email' => $_POST['parent_email'],
            ':p_phone' => $_POST['parent_phone_number'] ?? null,
            ':status' => $_POST['status'],
            ':id' => $_POST['student_id'],
            ':school_id' => $school_id
        ]);
        redirect_with_message('Student details updated successfully.', 'success');
    } catch (PDOException $e) {
         if ($e->errorInfo[1] == 1062) { redirect_with_message('A student with this ID number already exists.', 'error'); }
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_delete_student($pdo, $school_id) {
    if (empty($_GET['id'])) { redirect_with_message('Student ID is missing.', 'error'); }
    try {
        // Fetch student and admin info before deleting
        $stmt_info = $pdo->prepare("SELECT s.full_name as student_name, u.email as admin_email, u.full_name as admin_name FROM students s JOIN users u ON s.school_id = u.school_id WHERE s.id = :id AND s.school_id = :school_id AND u.role = 'school_admin' LIMIT 1");
        $stmt_info->execute([':id' => $_GET['id'], ':school_id' => $school_id]);
        $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id AND school_id = :school_id");
        $stmt->execute([':id' => $_GET['id'], ':school_id' => $school_id]);

        // Send notification email
        if ($info) {
            $subject = "Student Removed: " . htmlspecialchars($info['student_name']);
            $body = "
                <p>Hi " . htmlspecialchars($info['admin_name']) . ",</p>
                <p>The student, " . htmlspecialchars($info['student_name']) . ", has been removed from your school.</p>
            ";
            send_email($pdo, $info['admin_email'], $subject, $body);
        }

        redirect_with_message('Student deleted successfully.', 'success');
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_bulk_import($pdo, $school_id) {
    if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        redirect_with_message('File upload failed. Please try again.', 'error');
    }
    $file_path = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file_path, "r")) === FALSE) {
        redirect_with_message('Could not open the uploaded file.', 'error');
    }

    try {
        // Check available slots and package type
        $stmt_check = $pdo->prepare(
            "SELECT
                (SELECT COUNT(id) FROM students WHERE school_id = :school_id AND status = 'active') as current_students,
                s.student_slots as total_slots,
                p.name as package_name
             FROM schools s
             LEFT JOIN packages p ON s.package_id = p.id
             WHERE s.id = :school_id"
        );
        $stmt_check->execute(['school_id' => $school_id]);
        $check_info = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$check_info) {
            redirect_with_message('Could not verify school package information.', 'error');
        }

        $available_slots = $check_info['total_slots'] - $check_info['current_students'];

        if ($available_slots <= 0) {
            if (strtolower($check_info['package_name']) === 'freemium') {
                redirect_with_message('You have reached the student limit for your Freemium plan. Please upgrade to import more students.', 'error');
            } else {
                redirect_with_message('No available student slots. Please purchase more before importing.', 'error');
            }
        }
    } catch (PDOException $e) {
        redirect_with_message('Could not verify available slots: ' . $e->getMessage(), 'error');
    }

    $pdo->beginTransaction();
    $sql = "INSERT INTO students (school_id, full_name, student_id_number, class, parent_name, parent_email, parent_phone_number) VALUES (:school_id, :full_name, :student_id, :class, :p_name, :p_email, :p_phone)";
    $stmt = $pdo->prepare($sql);

    $imported_count = 0;
    $skipped_count = 0;
    fgetcsv($handle, 1000, ","); // Read and discard header row

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($available_slots <= 0) { $skipped_count++; continue; }
        // Check for mandatory fields (full_name and class)
        if (empty($data[0]) || empty($data[2])) { $skipped_count++; continue; }

        try {
            $stmt->execute([
                ':school_id' => $school_id,
                ':full_name' => $data[0],
                ':student_id' => $data[1] ?? null,
                ':class' => $data[2],
                ':p_name' => $data[3] ?? null,
                ':p_email' => $data[4] ?? null,
                ':p_phone' => $data[5] ?? null
            ]);
            $imported_count++;
            $available_slots--;
        } catch (PDOException $e) {
            $skipped_count++; // Skip duplicates or other errors
        }
    }
    fclose($handle);
    $pdo->commit();

    $message = "Successfully imported {$imported_count} students.";
    if ($skipped_count > 0) {
        $message .= " Skipped {$skipped_count} rows due to errors, duplicates, or lack of available slots.";
    }
    redirect_with_message($message, 'success');
}

function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/school_admin/student_management.php');
    exit;
}
?>