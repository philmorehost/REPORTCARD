<?php
/**
 * auth_controller.php - Handles user authentication (login/logout) and impersonation.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/init.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handle_login($pdo);
        break;
    case 'logout':
        handle_logout();
        break;
    case 'login_as':
        handle_login_as($pdo);
        break;
    case 'switch_back':
        handle_switch_back();
        break;
    default:
        header('Location: /index.php');
        exit;
}

function handle_login($pdo) {
    $role = $_POST['role'] ?? 'teacher';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect_with_error('Invalid request method.', $role); }
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if (!$email || empty($password)) { redirect_with_error('Email and password are required.', $role); }
    try {
        $stmt = $pdo->prepare("SELECT id, password, full_name, role, school_id FROM users WHERE email = :email AND role = :role AND status = 'active'");
        $stmt->execute(['email' => $email, 'role' => $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            if ($user['school_id']) { $_SESSION['school_id'] = $user['school_id']; }
            $redirect_path = ['super_admin' => '/views/super_admin/dashboard.php', 'school_admin' => '/views/school_admin/dashboard.php', 'teacher' => '/views/teacher/dashboard.php'];
            header('Location: ' . ($redirect_path[$user['role']] ?? '/index.php'));
            exit;
        } else {
            redirect_with_error('Invalid email or password.', $role);
        }
    } catch (PDOException $e) {
        redirect_with_error('Database error. Please try again later.', $role);
    }
}

function handle_logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header('Location: /index.php');
    exit;
}

function handle_login_as($pdo) {
    // Security check: Only super admins can use this feature
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
        die("Access Denied.");
    }
    // And only if they are not already impersonating someone
    if (isset($_SESSION['original_user'])) {
        die("You are already impersonating a user. Switch back first.");
    }

    $target_user_id = $_GET['user_id'] ?? 0;
    if (!$target_user_id) { die("No user ID specified."); }

    try {
        $stmt = $pdo->prepare("SELECT id, password, full_name, role, school_id FROM users WHERE id = :id AND role = 'school_admin'");
        $stmt->execute(['id' => $target_user_id]);
        $target_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($target_user) {
            // Store original super admin session
            $_SESSION['original_user'] = [
                'user_id' => $_SESSION['user_id'],
                'user_role' => $_SESSION['user_role'],
                'user_name' => $_SESSION['user_name'],
            ];
            // Switch to target user's session
            $_SESSION['user_id'] = $target_user['id'];
            $_SESSION['user_role'] = $target_user['role'];
            $_SESSION['user_name'] = $target_user['full_name'];
            $_SESSION['school_id'] = $target_user['school_id'];

            header('Location: /views/school_admin/dashboard.php');
            exit;
        } else {
            die("Target user not found or is not a School Administrator.");
        }
    } catch (PDOException $e) {
        die("Database error during impersonation.");
    }
}

function handle_switch_back() {
    // Check if there's an original user session to switch back to
    if (isset($_SESSION['original_user'])) {
        // Restore the original super admin session
        $original_user = $_SESSION['original_user'];
        $_SESSION['user_id'] = $original_user['user_id'];
        $_SESSION['user_role'] = $original_user['user_role'];
        $_SESSION['user_name'] = $original_user['user_name'];
        unset($_SESSION['school_id']); // Super admin doesn't have a school_id

        // Remove the temporary session data
        unset($_SESSION['original_user']);

        header('Location: /views/super_admin/dashboard.php');
        exit;
    } else {
        // If no original session, just send them to their respective dashboard
        $role = $_SESSION['user_role'] ?? 'guest';
        $redirect_path = ['super_admin' => '/views/super_admin/dashboard.php', 'school_admin' => '/views/school_admin/dashboard.php', 'teacher' => '/views/teacher/dashboard.php'];
        header('Location: ' . ($redirect_path[$role] ?? '/index.php'));
        exit;
    }
}

function redirect_with_error($message, $role) {
    $redirect_path = ['super_admin' => '/views/super_admin/login.php', 'school_admin' => '/views/school_admin/login.php', 'teacher' => '/views/teacher/login.php'];
    header('Location: ' . ($redirect_path[$role] ?? '/index.php') . '?error=' . urlencode($message));
    exit;
}
?>