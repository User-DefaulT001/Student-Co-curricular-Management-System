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
if (isset($_SESSION['success_message'])) { $success = $_SESSION['success_message']; unset($_SESSION['success_message']); }

// --- DATABASE LOGIC: POST ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action: ADD NEW CLUB (Only Students)
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

        // Admin can update anything, Student only their own
        if ($role === 'admin') {
            $query = "UPDATE clubs SET club_name='$club_name', role='$role_held', join_date='$join_date', status='$status' WHERE club_id=$club_id";
        } else {
            $query = "UPDATE clubs SET club_name='$club_name', role='$role_held', join_date='$join_date', status='$status' WHERE club_id=$club_id AND user_id=$user_id";
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
        $_SESSION['success_message'] = "Record has been deleted.";
        header("Location: club_tracker.php"); exit();
    }
}

// --- PREPARE DATA FOR UI ---
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
        .card-header-main { background: linear-gradient(80deg, #4e73df 10%, #224abe 100%) !important; color: white; }
        .stats-icon { font-size: 2.5rem; opacity: 0.3; }
        .btn-circle { width: 30px; height: 30px; padding: 6px 0px; border-radius: 15px; text-align: center; font-size: 12px; line-height: 1.42857; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('../../includes/sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users mr-2"></i>Club Tracker</h1>
                        <div>
                            <?php if($role === 'student'): ?>
                            <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addClubModal">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Record
                            </button>
                            <?php endif; ?>
                            <button onclick="window.print()" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-download fa-sm text-white-50"></i> Print Report
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                <?php echo ($role === 'admin') ? "Global Club Records" : "Total Clubs Joined"; ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_clubs; ?> Records</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-university stats-icon text-primary"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Memberships</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $active_clubs; ?> Active</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-id-badge stats-icon text-success"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
                            <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header card-header-main py-3">
                            <h6 class="m-0 font-weight-bold text-white">
                                <?php echo ($role === 'admin') ? "All Student Memberships" : "My Membership Record List"; ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="clubTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <?php if($role === 'admin'): ?><th>Student</th><?php endif; ?>
                                            <th>Club Name</th>
                                            <th>Role</th>
                                            <th>Join Date</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $clubs = mysqli_query($conn, $table_query);
                                        while($row = mysqli_fetch_assoc($clubs)): 
                                            $status_class = ($row['status'] == 'active') ? 'success' : 'secondary';
                                        ?>
                                        <tr>
                                            <?php if($role === 'admin'): ?>
                                                <td><span class='badge badge-dark'><?php echo htmlspecialchars($row['username'] ?? 'Unknown'); ?></span></td>
                                            <?php endif; ?>
                                            <td><span class='font-weight-bold text-dark'><?php echo htmlspecialchars($row['club_name']); ?></span></td>
                                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['join_date'])); ?></td>
                                            <td><span class='badge badge-<?php echo $status_class; ?> p-2 px-3'><?php echo ucfirst($row['status']); ?></span></td>
                                            <td class='text-center'>
                                                <button class='btn btn-circle btn-sm btn-info edit-btn' 
                                                        data-id='<?php echo $row['club_id']; ?>' 
                                                        data-name='<?php echo htmlspecialchars($row['club_name']); ?>' 
                                                        data-role='<?php echo htmlspecialchars($row['role']); ?>' 
                                                        data-date='<?php echo $row['join_date']; ?>' 
                                                        data-status='<?php echo $row['status']; ?>' 
                                                        data-toggle='modal' data-target='#editClubModal'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <a href='?delete=<?php echo $row['club_id']; ?>' class='btn btn-circle btn-sm btn-danger' onclick='return confirm("Confirm delete?")'>
                                                    <i class='fas fa-trash'></i>
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
            <?php include('../../includes/footer.php'); ?>
        </div> 
    </div>

    <div class="modal fade" id="addClubModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="" method="POST" class="modal-content">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Club Membership</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Club Name</label><input type="text" name="club_name" class="form-control" required></div>
                    <div class="form-group"><label>Role</label><input type="text" name="role" class="form-control" required></div>
                    <div class="form-group"><label>Join Date</label><input type="date" name="join_date" class="form-control" required></div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Record</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editClubModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="" method="POST" class="modal-content">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="club_id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Membership</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Club Name</label><input type="text" name="club_name" id="edit_name" class="form-control" required></div>
                    <div class="form-group"><label>Role</label><input type="text" name="role" id="edit_role" class="form-control" required></div>
                    <div class="form-group"><label>Join Date</label><input type="date" name="join_date" id="edit_date" class="form-control" required></div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Update Record</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#clubTable').DataTable();
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
