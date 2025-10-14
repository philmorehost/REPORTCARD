<?php
/**
 * sender_id_controller.php - Handles Sender ID registration and status checks with PhilmoreSMS.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only school admins can execute these actions
require_auth('school_admin');

$action = $_POST['action'] ?? '';
$school_id = $_SESSION['school_id'];

// --- API Configuration ---
$api_token = s_get($pdo, 'sms_api_key');
if (empty($api_token)) {
    redirect_with_message('The SMS API has not been configured by the super administrator.', 'error');
}

switch ($action) {
    case 'register_sender_id':
        handle_register_sender_id($pdo, $api_token, $school_id);
        break;
    case 'check_sender_id_status':
        handle_check_status($pdo, $api_token);
        break;
    default:
        redirect_with_message('Invalid action specified.', 'error');
}

/**
 * Handles the registration of a new Sender ID.
 */
function handle_register_sender_id($pdo, $api_token, $school_id) {
    $sender_id = trim($_POST['sender_id'] ?? '');
    $sample_message = trim($_POST['sample_message'] ?? '');

    if (empty($sender_id) || empty($sample_message)) {
        redirect_with_message('Sender ID and a sample message are required.', 'error');
    }
    if (strlen($sender_id) > 11) {
        redirect_with_message('Sender ID cannot be more than 11 characters.', 'error');
    }

    $post_data = http_build_query([
        'token' => $api_token,
        'senderID' => $sender_id,
        'message' => $sample_message
    ]);

    $response = make_api_request('https://app.philmoresms.com/api/senderID.php', $post_data);

    if ($response && $response['status'] === 'success' && $response['error_code'] === '000') {
        // If submission is successful, store the pending sender ID for the school
        try {
            $stmt = $pdo->prepare("UPDATE schools SET sender_id = :sender_id WHERE id = :id");
            $stmt->execute(['sender_id' => $sender_id, 'id' => $school_id]);
        } catch (PDOException $e) {
            // Non-fatal error, as the main action (API call) succeeded.
            error_log("Could not save pending sender ID for school {$school_id}: " . $e->getMessage());
        }
        redirect_with_message('Sender ID submitted successfully! It is now pending review.', 'success');
    } else {
        $error_message = $response['message'] ?? 'An unknown error occurred.';
        redirect_with_message("API Error: " . $error_message, 'error');
    }
}

/**
 * Handles checking the status of a Sender ID.
 */
function handle_check_status($pdo, $api_token) {
    $sender_id = trim($_POST['sender_id'] ?? '');

    if (empty($sender_id)) {
        redirect_with_message('Sender ID is required to check its status.', 'error');
    }

    $post_data = http_build_query([
        'token' => $api_token,
        'senderID' => $sender_id
    ]);

    $response = make_api_request('https://app.philmoresms.com/api/check_senderID.php', $post_data);

    if ($response && $response['status'] === 'success' && $response['error_code'] === '000') {
        $status_message = "Status for '{$sender_id}': <strong>" . htmlspecialchars(ucfirst($response['data']['status'])) . "</strong>.";
        redirect_with_message($status_message, 'success');
    } else {
        $error_message = $response['message'] ?? 'An unknown error occurred.';
        redirect_with_message("API Error: " . $error_message, 'error');
    }
}

/**
 * A helper function to make cURL requests to the API.
 */
function make_api_request($url, $post_data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $response_body = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        redirect_with_message('cURL Error: ' . $curl_error, 'error');
    }

    return json_decode($response_body, true);
}

/**
 * Redirects back to the sender ID page with a message.
 */
function redirect_with_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: /views/school_admin/sender_id.php");
    exit;
}
?>