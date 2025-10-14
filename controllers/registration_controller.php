<?php
/**
 * registration_controller.php - Handles the multi-step school registration process with packages.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/email.php';

$action = $_POST['action'] ?? '';

// --- Action Routing ---
switch ($action) {
    case 'register_school':
        handle_registration_start($pdo);
        break;
    case 'process_payment': // A single handler for both payment types
        handle_payment_processing($pdo);
        break;
    default:
        $_SESSION['error'] = 'Invalid action specified.';
        header('Location: /register.php');
        exit;
}


/**
 * Handles the initial registration form submission.
 * It checks the selected package and either completes the registration (for free packages)
 * or redirects to the payment page (for paid packages).
 */
function handle_registration_start($pdo) {
    // 1. Validation
    $required_fields = ['package_id', 'school_name', 'admin_name', 'admin_email', 'admin_password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: /register.php');
            exit;
        }
    }

    if ($_POST['admin_password'] !== $_POST['confirm_password']) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: /register.php');
        exit;
    }

    $email = filter_input(INPUT_POST, 'admin_email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $_SESSION['error'] = 'Invalid email format provided.';
        header('Location: /register.php');
        exit;
    }

    // 2. Fetch Package Details
    try {
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = :id");
        $stmt->execute([':id' => $_POST['package_id']]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$package) {
            $_SESSION['error'] = 'Invalid package selected.';
            header('Location: /register.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error fetching package details: ' . $e->getMessage();
        header('Location: /register.php');
        exit;
    }

    // 3. Store data in session
    $_SESSION['registration_data'] = [
        'package_id' => $package['id'],
        'package_name' => $package['name'],
        'package_price' => $package['price'],
        'student_limit' => $package['student_limit'],
        'school_name' => $_POST['school_name'],
        'school_address' => $_POST['school_address'] ?? '',
        'admin_name' => $_POST['admin_name'],
        'admin_email' => $email,
        'admin_password' => $_POST['admin_password']
    ];

    // 4. Decide next step based on price
    if ((float)$package['price'] <= 0) {
        // --- Freemium Package Flow ---
        // It's free, so create the school and user directly.
        try {
            $pdo->beginTransaction();

            $school_id = create_school_and_admin($pdo, $_SESSION['registration_data'], 'active', $package['student_limit']);

            // Log a "transaction" for consistency, though no payment was made.
            $stmt_trans = $pdo->prepare("INSERT INTO payment_transactions (school_id, description, amount, payment_method, status) VALUES (:sid, :desc, 0.00, 'freemium', 'completed')");
            $stmt_trans->execute([':sid' => $school_id, ':desc' => "Registration for Freemium package."]);

            $pdo->commit();

            unset($_SESSION['registration_data']);
            header('Location: /register_pending_verification.php');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            // Check for duplicate email error
            if ($e->errorInfo[1] == 1062) {
                $_SESSION['error'] = 'An account with this email address already exists.';
            } else {
                $_SESSION['error'] = "Freemium registration failed: " . $e->getMessage();
            }
            header('Location: /register.php');
            exit;
        }
    } else {
        // --- Premium Package Flow ---
        // It's a paid package, so proceed to the purchase/payment page.
        header('Location: /register_purchase.php');
        exit;
    }
}


/**
 * Handles the logic after the user specifies student quantity on the purchase page.
 * This function is for paid packages only.
 */
function handle_payment_processing($pdo) {
    if (!isset($_SESSION['registration_data'])) {
        header('Location: /register.php');
        exit;
    }

    // This action is now only for bank transfers
    if (($_POST['payment_method'] ?? '') !== 'bank_transfer') {
        $_SESSION['error'] = 'Invalid payment method for this action.';
        header('Location: /register_purchase.php');
        exit;
    }

    $reg_data = $_SESSION['registration_data'];
    $slots = filter_input(INPUT_POST, 'slot_quantity', FILTER_VALIDATE_INT);

    if ($slots === false || $slots <= 0) {
        $_SESSION['error'] = 'Please enter a valid number of student slots.';
        header('Location: /register_purchase.php');
        exit;
    }

    $amount = $slots * (float)$reg_data['package_price'];

    try {
        $pdo->beginTransaction();

        // Create school with pending status
        $school_id = create_school_and_admin($pdo, $reg_data, 'pending_payment', 0); // Slots added upon approval

        // Store info needed for the final step (proof upload)
        $_SESSION['pending_school_id'] = $school_id;
        $_SESSION['pending_slots'] = $slots;
        $_SESSION['pending_amount'] = $amount;

        $pdo->commit();
        unset($_SESSION['registration_data']);
        header('Location: /register_bank_transfer.php');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062) {
            $_SESSION['error'] = 'An account with this email address or school name already exists.';
        } else {
            $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        }
        header('Location: /register.php');
        exit;
    }
}

/**
 * Helper function to create school and admin user records.
 * This reduces code duplication.
 */
function create_school_and_admin($pdo, $reg_data, $status, $slots) {
    // Create school
    $stmt_school = $pdo->prepare(
        "INSERT INTO schools (name, package_id, address, student_slots, status, subscription_expires_at)
         VALUES (:name, :pid, :address, :slots, :status, :expiry)"
    );
    $stmt_school->execute([
        ':name' => $reg_data['school_name'],
        ':pid' => $reg_data['package_id'],
        ':address' => $reg_data['school_address'],
        ':slots' => $slots,
        ':status' => $status,
        ':expiry' => date('Y-m-d H:i:s', strtotime('+365 days')) // Assume a 1-year subscription
    ]);
    $school_id = $pdo->lastInsertId();

    // Generate a verification token
    $verification_token = bin2hex(random_bytes(32));

    // Create admin user with unverified status
    $stmt_user = $pdo->prepare(
        "INSERT INTO users (school_id, email, password, full_name, role, status, email_verification_token)
         VALUES (:sid, :email, :pass, :name, 'school_admin', 'unverified', :token)"
    );
    $stmt_user->execute([
        ':sid' => $school_id,
        ':email' => $reg_data['admin_email'],
        ':pass' => password_hash($reg_data['admin_password'], PASSWORD_BCRYPT),
        ':name' => $reg_data['admin_name'],
        ':token' => $verification_token
    ]);

    // Send verification email
    $verification_link = get_site_url() . "/verify_email.php?token=" . $verification_token;
    $subject = "Verify Your Email Address";
    $body = "
        <p>Hi " . htmlspecialchars($reg_data['admin_name']) . ",</p>
        <p>Thanks for registering. Please click the link below to verify your email address:</p>
        <p><a href='" . $verification_link . "'>" . $verification_link . "</a></p>
        <p>If you did not register, please ignore this email.</p>
    ";
    send_email($pdo, $reg_data['admin_email'], $subject, $body);


    return $school_id;
}
?>