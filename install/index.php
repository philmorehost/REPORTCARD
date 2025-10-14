<?php
// Installer for the Automated Report Card System

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the steps
$steps = [
    1 => 'Welcome & Requirements',
    2 => 'Database Setup',
    3 => 'Admin Account',
    4 => 'Complete'
];

// Determine the current step
$current_step = $_GET['step'] ?? 1;
if (!array_key_exists($current_step, $steps)) {
    $current_step = 1;
}

// --- Server Requirements Check ---
$requirements = [
    'php_version' => [
        'name' => 'PHP Version >= 8.0',
        'check' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'message' => 'Your PHP version is ' . PHP_VERSION . '. Version 8.0 or higher is required.'
    ],
    'pdo_mysql' => [
        'name' => 'PDO_MySQL Extension',
        'check' => extension_loaded('pdo_mysql'),
        'message' => 'The PDO_MySQL extension is required for database connectivity.'
    ],
    'json' => [
        'name' => 'JSON Extension',
        'check' => extension_loaded('json'),
        'message' => 'The JSON extension is required for handling report card structures.'
    ],
];

$all_requirements_met = true;
foreach ($requirements as $req) {
    if (!$req['check']) {
        $all_requirements_met = false;
        break;
    }
}

// --- HTML Layout ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARS Installer - <?php echo $steps[$current_step]; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .installer-container { max-width: 700px; margin: 5rem auto; }
        .card-header { background-color: #343a40; color: white; }
        .list-group-item .bi-check-circle-fill { color: #198754; }
        .list-group-item .bi-x-circle-fill { color: #dc3545; }
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
                <h5 class="mb-0">Step 1: Welcome & Server Requirements</h5>
            </div>
            <div class="card-body p-4">
                <p>Welcome to the ARS installer! This wizard will guide you through the setup process. First, let's check if your server meets the minimum requirements.</p>

                <ul class="list-group mb-4">
                    <?php foreach ($requirements as $req): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo $req['name']; ?>
                        <?php if ($req['check']): ?>
                            <i class="bi bi-check-circle-fill fs-5"></i>
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill fs-5" title="<?php echo $req['message']; ?>"></i>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!$all_requirements_met): ?>
                    <div class="alert alert-danger">
                        Your server does not meet all the requirements. Please resolve the issues marked with a <i class="bi bi-x-circle-fill"></i> before proceeding.
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        Congratulations! Your server meets all the requirements.
                    </div>
                    <div class="d-grid">
                        <a href="/install/database.php" class="btn btn-primary">
                            Next Step: Database Setup <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>