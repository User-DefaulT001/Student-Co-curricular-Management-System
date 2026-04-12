<?php

session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "student_cms");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];
$message = "";

// CREATE (Add Record)
if (isset($_POST['add'])) {
    $c_name   = mysqli_real_escape_string($conn, $_POST['club_name']);
    $c_role   = mysqli_real_escape_string($conn, $_POST['role']);
    $c_date   = mysqli_real_escape_string($conn, $_POST['join_date']);
    $c_status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO clubs (user_id, club_name, role, join_date, status) 
            VALUES ('$user_id', '$c_name', '$c_role', '$c_date', '$c_status')";
    
    if (mysqli_query($conn, $sql)) {
        $message = "Record added successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Ensure the record belongs to the current user
    $sql = "DELETE FROM clubs WHERE club_id = $id AND user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: club-tracker.php?msg=deleted");
        exit();
    }
}

// UPDATE
if (isset($_POST['update'])) {
    $id       = intval($_POST['club_id']);
    $c_name   = mysqli_real_escape_string($conn, $_POST['club_name']);
    $c_role   = mysqli_real_escape_string($conn, $_POST['role']);
    $c_date   = mysqli_real_escape_string($conn, $_POST['join_date']);
    $c_status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE clubs SET club_name='$c_name', role='$c_role', join_date='$c_date', status='$c_status' 
            WHERE club_id=$id AND user_id=$user_id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: club-tracker.php?msg=updated");
        exit();
    }
}

// FETCH DATA FOR EDITING
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM clubs WHERE club_id=$id AND user_id=$user_id");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Club Tracker | Student CMS</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; background-color: #f4f7f6; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; color: #333; }
        .form-box { background: #fafafa; padding: 20px; border: 1px solid #eee; margin-bottom: 30px; }
        input, select { padding: 8px; margin: 10px 0; width: 100%; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn-submit { background-color: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        .action-links a { text-decoration: none; margin-right: 10px; font-weight: bold; }
        .delete-btn { color: #dc3545; }
        .edit-btn { color: #007bff; }
    </style>
</head>
<body>

<div class="container">
    <h2>Club Tracker Module</h2>
    <p><a href="dashboard.php">← Back to Dashboard</a></p>

    <?php 
        if ($message) echo "<p style='color:green;'>$message</p>"; 
        if (isset($_GET['msg'])) echo "<p style='color:green;'>Action successful!</p>";
    ?>

    <div class="form-box">
        <h3><?php echo $edit_data ? "Edit Club Entry" : "Add New Membership"; ?></h3>
        <form method="POST">
            <?php if ($edit_data): ?>
                <input type="hidden" name="club_id" value="<?php echo $edit_data['club_id']; ?>">
            <?php endif; ?>

            <label>Club Name:</label>
            <input type="text" name="club_name" value="<?php echo htmlspecialchars($edit_data['club_name'] ?? ''); ?>" required>

            <label>Role / Position:</label>
            <input type="text" name="role" value="<?php echo htmlspecialchars($edit_data['role'] ?? ''); ?>" placeholder="e.g. Committee Member" required>

            <label>Join Date:</label>
            <input type="date" name="join_date" value="<?php echo $edit_data['join_date'] ?? ''; ?>" required>

            <label>Status:</label>
            <select name="status">
                <option value="active" <?php echo (isset($edit_data['status']) && $edit_data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo (isset($edit_data['status']) && $edit_data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>

            <button type="submit" name="<?php echo $edit_data ? 'update' : 'add'; ?>" class="btn-submit">
                <?php echo $edit_data ? "Update Record" : "Save Record"; ?>
            </button>
            <?php if ($edit_data): ?> 
                <a href="club-tracker.php" style="margin-left: 10px; color: #666;">Cancel</a> 
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Club Name</th>
                <th>Role</th>
                <th>Joined Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Read: Display only records belonging to the current user
            $query = "SELECT * FROM clubs WHERE user_id = '$user_id' ORDER BY join_date DESC";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['club_name']) . "</td>
                            <td>" . htmlspecialchars($row['role']) . "</td>
                            <td>" . $row['join_date'] . "</td>
                            <td>" . ucfirst($row['status']) . "</td>
                            <td class='action-links'>
                                <a href='club-tracker.php?edit={$row['club_id']}' class='edit-btn'>Edit</a>
                                <a href='club-tracker.php?delete={$row['club_id']}' 
                                   class='delete-btn' 
                                   onclick='return confirm(\"Are you sure you want to delete this record?\")'>Delete</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
