<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];

    // 1. Test Database Connection
    try {
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if database exists, create if not
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");

    } catch (PDOException $e) {
        $error_message = "Database connection failed: " . $e->getMessage();
    }

    if (empty($error_message)) {
        // 2. Create the config.php file
        $config_content = "<?php
// --- Database Configuration ---
define('DB_HOST', '$db_host');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('DB_NAME', '$db_name');

// --- Application Paths ---
\$protocol = (!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off') || (\$_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' ? 'https://' : 'http://';
define('APP_URL', \$protocol . \$_SERVER['HTTP_HOST'] . rtrim(dirname(dirname(\$_SERVER['SCRIPT_NAME'])), '/'));
define('APP_ROOT', dirname(__DIR__));

// --- Other Settings ---
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME, DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException \$e) {
    die(\"ERROR: Could not connect to the database. \" . \$e->getMessage());
}
?>";

        if (!file_put_contents('../config/config.php', $config_content)) {
            $error_message = "Error: Could not write to config.php. Please check file permissions.";
        }
    }

    if (empty($error_message)) {
        // 3. Import the database schema from the root directory
        try {
            require_once '../core/helpers.php'; // Include the helper file
            import_sql_file($pdo, '../database.sql');
        } catch (Exception $e) {
            $error_message = "Error importing database schema: " . $e->getMessage();
        }
    }

    if (empty($error_message)) {
        // If everything is successful, redirect to the next step
        header('Location: /install/admin_setup.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARS Installer - Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .installer-container { max-width: 700px; margin: 5rem auto; }
        .card-header { background-color: #343a40; color: white; }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="text-center mb-4">
            <h1 class="h2">Automated Report Card System</h1>
            <p class="lead">Installation Wizard</p>
        </div>

        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Step 2: Database Setup</h5>
            </div>
            <div class="card-body p-4">
                <p>Please provide your database details below. The installer will attempt to create the database if it doesn't exist.</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Database Host</label>
                        <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_name" class="form-label">Database Name</label>
                        <input type="text" class="form-control" id="db_name" name="db_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_user" class="form-label">Database Username</label>
                        <input type="text" class="form-control" id="db_user" name="db_user" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_pass" class="form-label">Database Password</label>
                        <input type="password" class="form-control" id="db_pass" name="db_pass">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            Setup Database <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>