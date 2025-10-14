<?php
/**
 * email.php - Handles sending emails using PHPMailer with system-wide settings.
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require PHPMailer source files from the correct path
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
// Require the new modern email template
require_once __DIR__ . '/email_template.php';

/**
 * Sends a real email using the system-wide configured SMTP settings.
 *
 * @param PDO $pdo The database connection object.
 * @param string $to The recipient's email address.
 * @param string $subject The email subject.
 * @param string $body The HTML body of the email.
 * @param array $template_data Optional data to wrap the email in a modern template.
 * @return string|true Returns true on success, or a string containing the error message on failure.
 */
function send_email($pdo, $to, $subject, $body, $template_data = []) {
    // --- Validate Parameters ---
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL) || empty($subject) || empty($body)) {
        return "Invalid parameters provided for email.";
    }

    // --- Fetch System-wide SMTP Settings ---
    try {
        $smtp_host = s_get($pdo, 'smtp_host');
        $smtp_port = s_get($pdo, 'smtp_port');
        $smtp_user = s_get($pdo, 'smtp_username');
        $smtp_pass = s_get($pdo, 'smtp_password');
        $from_email = s_get($pdo, 'smtp_from_email');
        $from_name = s_get($pdo, 'smtp_from_name');
        $smtp_encryption = s_get($pdo, 'smtp_encryption'); // Fetch encryption setting
    } catch (PDOException $e) {
        error_log("Database error while fetching system SMTP settings: " . $e->getMessage());
        return "Database error while fetching system settings.";
    }

    // --- Validate Settings ---
    if (empty($smtp_host) || empty($smtp_port) || empty($smtp_user) || empty($from_email) || empty($from_name)) {
        return "The system-wide SMTP settings are not fully configured by the super administrator.";
    }

    // If template data is provided, wrap the body content in the modern template
    if (!empty($template_data)) {
        // Ensure the logo URL is absolute for email clients
        if (!empty($template_data['school_info']['logo_url']) && !empty($template_data['system_url'])) {
            $logo_path = $template_data['school_info']['logo_url'];
            // Check if it's not already a full URL
            if (strpos($logo_path, 'http') !== 0) {
                // If not, prepend the system URL
                $template_data['school_info']['logo_url'] = rtrim($template_data['system_url'], '/') . '/' . ltrim($logo_path, '/');
            }
        }
        $body = create_modern_email_html($body, $template_data);
    }

    $mail = new PHPMailer(true);

    try {
        // --- Server settings ---
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;

        // Dynamically set the encryption based on the system setting
        if ($smtp_encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($smtp_encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        // If 'none' or null, SMTPSecure is not set, disabling explicit encryption as intended.

        $mail->Port       = (int)$smtp_port;
        $mail->Timeout    = 10; // 10-second timeout

        // --- Recipients ---
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);
        $mail->addReplyTo($from_email, $from_name);

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log and return the detailed error from PHPMailer
        $error_message = "Mailer Error: {$mail->ErrorInfo}";
        error_log("Email could not be sent. " . $error_message);
        return $error_message;
    }
}
?>