<?php
/**
 * school_controller.php - Handles all CRUD operations for schools.
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
    case 'create':
        handle_create_school($pdo);
        break;
    case 'update':
        handle_update_school($pdo);
        break;
    case 'toggle_status':
        handle_toggle_status($pdo);
        break;
    case 'add_credits':
        handle_add_credits($pdo);
        break;
    case 'credit_sms':
        handle_credit_sms($pdo);
        break;
    case 'delete_school':
        handle_delete_school($pdo);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

function handle_credit_sms($pdo) {
    if (empty($_POST['school_id']) || empty($_POST['credits_to_add'])) {
        redirect_with_message('School and credit amount are required.', 'error', '/views/super_admin/credit_school.php');
    }

    $school_id = (int)$_POST['school_id'];
    $credits_to_add = (int)$_POST['credits_to_add'];
    $reference = !empty($_POST['transaction_reference']) ? trim($_POST['transaction_reference']) : 'manual-' . time();


    if ($credits_to_add <= 0) {
        redirect_with_message('Credits must be a positive number.', 'error', '/views/super_admin/credit_school.php');
    }

    $pdo->beginTransaction();
    try {
        // 1. Create a general payment transaction record for auditing
        $description = "Manual credit of " . number_format($credits_to_add) . " SMS units by Super Admin.";
        $stmt_payment = $pdo->prepare(
            "INSERT INTO payment_transactions (school_id, description, amount, payment_method, status, reference)
             VALUES (:sid, :desc, 0.00, 'Manual Credit', 'completed', :ref)"
        );
        $stmt_payment->execute([
            ':sid' => $school_id,
            ':desc' => $description,
            ':ref' => $reference
        ]);
        $payment_transaction_id = $pdo->lastInsertId();

        // 2. Create the specific SMS transaction record, linking it to the general payment
        $stmt_sms = $pdo->prepare(
            "INSERT INTO sms_transactions (school_id, payment_transaction_id, credits_purchased, amount, payment_method, status, reference)
             VALUES (:sid, :ptid, :credits, 0.00, 'Manual Credit', 'completed', :ref)"
        );
        $stmt_sms->execute([
            ':sid' => $school_id,
            ':ptid' => $payment_transaction_id,
            ':credits' => $credits_to_add,
            ':ref' => $reference . '_sms' // Make reference unique for this table
        ]);

        // 3. Add credits to the school's account
        $stmt_add = $pdo->prepare("UPDATE schools SET sms_credits = sms_credits + :credits WHERE id = :id");
        $stmt_add->execute(['credits' => $credits_to_add, 'id' => $school_id]);

        // Get admin email for notification
        $stmt_email = $pdo->prepare("SELECT u.email, u.full_name FROM users u WHERE u.school_id = :id AND u.role = 'school_admin' LIMIT 1");
        $stmt_email->execute(['id' => $school_id]);
        $admin_info = $stmt_email->fetch(PDO::FETCH_ASSOC);

        if ($admin_info) {
            $subject = "Your SMS Account Has Been Credited";
            $body = "
                <p>Hi " . htmlspecialchars($admin_info['full_name']) . ",</p>
                <p>This is a notification to confirm that your SMS account has been credited with " . number_format($credits_to_add) . " units by the super administrator.</p>
            ";
            send_email($pdo, $admin_info['email'], $subject, $body);
        }

        $pdo->commit();
        redirect_with_message(number_format($credits_to_add) . ' SMS credits added successfully.', 'success', '/views/super_admin/credit_school.php');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062) { // Unique constraint violation for reference
            redirect_with_message('Error: The transaction reference provided already exists.', 'error', '/views/super_admin/credit_school.php');
        } else {
            redirect_with_message('Database error: ' . $e->getMessage(), 'error', '/views/super_admin/credit_school.php');
        }
    }
}

function handle_create_school($pdo) {
    if (empty($_POST['school_name']) || empty($_POST['admin_name']) || empty($_POST['admin_email']) || empty($_POST['admin_password']) || empty($_POST['package_id'])) {
        redirect_with_message('All fields are required to create a new school.', 'error');
    }

    $school_name = $_POST['school_name'];
    $package_id = $_POST['package_id'];
    $admin_name = $_POST['admin_name'];
    $admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_VALIDATE_EMAIL);
    $admin_password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);

    if (!$admin_email) {
        redirect_with_message('Invalid email format provided.', 'error');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO schools (name, package_id) VALUES (:name, :package_id)");
        $stmt->execute(['name' => $school_name, 'package_id' => $package_id]);
        $school_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            "INSERT INTO users (school_id, email, password, full_name, role)
             VALUES (:school_id, :email, :password, :full_name, 'school_admin')"
        );
        $stmt->execute([
            'school_id' => $school_id,
            'email' => $admin_email,
            'password' => $admin_password,
            'full_name' => $admin_name
        ]);

        $pdo->commit();
        redirect_with_message('School and administrator created successfully.', 'success');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062) {
            redirect_with_message('An account with this email address already exists.', 'error');
        } else {
            redirect_with_message('Database error: ' . $e->getMessage(), 'error');
        }
    }
}

function handle_update_school($pdo) {
    if (empty($_POST['school_id']) || empty($_POST['user_id']) || empty($_POST['school_name']) || empty($_POST['admin_name']) || empty($_POST['admin_email'])) {
        redirect_with_message('Missing required fields for update.', 'error');
    }

    $school_id = $_POST['school_id'];
    $user_id = $_POST['user_id'];
    $school_name = $_POST['school_name'];
    $admin_name = $_POST['admin_name'];
    $admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_VALIDATE_EMAIL);

    if (!$admin_email) {
        redirect_with_message('Invalid email format provided.', 'error');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE schools SET name = :name WHERE id = :id");
        $stmt->execute(['name' => $school_name, 'id' => $school_id]);

        $stmt = $pdo->prepare("UPDATE users SET full_name = :full_name, email = :email WHERE id = :user_id AND role = 'school_admin'");
        $stmt->execute(['full_name' => $admin_name, 'email' => $admin_email, 'user_id' => $user_id]);

        if (!empty($_POST['admin_password'])) {
            $admin_password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $stmt->execute(['password' => $admin_password, 'user_id' => $user_id]);
        }

        $pdo->commit();
        redirect_with_message('School details updated successfully.', 'success');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062) {
            redirect_with_message('Cannot update. An account with the new email address already exists.', 'error');
        } else {
            redirect_with_message('Database error: ' . $e->getMessage(), 'error');
        }
    }
}

function handle_toggle_status($pdo) {
    if (empty($_GET['school_id'])) {
        redirect_with_message('School ID is missing.', 'error');
    }

    $school_id = $_GET['school_id'];

    try {
        $stmt = $pdo->prepare("SELECT s.status, u.email, u.full_name FROM schools s JOIN users u ON s.id = u.school_id WHERE s.id = :id AND u.role = 'school_admin' LIMIT 1");
        $stmt->execute(['id' => $school_id]);
        $school_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$school_info) {
            redirect_with_message('School or school admin not found.', 'error');
        }

        $new_status = ($school_info['status'] == 'active') ? 'suspended' : 'active';

        $update_stmt = $pdo->prepare("UPDATE schools SET status = :status WHERE id = :id");
        $update_stmt->execute(['status' => $new_status, 'id' => $school_id]);

        // Send email notification
        $subject = "Your Account has been " . ucfirst($new_status);
        $body = "
            <p>Hi " . htmlspecialchars($school_info['full_name']) . ",</p>
            <p>This is a notification that your school's account has been " . htmlspecialchars($new_status) . " by the super administrator.</p>
            <p>If you have any questions, please contact support.</p>
        ";
        send_email($pdo, $school_info['email'], $subject, $body);

        redirect_with_message("School status updated to '{$new_status}'.", 'success');

    } catch (PDOException $e) {
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_add_credits($pdo) {
    if (empty($_POST['school_id']) || empty($_POST['credit_amount'])) {
        redirect_with_message('School ID and credit amount are required.', 'error');
    }

    $school_id = (int)$_POST['school_id'];
    $credit_amount = (int)$_POST['credit_amount'];

    if ($credit_amount <= 0) {
        redirect_with_message('Credit amount must be a positive number.', 'error');
    }

    $pdo->beginTransaction();
    try {
        // Add credits to the school's account
        $stmt = $pdo->prepare("UPDATE schools SET sms_credits = sms_credits + :credits WHERE id = :id");
        $stmt->execute(['credits' => $credit_amount, 'id' => $school_id]);

        // Log the manual transaction
        $description = "Manual credit of " . number_format($credit_amount) . " SMS units by Super Admin.";
        $stmt_log = $pdo->prepare(
            "INSERT INTO payment_transactions (school_id, description, amount, payment_method, status, reference)
             VALUES (:school_id, :description, 0.00, 'Manual Credit', 'completed', :reference)"
        );
        $stmt_log->execute([
            ':school_id' => $school_id,
            ':description' => $description,
            ':reference' => 'manual-' . time() . '-' . $school_id
        ]);

        $pdo->commit();
        redirect_with_message(number_format($credit_amount) . ' credits added successfully.', 'success');
    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function handle_delete_school($pdo) {
    if (empty($_POST['school_id'])) {
        redirect_with_message('School ID is missing.', 'error');
    }

    $school_id = $_POST['school_id'];

    $pdo->beginTransaction();
    try {
        // Fetch admin email before deleting
        $stmt_email = $pdo->prepare("SELECT u.email, u.full_name FROM users u WHERE u.school_id = :id AND u.role = 'school_admin' LIMIT 1");
        $stmt_email->execute(['id' => $school_id]);
        $admin_info = $stmt_email->fetch(PDO::FETCH_ASSOC);

        // Since the database has ON DELETE CASCADE constraints, deleting the school will also delete related records in other tables.
        $stmt = $pdo->prepare("DELETE FROM schools WHERE id = :id");
        $stmt->execute(['id' => $school_id]);

        // Send email notification after successful deletion
        if ($admin_info) {
            $subject = "Your Account Has Been Permanently Deleted";
            $body = "
                <p>Hi " . htmlspecialchars($admin_info['full_name']) . ",</p>
                <p>This is a notification to confirm that your school's account and all associated data have been permanently deleted from our system by the super administrator.</p>
                <p>This action cannot be undone. If you believe this was in error, please contact our support team immediately.</p>
            ";
            send_email($pdo, $admin_info['email'], $subject, $body);
        }

        $pdo->commit();
        redirect_with_message('School and all associated data have been permanently deleted.', 'success');
    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database error: ' . $e->getMessage(), 'error');
    }
}

function redirect_with_message($message, $type, $location = '/views/super_admin/school_management.php') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: " . $location);
    exit;
}
?>