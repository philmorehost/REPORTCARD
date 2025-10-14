<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARS Installer - Installation Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .installer-container { max-width: 700px; margin: 5rem auto; }
        .card-header { background-color: #198754; color: white; }
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
                <h5 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Installation Complete!</h5>
            </div>
            <div class="card-body p-4 text-center">
                <h2 class="text-success">Congratulations!</h2>
                <p class="lead">The Automated Report Card System has been installed successfully.</p>

                <div class="alert alert-danger mt-4">
                    <h4 class="alert-heading">IMPORTANT SECURITY WARNING!</h4>
                    <p>For the security of your application, you must now <strong>delete the entire `/install` directory</strong> from your server.</p>
                    <p class="mb-0">Leaving the installer on a live server is a major security risk.</p>
                </div>

                <div class="mt-4">
                    <a href="../views/super_admin/login.php" class="btn btn-primary btn-lg">Go to Super Admin Login</a>
                    <a href="../" class="btn btn-secondary btn-lg">View Your Landing Page</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>