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

    // Action: ADD NEW CLUB
    if ($action === 'add') {
        $club_name   = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role_held   = mysqli_real_escape_string($conn, $_POST['role']);
        $level       = mysqli_real_escape_string($conn, $_POST['level']);
        $join_date   = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $query = "INSERT INTO clubs (user_id, club_name, role, level, join_date, status, description) 
                  VALUES ('$user_id', '$club_name', '$role_held', '$level', '$join_date', '$status', '$description')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_message'] = "Club membership record added successfully!";
            header("Location: club_tracker.php"); exit();
        } else {
            $_SESSION['error_message'] = "Error: Could not save record.";
        }
    }

    // Action: UPDATE EXISTING CLUB
    if ($action === 'edit') {
        $club_id     = (int)$_POST['club_id'];
        $club_name   = mysqli_real_escape_string($conn, $_POST['club_name']);
        $role_held   = mysqli_real_escape_string($conn, $_POST['role']);
        $level       = mysqli_real_escape_string($conn, $_POST['level']);
        $join_date   = mysqli_real_escape_string($conn, $_POST['join_date']);
        $status      = mysqli_real_escape_string($conn, $_POST['status']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $query = "UPDATE clubs SET 
                  club_name='$club_name', role='$role_held', level='$level', 
                  join_date='$join_date', status='$status', description='$description' 
                  WHERE club_id=$club_id AND user_id=$user_id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_message'] = "Club record updated successfully!";
            header("Location: club_tracker.php"); exit();
        }
    }
}

// Action: DELETE (GET Request)
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $del_query = "DELETE FROM clubs WHERE club_id=$del_id AND user_id=$user_id";
    if (mysqli_query($conn, $del_query)) {
        $_SESSION['success_message'] = "Record has been deleted.";
        header("Location: club_tracker.php"); exit();
    }
}

$total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM clubs WHERE user_id=$user_id");
$total_clubs = mysqli_fetch_assoc($total_query)['count'];

$active_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM clubs WHERE user_id=$user_id AND status='active'");
$active_clubs = mysqli_fetch_assoc($active_query)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Club Tracker - Student CMS</title>

    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../../assets/style.css" rel="stylesheet">

    <style>
        .card-header-main {
            background: var(--primary-gradient) !important;
            color: white;
        }
        .stats-icon { font-size: 2.5rem; opacity: 0.3; }
        .table-action-btns { white-space: nowrap; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">
        <?php include('../../includes/sidebar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h5 class="ml-3 font-weight-bold text-primary">Club Tracker</h5>
                </nav>

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">My Memberships</h1>
                        <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addClubModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Record
                        </button>
                    </div>

                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm">
                            <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3" style="background: var(--primary-gradient) !important;">
                            <h6 class="m-0 font-weight-bold text-white">Membership Data</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Club Name</th>
                                            <th>Role</th>
                                            <th>Level</th>
                                            <th>Date Joined</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $clubs = mysqli_query($conn, "SELECT * FROM clubs WHERE user_id=$user_id ORDER BY join_date DESC");
                                        while($row = mysqli_fetch_assoc($clubs)): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($row['club_name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                                <td><span class="badge badge-info px-2"><?php echo htmlspecialchars($row['level']); ?></span></td>
                                                <td><?php echo $row['join_date']; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo ($row['status'] == 'active') ? 'success' : 'secondary'; ?> p-2">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-info btn-sm edit-btn" 
                                                            data-id="<?php echo $row['club_id']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($row['club_name']); ?>" 
                                                            data-role="<?php echo htmlspecialchars($row['role']); ?>" 
                                                            data-level="<?php echo $row['level']; ?>" 
                                                            data-date="<?php echo $row['join_date']; ?>" 
                                                            data-status="<?php echo $row['status']; ?>" 
                                                            data-desc="<?php echo htmlspecialchars($row['description']); ?>" 
                                                            data-toggle="modal" data-target="#editClubModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?delete=<?php echo $row['club_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">
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
        </div>
    </div>

    <div class="modal fade" id="addClubModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Register New Club</h5>
                        <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Club Name</label><input type="text" name="club_name" class="form-control" required></div>
                        <div class="form-group"><label>Role / Position</label><input type="text" name="role" class="form-control" required></div>
                        <div class="form-group"><label>Level</label>
                            <select name="level" class="form-control">
                                <option value="School">School</option>
                                <option value="District">District</option>
                                <option value="State">State</option>
                                <option value="National">National</option>
                            </select></div>
                        <div class="form-group"><label>Join Date</label><input type="date" name="join_date" class="form-control" required></div>
                        <div class="form-group"><label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select></div>
                        <div class="form-group"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editClubModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="club_id" id="edit_id">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Modify Record</h5>
                        <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Club Name</label><input type="text" name="club_name" id="edit_name" class="form-control"></div>
                        <div class="form-group"><label>Role</label><input type="text" name="role" id="edit_role" class="form-control"></div>
                        <div class="form-group"><label>Level</label>
                            <select name="level" id="edit_level" class="form-control">
                                <option value="School">School</option>
                                <option value="District">District</option>
                                <option value="State">State</option>
                                <option value="National">National</option>
                            </select></div>
                        <div class="form-group"><label>Date</label><input type="date" name="join_date" id="edit_date" class="form-control"></div>
                        <div class="form-group"><label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select></div>
                        <div class="form-group"><label>Description</label><textarea name="description" id="edit_desc" class="form-control"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-info text-white">Update Record</button></div>
                </form>
            </div>
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
            $('#edit_level').val($(this).data('level'));
            $('#edit_date').val($(this).data('date'));
            $('#edit_status').val($(this).data('status'));
            $('#edit_desc').val($(this).data('desc'));
        });
    });
    </script>
</body>
</html>
