<?php
// Include the authentication backend that handles the registration logic
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register - Student Co-curricular Management System</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Segoe+UI:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-card-header">
                <h1><i class="fas fa-user-plus"></i></h1>
                <h1>Student CMS</h1>
                <p>Create a New Account</p>
            </div>
            
            <div class="login-card-body">
                <?php if (!empty($reg_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo htmlspecialchars($reg_error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($reg_success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <strong>Success!</strong> <?php echo htmlspecialchars($reg_success); ?>
                        <br><a href="login.php" class="alert-link">Click here to log in.</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <input type="hidden" name="action" value="register"> <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Register Account</button>
                </form>

                <hr style="margin: 1.5rem 0;">
                
                <p class="text-center mb-0">
                    Already have an account? <a href="login.php" class="text-primary font-weight-bold">Log in here</a>
                </p>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>