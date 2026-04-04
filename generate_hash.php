<?php
/**
 * Generate fresh password hashes
 */

echo "<h2>🔐 Password Hash Generator</h2>";

$password = 'password';
$fresh_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

echo "<p><strong>Password:</strong> " . $password . "</p>";
echo "<p><strong>Fresh Hash:</strong> <code>" . $fresh_hash . "</code></p>";
echo "<p style='color: green;'>Copy the hash above and paste it into the database</p>";

// Test the new hash
if (password_verify($password, $fresh_hash)) {
    echo "<p style='color: green;'>✅ This new hash works correctly with password_verify()</p>";
} else {
    echo "<p style='color: red;'>❌ Hash generation failed</p>";
}

// SQL to update users
echo "<h3>SQL to Update Users:</h3>";
echo "<pre><code>UPDATE users SET password = '$fresh_hash' WHERE username IN ('admin', 'student1');</code></pre>";

echo "<p style='margin-top: 20px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107;'>";
echo "<strong>⚠️ Instructions:</strong><br>";
echo "1. Copy the hash from above<br>";
echo "2. Go to phpMyAdmin http://localhost/phpmyadmin/<br>";
echo "3. Select the student_cms database<br>";
echo "4. Click SQL tab<br>";
echo "5. Paste the SQL UPDATE command<br>";
echo "6. Click Go<br>";
echo "7. Try logging in again with student1/password<br>";
echo "</p>";
?>
