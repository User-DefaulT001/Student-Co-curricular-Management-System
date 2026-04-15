<?php
session_start();
require_once '../../config.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'student';

// --- FLASH MESSAGES ---
$success = '';
$error   = '';

if (isset($_SESSION['success_message'])) { $success = $_SESSION['success_message']; unset($_SESSION['success_message']); }
if (isset($_SESSION['error_message']))   { $error   = $_SESSION['error_message'];   unset($_SESSION['error_message']);   }

// --- DATABASE LOGIC: POST ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action: ADD NEW CLUB (Usually only students add their own)
    if ($action === 'add' && $role === 'student') {
        $club_name   = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role_held   = mysqli_real_escape_string($conn, $_POST['role']);
        $join_date   = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);

        $query = "INSERT INTO clubs (user_id, club_name, role, join_date, status, created_at)  
                  VALUES ('$user_id', '$club_name', '$role_held', '$join_date', '$status', NOW())";

        if (mysqli_query($conn, $query)) {
            $_SESSION['success_message'] = "Club membership record added successfully!";
            header("Location: club_tracker.php"); exit();
        }
    }

    // Action: UPDATE EXISTING CLUB
    if ($action === 'edit') {
        $club_id     = (int)$_POST['club_id'];
        $club_name   = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role_held   = mysqli_real_escape_string($conn, $_POST['role']);
        $join_date   = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);

        // FIX: If admin, ignore user_id check. If student, ensure they own the record.
        if ($role === 'admin') {
            $query = "UPDATE clubs SET 
                      club_name='$club_name', role='$role_held',
                      join_date='$join_date', status='$status'
                      WHERE club_id=$club_id";
        } else {
            $query = "UPDATE clubs SET 
                      club_name='$club_name', role='$role_held',
                      join_date='$join_date', status='$status'
                      WHERE club_id=$club_id AND user_id=$user_id";
        }
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_message'] = "Club record updated successfully!";
            header("Location: club_tracker.php"); exit();
        }
    }
}

// Action: DELETE (GET Request)
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $del_query = ($role === 'admin') ? "DELETE FROM clubs WHERE club_id=$del_id" : "DELETE FROM clubs WHERE club_id=$del_id AND user_id=$user_id";
    if (mysqli_query($conn, $del_query)) {
        $_SESSION['success_message'] = "Record deleted.";
        header("Location: club_tracker.php"); exit();
    }
}
    
    // FIX: If admin, delete any. If student, delete only own.
if ($role === 'admin') {
    $total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM clubs");
    $active_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM clubs WHERE status='active'");
    $table_query = "SELECT c.*, u.username FROM clubs c LEFT JOIN users u ON c.user_id = u.user_id ORDER BY c.join_date DESC";
} else {
    $total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM clubs WHERE user_id=$user_id");
    $active_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM clubs WHERE user_id=$user_id AND status='active'");
    $table_query = "SELECT * FROM clubs WHERE user_id=$user_id ORDER BY join_date DESC";
}
$total_clubs = mysqli_fetch_assoc($total_query)['count'];
$active_clubs = mysqli_fetch_assoc($active_query)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Club Tracker - Student CMS</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../../assets/style.css" rel="stylesheet">
    <style>
        .card-header-main { background: var(--primary-gradient) !important; color: white; }
        .stats-icon { font-size: 2.5rem; opacity: 0.3; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('../../includes/sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content" class="container-fluid mt-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Club Tracker (<?php echo ucfirst($role); ?>)</h1>
                    <div>
                        <?php if($role === 'student'): ?>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addClubModal">Add Record</button>
                        <?php endif; ?>
                        <button onclick="window.print()" class="btn btn-secondary btn-sm">Print</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">Total Records: <?php echo $total_clubs; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">Active Now: <?php echo $active_clubs; ?></div>
                        </div>
                    </div>
                </div>

                <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header card-header-main">Club Membership List</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="clubTable" width="100%">
                                <thead>
                                    <tr>
                                        <?php if($role === 'admin'): ?><th>Student</th><?php endif; ?>
                                        <th>Club</th><th>Role</th><th>Date</th><th>Status</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $result = mysqli_query($conn, $table_query);
                                    while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <?php if($role === 'admin'): ?><td><?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?></td><?php endif; ?>
                                        <td><?php echo htmlspecialchars($row['club_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                                        <td><?php echo $row['join_date']; ?></td>
                                        <td><span class="badge badge-<?php echo ($row['status']=='active'?'success':'secondary'); ?>"><?php echo $row['status']; ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-btn" 
                                                data-id="<?php echo $row['club_id']; ?>" 
                                                data-name="<?php echo $row['club_name']; ?>" 
                                                data-role="<?php echo $row['role']; ?>" 
                                                data-date="<?php echo $row['join_date']; ?>" 
                                                data-status="<?php echo $row['status']; ?>" 
                                                data-toggle="modal" data-target="#editClubModal">Edit</button>
                                            <a href="?delete=<?php echo $row['club_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Del</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('../../includes/footer.php'); ?>
        </div>
    </div>

    <div class="modal fade" id="addClubModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="" method="POST" class="modal-content">
                <input type="hidden" name="action" value="add">
                <div class="modal-header"><h5>Add New Club</h5></div>
                <div class="modal-body">
                    <input type="text" name="club_name" class="form-control mb-2" placeholder="Club Name" required>
                    <input type="text" name="role" class="form-control mb-2" placeholder="Your Role" required>
                    <input type="date" name="join_date" class="form-control mb-2" required>
                    <select name="status" class="form-control"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editClubModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="" method="POST" class="modal-content">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="club_id" id="edit_id">
                <div class="modal-header"><h5>Edit Record</h5></div>
                <div class="modal-body">
                    <label>Club Name</label><input type="text" name="club_name" id="edit_name" class="form-control mb-2" required>
                    <label>Role</label><input type="text" name="role" id="edit_role" class="form-control mb-2" required>
                    <label>Join Date</label><input type="date" name="join_date" id="edit_date" class="form-control mb-2" required>
                    <label>Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Update Changes</button></div>
            </form>
        </div>
    </div>

    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.edit-btn').on('click', function() {
                $('#edit_id').val($(this).data('id'));
                $('#edit_name').val($(this).data('name'));
                $('#edit_role').val($(this).data('role'));
                $('#edit_date').val($(this).data('date'));
                $('#edit_status').val($(this).data('status'));
            });
        });
    </script>
</body>
</html>






