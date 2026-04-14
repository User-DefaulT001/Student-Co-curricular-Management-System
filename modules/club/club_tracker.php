<?php

session_start();
require_once '../../config.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$success = '';

// Handle Messages
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// --- Logic: Handle POST Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $date = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status = $_POST['status'];
        mysqli_query($conn, "INSERT INTO clubs (user_id, club_name, role, join_date, status) VALUES ('$user_id', '$name', '$role', '$date', '$status')");
        $success = "Club record added!";
    }

    if ($action === 'edit') {
        $id     = (int)$_POST['club_id'];
        $name   = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role   = mysqli_real_escape_string($conn, $_POST['role']);
        $date   = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status = $_POST['status'];

        $sql = "UPDATE clubs SET club_name='$name', role='$role', join_date='$date', status='$status' WHERE club_id=$id AND user_id=$user_id";
        if (mysqli_query($conn, $sql)) $_SESSION['success_message'] = "Club record updated!";
        header("Location: club_tracker.php"); exit();
    }

    // Handle Delete
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        mysqli_query($conn, "DELETE FROM clubs WHERE club_id=$id AND user_id=$user_id");
        header("Location: club_tracker.php"); exit();
    }
}
include('../../includes/header.php');
include('../../includes/sidebar.php'); 
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow"></nav>

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
                <h1 class="h3 mb-0 text-gray-800">Club Tracker</h1>
                <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addClubModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Club
                </button>
            </div>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Membership Records</h6>
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
                                $res = mysqli_query($conn, "SELECT * FROM clubs WHERE user_id = $user_id");
                                while($row = mysqli_fetch_assoc($res)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['club_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                                        <td><?php echo $row['join_date']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo ($row['status']=='active')?'success':'secondary'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm">Edit</button>
                                            <a href="?delete=<?php echo $row['club_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> </div> <?php include('../../includes/footer.php'); 
?>



<script>
document.addEventListener('DOMContentLoaded', function () {
    $('.edit-btn').on('click', function() {
        $('#e_id').val($(this).data('id'));
        $('#e_name').val($(this).data('name'));
        $('#e_role').val($(this).data('role'));
        $('#e_date').val($(this).data('date'));
        $('#e_status').val($(this).data('status'));
    });
});
</script>
