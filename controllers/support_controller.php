<?php
/**
 * support_controller.php - Handles support ticket creation and replies.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';
require_once __DIR__ . '/../core/email.php';

// Users must be logged in to access this controller
require_auth();

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'] ?? null; // School ID might not be set for super admin
$user_role = $_SESSION['role'];

switch ($action) {
    case 'create_ticket':
        if ($user_role !== 'school_admin') {
            redirect_with_message('You are not authorized to perform this action.', 'error', '/views/dashboard.php');
        }
        handle_create_ticket($pdo, $user_id, $school_id);
        break;
    case 'add_reply':
        handle_add_reply($pdo, $user_id, $user_role);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error', '/views/dashboard.php');
}

function handle_create_ticket($pdo, $user_id, $school_id) {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject) || empty($message)) {
        redirect_with_message('Subject and message cannot be empty.', 'error', '/views/school_admin/create_ticket.php');
    }

    $pdo->beginTransaction();
    try {
        // 1. Create the ticket
        $stmt_ticket = $pdo->prepare("INSERT INTO support_tickets (school_id, user_id, subject) VALUES (:school_id, :user_id, :subject)");
        $stmt_ticket->execute(['school_id' => $school_id, 'user_id' => $user_id, 'subject' => $subject]);
        $ticket_id = $pdo->lastInsertId();

        // 2. Add the initial message as the first reply
        $stmt_reply = $pdo->prepare("INSERT INTO support_ticket_replies (ticket_id, user_id, message) VALUES (:ticket_id, :user_id, :message)");
        $stmt_reply->execute(['ticket_id' => $ticket_id, 'user_id' => $user_id, 'message' => $message]);

        // 3. Email Notification to Super Admin
        $super_admin_email = s_get($pdo, 'super_admin_email');
        if ($super_admin_email) {
            $school_name = $_SESSION['school_name'] ?? 'A school';
            $email_subject = "New Support Ticket #{$ticket_id}: {$subject}";
            $email_body = "A new support ticket has been created by {$school_name}.<br><br><strong>Subject:</strong> {$subject}<br><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message));
            send_email($pdo, $super_admin_email, $email_subject, $email_body);
        }

        $pdo->commit();
        redirect_with_message('Support ticket created successfully.', 'success', '/views/school_admin/view_ticket.php?id=' . $ticket_id);

    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database Error: ' . $e->getMessage(), 'error', '/views/school_admin/create_ticket.php');
    }
}

function handle_add_reply($pdo, $user_id, $user_role) {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    $message = trim($_POST['message'] ?? '');
    $new_status = $_POST['status'] ?? null;
    $redirect_path = ($user_role === 'super_admin') ? '/views/super_admin/view_ticket.php?id=' . $ticket_id : '/views/school_admin/view_ticket.php?id=' . $ticket_id;

    if ((empty($message) && $user_role === 'school_admin') || !$ticket_id) {
        redirect_with_message('Message cannot be empty.', 'error', $redirect_path);
    }
    if ($user_role === 'super_admin' && empty($message) && !$new_status) {
        redirect_with_message('You must provide a reply or change the status.', 'error', $redirect_path);
    }

    $pdo->beginTransaction();
    try {
        // 1. Verify the user has permission to reply to this ticket
        $stmt_verify = $pdo->prepare("SELECT school_id, user_id, subject, status FROM support_tickets WHERE id = :id");
        $stmt_verify->execute(['id' => $ticket_id]);
        $ticket = $stmt_verify->fetch(PDO::FETCH_ASSOC);

        if (!$ticket || ($user_role === 'school_admin' && $ticket['school_id'] !== $_SESSION['school_id'])) {
            redirect_with_message('You do not have permission to reply to this ticket.', 'error', $redirect_path);
        }

        // 2. Insert the reply if a message was provided
        if (!empty($message)) {
            $stmt_reply = $pdo->prepare("INSERT INTO support_ticket_replies (ticket_id, user_id, message) VALUES (:ticket_id, :user_id, :message)");
            $stmt_reply->execute(['ticket_id' => $ticket_id, 'user_id' => $user_id, 'message' => $message]);
        }

        // 3. Update the ticket's `updated_at` timestamp and status
        $status_changed = false;
        $sql_parts = ["updated_at = CURRENT_TIMESTAMP"];
        $params = ['id' => $ticket_id];

        if ($user_role === 'super_admin' && $new_status && in_array($new_status, ['open', 'in_progress', 'closed'])) {
            $sql_parts[] = "status = :status";
            $params['status'] = $new_status;
            if ($new_status !== $ticket['status']) {
                $status_changed = true;
            }
        } elseif ($user_role === 'school_admin' && $ticket['status'] === 'closed') {
            // A school admin's reply should reopen a closed ticket
            $sql_parts[] = "status = 'open'";
            $status_changed = true;
        }

        $stmt_update = $pdo->prepare("UPDATE support_tickets SET " . implode(', ', $sql_parts) . " WHERE id = :id");
        $stmt_update->execute($params);


        // 4. Email Notification Logic
        if ($user_role === 'super_admin') {
            // Notify the school admin who created the ticket
            $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = :id");
            $stmt_user->execute(['id' => $ticket['user_id']]);
            $recipient_email = $stmt_user->fetchColumn();
            $email_subject = "Re: Support Ticket #{$ticket_id}: {$ticket['subject']}";
            $email_body = "A reply has been posted to your support ticket.<br><br><strong>Reply:</strong><br>" . nl2br(htmlspecialchars($message));
        } else {
            // Notify the super admin
            $recipient_email = s_get($pdo, 'super_admin_email');
            $school_name = $_SESSION['school_name'] ?? 'A school';
            $email_subject = "Re: Support Ticket #{$ticket_id}: {$ticket['subject']}";
            $email_body = "A new reply has been posted by {$school_name} on a support ticket.<br><br><strong>Reply:</strong><br>" . nl2br(htmlspecialchars($message));
        }

        if ($recipient_email) {
            send_email($pdo, $recipient_email, $email_subject, $email_body);
        }

        $pdo->commit();
        redirect_with_message('Reply added successfully.', 'success', $redirect_path);

    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('Database Error: ' . $e->getMessage(), 'error', $redirect_path);
    }
}


/**
 * Redirects to a specified page with a session message.
 */
function redirect_with_message($message, $type, $location) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: {$location}");
    exit;
}

?>
