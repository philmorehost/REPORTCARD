<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['message']) && !empty($_SESSION['message'])):
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info'; // Default to 'info'

    // Map message types to Bootstrap alert classes
    $alert_class = 'alert-info'; // Default
    if ($message_type === 'success') {
        $alert_class = 'alert-success';
    } elseif ($message_type === 'error') {
        $alert_class = 'alert-danger';
    } elseif ($message_type === 'warning') {
        $alert_class = 'alert-warning';
    }
?>
    <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; // The message can contain HTML, so we don't escape it here. Assumes sanitization at the source. ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
    // Clear the message from the session so it doesn't show again
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
endif;

// A separate check for 'error' for backward compatibility or direct error setting
if (isset($_SESSION['error']) && !empty($_SESSION['error'])):
?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
    unset($_SESSION['error']);
endif;
?>