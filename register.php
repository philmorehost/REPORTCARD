<?php
// This is a public page for new school registration.
require_once __DIR__ . '/core/init.php';

// Check if registration is enabled by the Super Admin
$registration_enabled = (bool)s_get($pdo, 'registration_enabled', 1); // Default to true if not set

// Start session to store registration data across steps
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch packages from the database
try {
    $stmt = $pdo->query("SELECT * FROM packages ORDER BY id");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $packages = [];
    $_SESSION['error'] = 'Could not load registration packages. Please contact support.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Registration - ARS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .registration-container { max-width: 700px; margin: 4rem auto; }
        .package-card {
            cursor: pointer;
            border: 1px solid #ddd;
            transition: all 0.2s ease-in-out;
        }
        .package-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.2);
        }
        .package-card .form-check-input {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 1.5em;
            height: 1.5em;
        }
        .package-card .form-check-label {
            width: 100%;
        }
        .package-features li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php if ($registration_enabled): ?>
    <div class="registration-container">
        <div class="text-center mb-4">
            <h1 class="h2">Register Your School</h1>
            <p class="lead">Step 1 of 3: Choose a Package & Provide Details</p>
        </div>

        <div class="card shadow">
            <div class="card-body p-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form action="/controllers/registration_controller.php" method="POST">
                    <input type="hidden" name="action" value="register_school">

                    <h5 class="mb-3">Choose Your Package</h5>
                    <?php if (!empty($packages)): ?>
                        <div class="row">
                        <?php foreach ($packages as $package): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 package-card" onclick="document.getElementById('package_<?php echo $package['id']; ?>').checked = true;">
                                    <div class="card-body">
                                        <input class="form-check-input" type="radio" name="package_id" id="package_<?php echo $package['id']; ?>" value="<?php echo $package['id']; ?>" required>
                                        <label class="form-check-label" for="package_<?php echo $package['id']; ?>">
                                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($package['name']); ?></h5>
                                            <p class="fs-4 fw-bold">
                                                <?php if ($package['price'] > 0): ?>
                                                    <?php echo get_currency_symbol($pdo); ?><?php echo htmlspecialchars(number_format($package['price'], 2)); ?>
                                                    <small class="text-muted fw-normal">/ student</small>
                                                <?php else: ?>
                                                    Free
                                                <?php endif; ?>
                                            </p>
                                            <p class="text-muted">
                                                <?php if ($package['student_limit'] > 0): ?>
                                                    Up to <?php echo htmlspecialchars($package['student_limit']); ?> students
                                                <?php else: ?>
                                                    Unlimited students
                                                <?php endif; ?>
                                            </p>
                                            <ul class="list-unstyled mt-3 mb-0 package-features">
                                                <?php
                                                $features = json_decode($package['features'], true);
                                                if (is_array($features)) {
                                                    foreach ($features as $feature) {
                                                        $is_negative = str_contains(strtolower($feature), 'cannot') || str_contains(strtolower($feature), 'no');
                                                        $icon = $is_negative ? 'bi-x-circle text-danger' : 'bi-check-circle text-success';
                                                        echo '<li><i class="bi ' . $icon . ' me-2"></i>' . htmlspecialchars($feature) . '</li>';
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">No packages are available at this time. Please contact support.</div>
                    <?php endif; ?>

                    <hr class="my-4">

                    <h5 class="mb-3">School & Administrator Information</h5>
                    <div class="mb-3">
                        <label for="school_name" class="form-label">School Name</label>
                        <input type="text" class="form-control" id="school_name" name="school_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="school_address" class="form-label">School Address</label>
                        <textarea class="form-control" id="school_address" name="school_address" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_name" class="form-label">Your Full Name</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="admin_email" class="form-label">Your Email Address</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" <?php if (empty($packages)) echo 'disabled'; ?>>
                            Register & Proceed <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="/">Back to Home</a>
        </div>
    </div>
    <script>
        // Add a class to the parent card when a radio button is selected
        document.querySelectorAll('.package-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.package-card').forEach(c => c.classList.remove('border-primary'));
                card.classList.add('border-primary');
            });
        });
    </script>
    <?php else: ?>
    <div class="registration-container">
        <div class="card shadow">
            <div class="card-body p-4 text-center">
                <h1 class="h2">Registration Closed</h1>
                <p class="lead">We are not accepting new school registrations at this time. Please check back later or contact support for more information.</p>
                <hr>
                <a href="/" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>