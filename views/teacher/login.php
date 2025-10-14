<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login - Automated Report Card System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #e9ecef;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .login-card-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-card-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
        }
         .login-card-header .bi-pencil-square {
            font-size: 2.5rem;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="login-card-header">
            <i class="bi bi-pencil-square"></i>
            <h1>Teacher Grading Hub</h1>
            <p class="text-muted">Please sign in to continue</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/controllers/auth_controller.php?action=login" method="POST">
            <input type="hidden" name="role" value="teacher">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="your.email@school.com" required>
                <label for="email">Email address</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg">Login</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"></script>
</body>
</html>