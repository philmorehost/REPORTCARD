<?php
$page_title = "School Settings";
include 'partials/header.php'; // This will load the i18n functions

// In a real app, you would get this from a more dynamic source
$available_languages = [
    'en' => 'English',
    'es' => 'Español (Spanish)',
];

// The $school variable is available from header.php
$current_language = $school['language'] ?? 'en';

// Fetch the system URL from the database
$system_url = s_get($pdo, 'system_url', rtrim(APP_URL, '/'));
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">School Settings</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="/controllers/school_settings_controller.php" method="POST">
        <input type="hidden" name="action" value="update_settings">
        <div class="row">
            <div class="col-lg-8">
                <!-- Language Card -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 fw-bold text-primary">Language Preference</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="language_selector" class="form-label">Select the primary language for your school's dashboard and communications.</label>
                            <select class="form-select" name="language" id="language_selector">
                                <?php foreach ($available_languages as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php echo ($current_language == $code) ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Custom Domain Card -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 fw-bold text-primary">Custom Report Card Domain</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="custom_domain" class="form-label">Optional: Enter a custom domain for report card links.</label>
                            <input type="text" class="form-control" id="custom_domain" name="custom_domain" value="<?php echo htmlspecialchars($school['custom_domain'] ?? ''); ?>" placeholder="e.g., reports.myschool.com">
                            <div class="form-text">If left empty, the default system domain will be used: <strong><?php echo htmlspecialchars($system_url); ?></strong></div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>

                <!-- Close Account Card -->
                <div class="card shadow mb-4 mt-4">
                    <div class="card-header bg-danger text-white">
                        <h6 class="m-0 fw-bold">Close Account</h6>
                    </div>
                    <div class="card-body">
                        <p>Closing your account will disable access for all administrators and teachers. The super administrator can restore your account upon request.</p>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeAccountModal">
                            Close My School Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Close Account Modal -->
<div class="modal fade" id="closeAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Account Closure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to close your school's account? You and your staff will immediately lose access.</p>
                <p class="text-danger"><strong>This action can only be undone by contacting the super administrator.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/controllers/school_settings_controller.php" method="POST">
                    <input type="hidden" name="action" value="close_account">
                    <button type="submit" class="btn btn-danger">Yes, Close My Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>