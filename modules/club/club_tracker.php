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
    
    // Add logic
    if ($action === 'add') {
        $name = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $date = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status = $_POST['status'];
        mysqli_query($conn, "INSERT INTO clubs (user_id, club_name, role, join_date, status) VALUES ('$user_id', '$name', '$role', '$date', '$status')");
        $_SESSION['success_message'] = "Club record added!";
        header("Location: club_tracker.php"); exit();
    }

    // Edit logic
    if ($action === 'edit') {
        $id = (int)$_POST['club_id'];
        $name = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $date = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status = $_POST['status'];
        $sql = "UPDATE clubs SET club_name='$name', role='$role', join_date='$date', status='$status' WHERE club_id=$id AND user_id=$user_id";
        if (mysqli_query($conn, $sql)) $_SESSION['success_message'] = "Club record updated!";
        header("Location: club_tracker.php"); exit();
    }
}

// Handle Delete logic
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM clubs WHERE club_id=$id AND user_id=$user_id");
    $_SESSION['success_message'] = "Record deleted!";
    header("Location: club_tracker.php"); exit();
}

include('../../includes/header.php');
include('../../includes/sidebar.php'); 
?>

<div id="content-wrapper" class="d-flex flex-column">

    <div id="content">

        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>
        </nav>

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users mr-2"></i>Club Tracker</h1>
                <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addClubModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Club
                </button>
            </div>

            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">My Club Records</h6>
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
                                $res = mysqli_query($conn, "SELECT * FROM clubs WHERE user_id = $user_id ORDER BY join_date DESC");
                                while($row = mysqli_fetch_assoc($res)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['club_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                                        <td><?php echo $row['join_date']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo ($row['status']=='active')?'success':'secondary'; ?> p-2">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm edit-btn" 
                                                data-id="<?php echo $row['club_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['club_name']); ?>"
                                                data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                                data-date="<?php echo $row['join_date']; ?>"
                                                data-status="<?php echo $row['status']; ?>"
                                                data-toggle="modal" data-target="#editClubModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete=<?php echo $row['club_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Confirm delete?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> </div> <?php include('../../includes/footer.php'); ?>

</div> <div class="modal fade" id="addClubModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-left-primary shadow">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">New Membership</h5>
                    <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Club Name</label><input type="text" name="club_name" class="form-control" required></div>
                    <div class="form-group"><label>Role</label><input type="text" name="role" class="form-control" required></div>
                    <div class="form-group"><label>Join Date</label><input type="date" name="join_date" class="form-control" required></div>
                    <div class="form-group"><label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editClubModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-left-info shadow">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="club_id" id="e_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Edit Record</h5>
                    <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Club Name</label><input type="text" name="club_name" id="e_name" class="form-control"></div>
                    <div class="form-group"><label>Role</label><input type="text" name="role" id="e_role" class="form-control"></div>
                    <div class="form-group"><label>Join Date</label><input type="date" name="join_date" id="e_date" class="form-control"></div>
                    <div class="form-group"><label>Status</label>
                        <select name="status" id="e_status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-info">Update</button></div>
            </form>
        </div>
    </div>
</div>

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
