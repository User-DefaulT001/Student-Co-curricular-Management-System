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
        .table-action-btns { white-space: nowrap; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <?php include('../../includes/sidebar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                

                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users mr-2"></i>Club Tracker</h1>
                        <div>
                            <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addClubModal">
                                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Record
                            </button>
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

                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show border-left-success shadow" role="alert">
                            <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

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
                                        <?php
                                        $clubs = mysqli_query($conn, "SELECT * FROM clubs WHERE user_id=$user_id ORDER BY join_date DESC");
                                        if (mysqli_num_rows($clubs) > 0) {
                                            while($row = mysqli_fetch_assoc($clubs)) {
                                                $status_class = ($row['status'] == 'active') ? 'success' : 'secondary';
                                                echo "<tr>
                                                    <td><span class='font-weight-bold text-dark'>{$row['club_name']}</span></td>
                                                    <td>{$row['role']}</td>
                                                    <td><span class='badge badge-info px-2'>{$row['level']}</span></td>
                                                    <td>".date('d M Y', strtotime($row['join_date']))."</td>
                                                    <td><span class='badge badge-{$status_class} p-2 px-3'>".ucfirst($row['status'])."</span></td>
                                                    <td class='text-center table-action-btns'>
                                                        <button class='btn btn-circle btn-sm btn-info edit-btn' 
                                                                data-id='{$row['club_id']}' 
                                                                data-name='".htmlspecialchars($row['club_name'])."' 
                                                                data-role='".htmlspecialchars($row['role'])."' 
                                                                data-level='{$row['level']}' 
                                                                data-date='{$row['join_date']}' 
                                                                data-status='{$row['status']}' 
                                                                data-desc='".htmlspecialchars($row['description'])."' 
                                                                data-toggle='modal' data-target='#editClubModal'>
                                                            <i class='fas fa-edit'></i>
                                                        </button>
                                                        <a href='?delete={$row['club_id']}' class='btn btn-circle btn-sm btn-danger' onclick='return confirm(\"Confirm delete record?\")'>
                                                            <i class='fas fa-trash'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div> </div> <?php include('../../includes/footer.php'); ?>

        </div> </div> <div class="modal fade" id="addClubModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">Register New Membership</h5>
                        <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Club Name</label>
                                <input type="text" name="club_name" class="form-control" placeholder="e.g. Robotics Club" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Role / Position</label>
                                <input type="text" name="role" class="form-control" placeholder="e.g. President" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Level</label>
                                <select name="level" class="form-control">
                                    <option value="School">School</option>
                                    <option value="District">District</option>
                                    <option value="State">State</option>
                                    <option value="National">National</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Date Joined</label>
                                <input type="date" name="join_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">Membership Status</label>
                                <select name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Responsibilities / Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Briefly describe your duties..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary px-4">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editClubModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg">
                <form method="POST" id="editForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="club_id" id="edit_id">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title font-weight-bold">Edit Membership Record</h5>
                        <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 form-group"><label class="font-weight-bold">Club Name</label>
                                <input type="text" name="club_name" id="edit_name" class="form-control" required></div>
                            <div class="col-md-6 form-group"><label class="font-weight-bold">Role</label>
                                <input type="text" name="role" id="edit_role" class="form-control" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group"><label class="font-weight-bold">Level</label>
                                <select name="level" id="edit_level" class="form-control">
                                    <option value="School">School</option>
                                    <option value="District">District</option>
                                    <option value="State">State</option>
                                    <option value="National">National</option>
                                </select></div>
                            <div class="col-md-4 form-group"><label class="font-weight-bold">Date Joined</label>
                                <input type="date" name="join_date" id="edit_date" class="form-control" required></div>
                            <div class="col-md-4 form-group"><label class="font-weight-bold">Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select></div>
                        </div>
                        <div class="form-group"><label class="font-weight-bold">Description</label>
                            <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info px-4">Update Changes</button>
                    </div>
                </form>
            </div>
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
