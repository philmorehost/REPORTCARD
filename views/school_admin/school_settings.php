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

                <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
            </div>
        </div>
    </form>
</div>

<?php include 'partials/footer.php'; ?>