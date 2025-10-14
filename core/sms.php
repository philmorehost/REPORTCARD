<?php
/**
 * sms.php - Handles sending SMS messages using the configured provider.
 */

require_once __DIR__ . '/helpers.php';

/**
 * Sends an SMS message using the configured provider, deducts credits, and logs the attempt.
 *
 * NOTE: This function does NOT start its own transaction. It is designed
 * to be called from within a parent function that manages the transaction.
 *
 * @param PDO $pdo The database connection object.
 * @param int $school_id The ID of the school sending the message.
 * @param int|null $student_id The ID of the student (for logging purposes).
 * @param string $phone_number The recipient's phone number.
 * @param string $message The text of the message to be sent.
 * @return bool|string True on success, an error string on failure.
 */
function send_sms_message($pdo, $school_id, $student_id, $phone_number, $message) {
    // 1. Fetch settings and school's credit balance
    try {
        $settings = get_system_settings($pdo, [
            'sms_api_key', 'sms_sender_id', 'sms_credit_cost'
        ]);

        $stmt_school = $pdo->prepare("SELECT sms_credits, sender_id FROM schools WHERE id = :id");
        $stmt_school->execute(['id' => $school_id]);
        $school_data = $stmt_school->fetch(PDO::FETCH_ASSOC);
        $current_credits = (int)($school_data['sms_credits'] ?? 0);
        // A school-specific sender ID overrides the system one
        $sender_id = $school_data['sender_id'] ?? $settings['sms_sender_id'];

    } catch (PDOException $e) {
        error_log("SMS DB Error (pre-send): " . $e->getMessage());
        return "Database error while preparing to send message.";
    }

    // Sanitize the phone number by removing all non-numeric characters
    $sanitized_phone_number = preg_replace('/[^0-9]/', '', $phone_number);

    // 2. Validate prerequisites and log failure if they are not met
    $failure_reason = null;
    if (empty($settings['sms_api_key'])) {
        $failure_reason = "SMS API settings are not configured.";
    } elseif (empty($sender_id)) {
        $failure_reason = "Sender ID not configured for system or school.";
    } elseif ($current_credits < 1) {
        $failure_reason = "Insufficient SMS credits.";
    } elseif (empty($sanitized_phone_number) || !preg_match('/^[0-9]{10,15}$/', $sanitized_phone_number)) {
        $failure_reason = "Invalid phone number format.";
    }

    if ($failure_reason) {
        try {
            $stmt_log = $pdo->prepare(
                "INSERT INTO sms_log (school_id, student_id, phone_number, message, status, api_response, cost)
                 VALUES (:sid, :stid, :phone, :msg, 'failed', :reason, 0)"
            );
            $stmt_log->execute([
                ':sid' => $school_id,
                ':stid' => $student_id,
                ':phone' => $sanitized_phone_number,
                ':msg' => $message,
                ':reason' => $failure_reason
            ]);
        } catch (PDOException $e) {
            error_log("SMS DB Error (pre-send failure log): " . $e->getMessage());
        }
        return $failure_reason; // Return the reason for the failure
    }

    // 3. Log the initial attempt as 'pending'
    try {
        $stmt_log = $pdo->prepare(
            "INSERT INTO sms_log (school_id, student_id, phone_number, message, status, cost)
             VALUES (:sid, :stid, :phone, :msg, 'pending', :cost)"
        );
        $stmt_log->execute([
            ':sid' => $school_id,
            ':stid' => $student_id,
            ':phone' => $sanitized_phone_number,
            ':msg' => $message,
            ':cost' => (float)($settings['sms_credit_cost'] ?? 0)
        ]);
        $log_id = $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("SMS DB Error (log insert): " . $e->getMessage());
        return "Database error: Could not log the message attempt.";
    }

    // 4. Send the message via the PhilmoreSMS API
    $query_params = http_build_query([
        'token' => $settings['sms_api_key'],
        'senderID' => $sender_id,
        'recipients' => $sanitized_phone_number,
        'message' => $message
    ]);

    $url = 'https://app.philmoresms.com/api/sms.php?' . $query_params;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $response_body = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 5. Process the API response and update the log & credits
    $status = 'failed';
    $api_response_text = '';

    if ($curl_error) {
        $api_response_text = "cURL Error: " . $curl_error;
    } else {
        $response_data = json_decode($response_body, true);
        $api_response_text = $response_body;

        // Check for PhilmoreSMS success code
        if (isset($response_data['error_code']) && $response_data['error_code'] === '000') {
            $status = 'sent';
        }
    }

    try {
        // Update the log with the final status and API response
        $stmt_update_log = $pdo->prepare("UPDATE sms_log SET status = :status, api_response = :api_response WHERE id = :id");
        $stmt_update_log->execute([':status' => $status, ':api_response' => $api_response_text, ':id' => $log_id]);

        if ($status === 'sent') {
            // If sent successfully, deduct the credits
            $stmt_deduct = $pdo->prepare("UPDATE schools SET sms_credits = sms_credits - 1 WHERE id = :id");
            $stmt_deduct->execute(['id' => $school_id]);
            return true;
        } else {
            return "Failed to send message: " . $api_response_text;
        }
    } catch (PDOException $e) {
        error_log("SMS DB Error (post-send update): " . $e->getMessage());
        return "Critical DB error after sending message. Please check logs.";
    }
}
?>