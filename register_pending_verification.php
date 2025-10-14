
<?php
// This is a public page shown after registration, before email verification.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Almost Complete - ARS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .verification-container { max-width: 500px; margin: 5rem auto; }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="card shadow">
            <div class="card-body p-5 text-center">
                <i class="bi bi-envelope-check-fill text-success" style="font-size: 4rem;"></i>
                <h1 class="h3 mt-3">Almost there!</h1>
                <p class="lead text-muted">We've sent a verification link to your email address.</p>
                <p>Please check your inbox (and spam folder) and click the link to complete your registration and activate your account.</p>
                <hr>
                <a href="/" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
