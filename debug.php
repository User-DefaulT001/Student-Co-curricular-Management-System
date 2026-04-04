<?php
/**
 * Debug script to diagnose login issues
 */
require_once 'config.php';

echo "<h2>🔍 Database Diagnostics</h2>";

// 1. Check connection
echo "<h3>1. Database Connection:</h3>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Connection Failed: " . $conn->connect_error . "</p>";
    die();
} else {
    echo "<p style='color: green;'>✅ Connected to database: " . DB_NAME . "</p>";
}

// 2. Check if users table exists
echo "<h3>2. Checking Users Table:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Users table exists</p>";
    
    // Count users
    $count_result = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $count_result->fetch_assoc();
    echo "<p>Total users in database: <strong>" . $count['count'] . "</strong></p>";
    
    // List all users
    echo "<h4>Users in database:</h4>";
    $users = $conn->query("SELECT user_id, username, email, full_name, role FROM users");
    if ($users->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th></tr>";
        while ($user = $users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['user_id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['full_name'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No users found in database</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Users table does NOT exist. You must import database/setup.sql</p>";
}

// 3. Test password verification
echo "<h3>3. Testing Password Hash:</h3>";
$test_hash = '$2y$10$Oys7sDIfGKbxULr8MZzFV.bF95dBB8dMXg.FshXphc.1UaGBdl/5y';
$test_password = 'password';

if (password_verify($test_password, $test_hash)) {
    echo "<p style='color: green;'>✅ Password 'password' verifies correctly with the test hash</p>";
} else {
    echo "<p style='color: red;'>❌ Password verification failed</p>";
}

// 4. Try to authenticate with student1
echo "<h3>4. Testing Login with 'student1':</h3>";
$stmt = $conn->prepare("SELECT user_id, username, password, full_name FROM users WHERE username = ?");
if ($stmt) {
    $stmt->bind_param("s", $username);
    $username = 'student1';
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ User 'student1' found in database</p>";
        echo "<p><strong>User Details:</strong></p>";
        echo "<p>ID: " . $user['user_id'] . "</p>";
        echo "<p>Username: " . $user['username'] . "</p>";
        echo "<p>Full Name: " . $user['full_name'] . "</p>";
        
        // Try to verify password
        if (password_verify('password', $user['password'])) {
            echo "<p style='color: green;'>✅ Password verification SUCCESS - password is correct</p>";
        } else {
            echo "<p style='color: red;'>❌ Password verification FAILED</p>";
            echo "<p><strong>Stored hash:</strong> " . $user['password'] . "</p>";
            echo "<p><strong>Expected hash:</strong> " . $test_hash . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ User 'student1' NOT found in database</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>❌ Database query failed: " . $conn->error . "</p>";
}

echo "<h3>5. Next Steps:</h3>";
echo "<p>If the users table doesn't exist, you need to:</p>";
echo "<ol>";
echo "<li>Open <strong>phpMyAdmin</strong> at http://localhost/phpmyadmin/</li>";
echo "<li>Click the <strong>Import</strong> tab</li>";
echo "<li>Select <strong>database/setup.sql</strong> from this project</li>";
echo "<li>Click <strong>Go</strong> to import the database</li>";
echo "<li>Come back to this page to verify everything is working</li>";
echo "</ol>";
?>
