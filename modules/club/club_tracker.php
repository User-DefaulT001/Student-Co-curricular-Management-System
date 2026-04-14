<?php

session_start();
require_once '../../config.php'; 

// Authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$success = '';
$error   = '';

// 3. Handle Form Submissions (CRUD)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD CLUB
    if ($action === 'add') {
        $name   = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role   = mysqli_real_escape_string($conn, $_POST['role']);
        $date   = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status = $_POST['status'] ?? 'active';

        $query = "INSERT INTO clubs (user_id, club_name, role, join_date, status) VALUES ('$user_id', '$name', '$role', '$date', '$status')";
        if (mysqli_query($conn, $query)) $success = "New club membership added!";
        else $error = "Failed to add the new club membership.";
    }

    // UPDATE CLUB
    if ($action === 'edit') {
        $id     = (int) $_POST['club_id'];
        $name   = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role   = mysqli_real_escape_string($conn, $_POST['role']);
        $date   = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status = $_POST['status'];

        $query = "UPDATE clubs SET club_name='$name', role='$role', join_date='$date', status='$status' WHERE club_id=$id AND user_id=$user_id";
        if (mysqli_query($conn, $query)) $success = "Club record updated!";
        else $error = "Update failed.";
    }
}

// DELETE CLUB
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM clubs WHERE club_id=$id AND user_id=$user_id");
    header("Location: club_tracker.php");
    exit();
}

// Header & Sidebar
include('../../includes/header.php'); 
include('../../includes/sidebar.php');
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users mr-2"></i>Club Tracker</h1>
                <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addClubModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Club
                </button>
            </div>

            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">My Memberships List</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Club Name</th>
                                    <th>Role / Position</th>
                                    <th>Joined Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($conn, "SELECT * FROM clubs WHERE user_id = $user_id ORDER BY join_date DESC");
                                while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['club_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                                        <td><?php echo $row['join_date']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo ($row['status'] == 'active') ? 'success' : 'secondary'; ?>">
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
                                            <a href="club_tracker.php?delete=<?php echo $row['club_id']; ?>" 
                                               class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">
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

        </div>
    </div>
</div>

<div class="modal fade" id="addClubModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Membership</h5>
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
                    <button class="btn btn-primary" type="submit">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editClubModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="club_id" id="edit_club_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Edit Club Record</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Club Name</label>
                        <input type="text" name="club_name" id="edit_club_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Position / Role</label>
                        <input type="text" name="role" id="edit_role" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Join Date</label>
                        <input type="date" name="join_date" id="edit_join_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-info" type="submit">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('.edit-btn').on('click', function() {
        $('#edit_club_id').val($(this).data('id'));
        $('#edit_club_name').val($(this).data('name'));
        $('#edit_role').val($(this).data('role'));
        $('#edit_join_date').val($(this).data('date'));
        $('#edit_status').val($(this).data('status'));
    });
});
</script>
