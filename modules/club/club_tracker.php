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

// --- PREPARE DATA FOR UI ---
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
        /* 这里的 Topbar 我们保持留白，或者彻底隐藏 */
        .topbar { height: 3.5rem !important; } 
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
                    </nav>

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
                            <i class="fas fa-users text-primary mr-2"></i>Club Tracker
                        </h1>
                        <div>
                            <button class="btn btn-primary btn-sm shadow-sm px-3" data-toggle="modal" data-target="#addClubModal">
                                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add New Record
                            </button>
                            <button onclick="window.print()" class="btn btn-light btn-sm shadow-sm border px-3 ml-2">
                                <i class="fas fa-print fa-sm text-gray-600 mr-1"></i> Print Report
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Clubs Joined</div>
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

                    <div class="card shadow mb-4">
                        <div class="card-header card-header-main py-3">
                            <h6 class="m-0 font-weight-bold text-white">Membership Record List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="clubTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Club Name</th>
                                            <th>Role</th>
                                            <th>Level</th>
                                            <th>Join Date</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div> </div> <?php include('../../includes/footer.php'); ?>
        </div>
    </div>

    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#clubTable').DataTable();

            // Populate Edit Modal
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
