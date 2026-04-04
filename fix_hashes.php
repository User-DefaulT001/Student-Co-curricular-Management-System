<?php
/**
 * Auto-fix password hashes
 */
require_once 'config.php';

echo "<h2>🔐 Auto-Fixing Password Hashes</h2>";

// Generate fresh hash for 'password'
$password = 'password';
$fresh_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

echo "<p>Generating fresh password hash for password: <strong>password</strong></p>";
echo "<p>New hash: <code>" . htmlspecialchars($fresh_hash) . "</code></p>";

// Update users with fresh hash
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username IN ('admin', 'student1')");
if ($stmt) {
    $stmt->bind_param("s", $fresh_hash);
    if ($stmt->execute()) {
        echo "<p style='color: green;'><strong>✅ SUCCESS!</strong> Updated " . $stmt->affected_rows . " users with fresh password hash</p>";
        
        // Verify the fix
        echo "<h3>Verifying the fix...</h3>";
        $verify_stmt = $conn->prepare("SELECT username, password FROM users WHERE username IN ('admin', 'student1')");
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();
        
        while ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                echo "<p style='color: green;'>✅ User '<strong>" . htmlspecialchars($user['username']) . "</strong>' - password verification works!</p>";
            } else {
                echo "<p style='color: red;'>❌ User '<strong>" . htmlspecialchars($user['username']) . "</strong>' - password verification still failed</p>";
            }
        }
        
        echo "<h3 style='margin-top: 30px; color: green;'>✅ All Done!</h3>";
        echo "<p><strong>You can now login with:</strong></p>";
        echo "<ul>";
        echo "<li>Username: <strong>student1</strong><br>Password: <strong>password</strong></li>";
        echo "<li>Username: <strong>admin</strong><br>Password: <strong>password</strong></li>";
        echo "</ul>";
        
        echo "<p><a href='login.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Login</a></p>";
        
        $verify_stmt->close();
    } else {
        echo "<p style='color: red;'><strong>❌ ERROR:</strong> " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'><strong>❌ ERROR:</strong> " . $conn->error . "</p>";
}
?>
