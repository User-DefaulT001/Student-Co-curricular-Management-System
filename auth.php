<?php
/**
 * Authentication Handler
 * Handles user login, logout, and session management
 */

session_start();
require_once 'config.php';

$login_error = '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle logout
if ($action === 'logout') {
    session_destroy();
    header("Location: login.php?message=logged_out");
    exit();
}

// Handle login POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $login_error = 'Please enter both username and password.';
    } else {
        // Query user from database
        $stmt = $conn->prepare("SELECT user_id, username, password, full_name, role FROM users WHERE username = ?");
        
        if (!$stmt) {
            $login_error = 'Database error: ' . $conn->error . ' (Make sure you imported database/setup.sql into MySQL)';
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Redirect to dashboard
                    header("Location: index.php");
                    exit();
                } else {
                    $login_error = 'Invalid password. Test credentials: student1 / password';
                }
            } else {
                $login_error = 'Username not found: "' . htmlspecialchars($username) . '". Test user is: student1';
            }
            $stmt->close();
        }
    }
}

// Handle registration POST request
if (isset($_POST['register'])) {
    $username = trim($_POST['reg_username'] ?? '');
    $email = trim($_POST['reg_email'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $confirm_password = $_POST['reg_confirm_password'] ?? '';
    $full_name = trim($_POST['reg_full_name'] ?? '');
    
    $reg_error = '';
    $reg_success = '';
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $reg_error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $reg_error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $reg_error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $reg_error = 'Invalid email format.';
    } else {
        // Check if user already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $reg_error = 'Username or email already exists.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $insert_stmt = $conn->prepare(
                    "INSERT INTO users (username, email, password, full_name, role) 
                     VALUES (?, ?, ?, ?, 'student')"
                );
                
                if ($insert_stmt) {
                    $insert_stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
                    
                    if ($insert_stmt->execute()) {
                        $reg_success = 'Registration successful! Please log in with your credentials.';
                    } else {
                        $reg_error = 'Registration failed. Please try again.';
                    }
                    $insert_stmt->close();
                } else {
                    $reg_error = 'Database error. Please try again later.';
                }
            }
            $stmt->close();
        }
    }
}

?>
