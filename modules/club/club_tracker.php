<?php

session_start();

// AUTHENTICATION 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// DATABASE CONNECTION
$conn = mysqli_connect("localhost", "root", "", "student_cms");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// 3. HANDLE ACTIONS (CRUD)
// --- Add Record ---
if (isset($_POST['add_club'])) {
    $name = mysqli_real_escape_string($conn, $_POST['club_name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $date = mysqli_real_escape_string($conn, $_POST['join_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO clubs (user_id, club_name, role, join_date, status) 
            VALUES ('$user_id', '$name', '$role', '$date', '$status')";
    if (mysqli_query($conn, $sql)) $success = "New club record added!";
    else $error = "Error: " . mysqli_error($conn);
}

// --- Update Record ---
if (isset($_POST['update_club'])) {
    $id = intval($_POST['club_id']);
    $name = mysqli_real_escape_string($conn, $_POST['club_name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $date = mysqli_real_escape_string($conn, $_POST['join_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE clubs SET club_name='$name', role='$role', join_date='$date', status='$status' 
            WHERE club_id=$id AND user_id=$user_id";
    if (mysqli_query($conn, $sql)) $success = "Club record updated!";
}

// --- Delete Record ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM clubs WHERE club_id=$id AND user_id=$user_id");
    header("Location: club_tracker.php");
    exit();
}


include('includes/header.php'); 
include('includes/sidebar.php'); 
?>

<div id="content">
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users mr-2"></i>Club Tracker</h1>
            <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addClubModal">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Club
            </button>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Your Memberships</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Club Name</th>
                                <th>Role</th>
                                <th>Join Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM clubs WHERE user_id = '$user_id' ORDER BY join_date DESC");
                            while($row = mysqli_fetch_assoc($res)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['club_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td><?php echo $row['join_date']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo ($row['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="club_tracker.php?edit=<?php echo $row['club_id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                        <a href="club_tracker.php?delete=<?php echo $row['club_id']; ?>" 
                                           class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addClubModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Club Membership</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Club Name</label>
                        <input type="text" name="club_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Position / Role</label>
                        <input type="text" name="role" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Join Date</label>
                        <input type="date" name="join_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit" name="add_club">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
