<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Student Co-curricular Management System</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Segoe+UI:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-card-header">
                <h1><i class="fas fa-graduation-cap"></i></h1>
                <h1>Student CMS</h1>
                <p>Event Tracker Module</p>
            </div>
            
            <div class="login-card-body">
                <?php if (!empty($reg_success)): ?>
                    <div class="alert alert-success">
                        <strong>Success!</strong> <?php echo htmlspecialchars($reg_success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-error">
                        <strong>Error!</strong> <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>

                <h3 class="mb-4">Login to Your Account</h3>
                
                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>

                <hr style="margin: 1.5rem 0;">
                
                <p class="text-center text-muted mb-0">Demo Credentials:</p>
                <p class="text-center" style="font-size: 0.9rem;">
                    <strong>Username:</strong> student1, admin<br>
                    <strong>Password:</strong> password (for both users)
                </p>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>