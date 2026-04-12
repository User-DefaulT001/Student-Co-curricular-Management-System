<?php
/**
 * Authentication Handler
 * Handles user login, logout, and registration
 */
session_start();
require_once 'config.php';

$login_error = '';
$reg_error = '';
$reg_success = '';

// Handle GET actions (like Logout)
$action_get = isset($_GET['action']) ? $_GET['action'] : '';
if ($action_get === 'logout') {
    session_destroy();
    header("Location: login.php?message=logged_out");
    exit();
}

// Handle POST actions (Login or Register)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check which form was submitted (Defaults to login)
    $action_post = isset($_POST['action']) ? $_POST['action'] : 'login';

    // ==========================================
    // 1. REGISTRATION LOGIC
    // ==========================================
    if ($action_post === 'register') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($full_name) || empty($email) || empty($username) || empty($password)) {
            $reg_error = 'Please fill in all fields.';
        } else {
            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $reg_error = 'Username or Email already exists.';
            } else {
                // Hash the password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert the new user into the database
                $insert = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'student')");
                
                if ($insert) {
                    $insert->bind_param("ssss", $username, $email, $hashed_password, $full_name);
                    if ($insert->execute()) {
                        $reg_success = 'Registration successful! You can now log in.';
                    } else {
                        $reg_error = 'Database Error: ' . $conn->error; 
                    }
                    $insert->close();
                } else {
                    $reg_error = 'Prepare Error: ' . $conn->error;
                }
            }
            $stmt->close();
        }
    } 
    // ==========================================
    // 2. LOGIN LOGIC
    // ==========================================
    elseif ($action_post === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $login_error = 'Please enter both username and password.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $db_password = $user['password'];
                
                $login_successful = false;

                // 1. Check if the password matches the secure hash
                if (password_verify($password, $db_password)) {
                    $login_successful = true;
                } 
                // 2. FALLBACK: Check if it's a plain text password from your setup.sql
                elseif ($password === $db_password) {
                    $login_successful = true;
                    
                    // Auto-upgrade their plain text password to a secure hash in the database
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $update_stmt->bind_param("si", $new_hash, $user['user_id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                // If login passed either check, set up the session
                if ($login_successful) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'student';
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $login_error = 'Invalid password.';
                }
            } else {
                $login_error = 'User not found.';
            }
            $stmt->close();
        }
    }
}
?>