<?php
/**
 * teacher_controller.php - Handles CRUD operations for teachers.
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
        handle_create_teacher($pdo, $school_id);
        break;
    case 'update':
        handle_update_teacher($pdo, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_create_teacher($pdo, $school_id) {
    if (empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['password'])) {
        redirect_with_message('Full name, email, and password are required.', 'error');
    }

    $full_name = $_POST['full_name'];
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if (!$email) {
        redirect_with_message('Invalid email format provided.', 'error');
    }

    try {
        $sql = "INSERT INTO users (school_id, full_name, email, password, role, status)
                VALUES (:school_id, :full_name, :email, :password, 'teacher', 'active')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':school_id' => $school_id,
            ':full_name' => $full_name,
            ':email' => $email,
            ':password' => $password
        ]);

        redirect_with_message('Teacher account created successfully.', 'success');

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Duplicate email
            redirect_with_message('A user with this email address already exists in the system.', 'error');
        }
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_update_teacher($pdo, $school_id) {
    if (empty($_POST['user_id']) || empty($_POST['full_name']) || empty($_POST['email'])) {
        redirect_with_message('Missing required fields for update.', 'error');
    }

    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        redirect_with_message('Invalid email format provided.', 'error');
    }

    try {
        // Update name and email
        $sql = "UPDATE users SET full_name = :full_name, email = :email WHERE id = :id AND school_id = :school_id AND role = 'teacher'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':id' => $user_id,
            ':school_id' => $school_id
        ]);

        // Optionally update password if provided
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id AND school_id = :school_id");
            $stmt->execute([':password' => $password, ':id' => $user_id, ':school_id' => $school_id]);
        }

        redirect_with_message('Teacher account updated successfully.', 'success');

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Duplicate email
            redirect_with_message('A user with this email address already exists in the system.', 'error');
        }
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

/**
 * Redirects back to the teacher management page with a session message.
 */
function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/school_admin/teacher_management.php');
    exit;
}
?>