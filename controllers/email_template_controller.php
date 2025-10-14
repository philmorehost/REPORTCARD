<?php
/**
 * email_template_controller.php - Handles CRUD for school-specific email templates.
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
    case 'create':
        handle_create($pdo, $school_id);
        break;
    case 'update':
        handle_update($pdo, $school_id);
        break;
    case 'delete':
        handle_delete($pdo, $school_id);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_create($pdo, $school_id) {
    if (empty($_POST['name']) || empty($_POST['subject']) || empty($_POST['body'])) {
        redirect_with_message('Template name, subject, and body are required.', 'error');
    }
    try {
        $sql = "INSERT INTO email_templates (school_id, name, subject, body) VALUES (:school_id, :name, :subject, :body)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':school_id' => $school_id,
            ':name' => $_POST['name'],
            ':subject' => $_POST['subject'],
            ':body' => $_POST['body']
        ]);
        redirect_with_message('Email template created successfully.', 'success');
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_update($pdo, $school_id) {
    if (empty($_POST['template_id']) || empty($_POST['name']) || empty($_POST['subject']) || empty($_POST['body'])) {
        redirect_with_message('Missing required fields for update.', 'error');
    }
    try {
        $sql = "UPDATE email_templates SET name = :name, subject = :subject, body = :body WHERE id = :id AND school_id = :school_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $_POST['name'],
            ':subject' => $_POST['subject'],
            ':body' => $_POST['body'],
            ':id' => $_POST['template_id'],
            ':school_id' => $school_id
        ]);
        redirect_with_message('Email template updated successfully.', 'success');
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_delete($pdo, $school_id) {
    if (empty($_GET['id'])) {
        redirect_with_message('Template ID is missing.', 'error');
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM email_templates WHERE id = :id AND school_id = :school_id");
        $stmt->execute([':id' => $_GET['id'], ':school_id' => $school_id]);
        redirect_with_message('Email template deleted successfully.', 'success');
    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: /views/school_admin/email_templates.php');
    exit;
}
?>