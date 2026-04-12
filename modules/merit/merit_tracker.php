<?php
/* Merit Tracker Module */

session_start();
require_once '../../config.php';

//AUTHENTICATION CHECK
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header("Location: ../../login.php");
    exit();
}
$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'];

// Reject any session with an unexpected role value
if (!in_array($role, ['student', 'admin'], true)) {
    session_destroy();
    header("Location: ../../login.php");
    exit();
}

// FLASH MESSAGES
$success = '';
$error   = '';

if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// HANDLE FORM SUBMISSIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // ADD  —  both student and admin can submit a merit
    if ($action === 'add') {

        $activity_name = trim($_POST['activity_name'] ?? '');
        $merit_points  = (int) ($_POST['merit_points'] ?? 0);
        $date_earned   = trim($_POST['date_earned']   ?? '');
        $description   = trim($_POST['description']   ?? '');

        if ($activity_name === '' || $merit_points < 1 || $date_earned === '') {
            $_SESSION['error_message'] = "Please fill in all required fields.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO merits (user_id, activity_name, merit_points, date_earned, description, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->bind_param("siiss", $user_id, $activity_name, $merit_points, $date_earned, $description);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_message'] = "Merit submitted for approval!";
        }

    // EDIT  —  admin: any record | student: can edit on pending only
    } elseif ($action === 'edit') {

        $merit_id      = (int) ($_POST['merit_id']      ?? 0);
        $activity_name = trim($_POST['activity_name']   ?? '');
        $merit_points  = (int) ($_POST['merit_points']  ?? 0);
        $date_earned   = trim($_POST['date_earned']     ?? '');
        $description   = trim($_POST['description']     ?? '');

        if ($merit_id < 1 || $activity_name === '' || $merit_points < 1 || $date_earned === '') {
            $_SESSION['error_message'] = "Please fill in all required fields.";

        } elseif ($role === 'admin') {
            $status = $_POST['status'] ?? 'pending';
            if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
                $status = 'pending';
            }

            $stmt = $conn->prepare("
                UPDATE merits
                SET activity_name=?, merit_points=?, date_earned=?, description=?, status=?
                WHERE merit_id=?
            ");
            $stmt->bind_param("siissi", $activity_name, $merit_points, $date_earned, $description, $status, $merit_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_message'] = "Merit record updated!";

        } else {
            $stmt = $conn->prepare("
                UPDATE merits
                SET activity_name=?, merit_points=?, date_earned=?, description=?
                WHERE merit_id=? AND user_id=? AND status='pending'
            ");
            $stmt->bind_param("siisii", $activity_name, $merit_points, $date_earned, $description, $merit_id, $user_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Merit record updated!";
            } else {
                $_SESSION['error_message'] = "Update failed. You can only edit your own pending records.";
            }
            $stmt->close();
        }

    // DELETE  —  admin only
    } elseif ($action === 'delete') {

        if ($role !== 'admin') {
            $_SESSION['error_message'] = "Unauthorized action.";
            header("Location: merit_tracker.php");
            exit();
        }

        $merit_id = (int) ($_POST['merit_id'] ?? 0);

        if ($merit_id > 0) {
            $stmt = $conn->prepare("DELETE FROM merits WHERE merit_id=?");
            $stmt->bind_param("i", $merit_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_message'] = "Merit record deleted.";
        }

    // UPDATE STATUS - admin only
    } elseif ($action === 'update_status') {

        if ($role !== 'admin') {
            $_SESSION['error_message'] = "Unauthorized action.";
            header("Location: merit_tracker.php");
            exit();
        }

        $merit_id = (int) ($_POST['merit_id'] ?? 0);
        $status   = $_POST['status'] ?? '';

        if ($merit_id > 0 && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $stmt = $conn->prepare("UPDATE merits SET status=? WHERE merit_id=?");
            $stmt->bind_param("si", $status, $merit_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_message'] = "Status updated to " . ucfirst($status) . ".";
        } else {
            $_SESSION['error_message'] = "Invalid status value.";
        }

    } else {
        $_SESSION['error_message'] = "Unknown action.";
    }

    header("Location: merit_tracker.php");
    exit();
}

// ==========================================
// DATA RETRIEVAL
// ==========================================
$merits = [];

if ($role === 'admin') {
    // Admin sees all records with the student's username
    $result = $conn->query("
        SELECT m.*, u.username
        FROM merits m
        LEFT JOIN users u ON m.user_id = u.user_id
        ORDER BY m.date_earned DESC
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $merits[] = $row;
        }
        $result->free(); // FIX #6 — free result set when done
    }
} else {
    // Student sees only their own records
    $stmt = $conn->prepare("SELECT * FROM merits WHERE user_id=? ORDER BY date_earned DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $merits[] = $row;
    }
    $stmt->close();
}

// Summary stats
$total        = count($merits);
$total_points = 0;
foreach ($merits as $m) {
    if ($m['status'] === 'approved') {
        $total_points += (int) $m['merit_points'];
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Merit Tracker - Student CMS</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../assets/style.css" rel="stylesheet">
</head>
<body id="page-top">

    <div id="wrapper">

        <?php include('../../includes/sidebar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <div class="container-fluid">

                    <!-- Page Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">Merit Tracker</h1>
                        </div>
                        <div>
                            <?php if ($role === 'student'): ?>
                                <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addMeritModal">
                                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add and view your Merit
                                </button>
                            <?php endif; ?>
                            <?php if ($role === 'admin'): ?>
                                <p class="mb-0 text-dark"> Review and manage all students' merit submissions. </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-1"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    <?php endif; ?>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Records</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total; ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-list fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Points</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_points; ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-star fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Merit Records Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h3 class="m-0 font-weight-bold text-primary">
                                <?php if ($role === 'admin'): ?>
                                    <p class="text-dark my-4"> Students' Merit Records Table</p>
                                <?php endif ?>
                                <?php if ($role === 'student'): ?>
                                    <p class="text-dark my-4"> My Merit Records</p>
                                <?php endif ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($merits)): ?>
                                <p class="text-center text-muted my-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No merit records found.
                                </p>
                            <?php else: ?>

                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No.</th>
                                            <?php if ($role === 'admin'): ?><th>Student</th><?php endif; ?>
                                            <th class="text-center">Activity Name</th>
                                            <th class="text-center">Points</th>
                                            <th class="text-center">Date Earned</th>
                                            <th class="text-center">Description</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($merits as $i => $m):
                                            $status = $m['status'] ?? 'pending';
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $i + 1; ?></td>

                                            <?php if ($role === 'admin'): ?>
                                                <td class="text-center"><?php echo htmlspecialchars($m['username'] ?? 'Unknown'); ?></td>
                                            <?php endif; ?>
                                            <td class="text-center"><?php echo htmlspecialchars($m['activity_name']); ?></td>
                                            <td class="text-center"><?php echo (int) $m['merit_points']; ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($m['date_earned']); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($m['description'] ?? '—'); ?></td>

                                            <td class="text-center">
                                                <?php
                                                    $badgeClass = 'badge-warning';
                                                    if ($status === 'approved') $badgeClass = 'badge-success';
                                                    if ($status === 'rejected') $badgeClass = 'badge-danger';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?> px-2 py-1">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </td>

                                            <td class="text-center text-nowrap">

                                                <!-- Actions -->
                                                <?php if ($role === 'admin'): ?>
                                                    <button class="btn btn-info btn-sm edit-btn"
                                                        data-id="<?php echo (int) $m['merit_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($m['activity_name'], ENT_QUOTES); ?>"
                                                        data-points="<?php echo (int) $m['merit_points']; ?>"
                                                        data-date="<?php echo htmlspecialchars($m['date_earned'], ENT_QUOTES); ?>"
                                                        data-desc="<?php echo htmlspecialchars($m['description'] ?? '', ENT_QUOTES); ?>"
                                                        data-status="<?php echo htmlspecialchars($status, ENT_QUOTES); ?>"
                                                        data-toggle="modal" data-target="#editMeritModal"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($role === 'student' && $status === 'pending'): ?>
                                                    <button class="btn btn-info btn-sm edit-btn"
                                                        data-id="<?php echo (int) $m['merit_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($m['activity_name'], ENT_QUOTES); ?>"
                                                        data-points="<?php echo (int) $m['merit_points']; ?>"
                                                        data-date="<?php echo htmlspecialchars($m['date_earned'], ENT_QUOTES); ?>"
                                                        data-desc="<?php echo htmlspecialchars($m['description'] ?? '', ENT_QUOTES); ?>"
                                                        data-toggle="modal" data-target="#editMeritModal"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($role === 'admin'): ?>
                                                    <?php if ($status === 'pending'): ?>
                                                        <form method="POST" class="d-inline ml-1">
                                                            <input type="hidden" name="action"   value="update_status">
                                                            <input type="hidden" name="status"   value="approved">
                                                            <input type="hidden" name="merit_id" value="<?php echo (int) $m['merit_id']; ?>">
                                                            <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline ml-1">
                                                            <input type="hidden" name="action"   value="update_status">
                                                            <input type="hidden" name="status"   value="rejected">
                                                            <input type="hidden" name="merit_id" value="<?php echo (int) $m['merit_id']; ?>">
                                                            <button type="submit" class="btn btn-warning btn-sm" title="Reject">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="POST" class="d-inline ml-1"
                                                        onsubmit="return confirm('Permanently delete this record?');">
                                                        <input type="hidden" name="action"   value="delete">
                                                        <input type="hidden" name="merit_id" value="<?php echo (int) $m['merit_id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div><!-- /.container-fluid -->
            </div><!-- /#content -->

            <?php include('../../includes/footer.php'); ?>
        </div><!-- /#content-wrapper -->
    </div><!-- /#wrapper -->


    <!-- MODAL --ADD MERIT (Student) -->
    <?php if ($role === 'student'): ?>
    <div class="modal fade" id="addMeritModal" tabindex="-1" role="dialog" aria-labelledby="addMeritLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" novalidate>
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMeritLabel">
                            <i class="fas fa-plus-circle text-primary mr-1"></i> Add New Merit Record
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="add_activity_name">Activity Name <span class="text-danger">*</span></label>
                                    <input type="text" name="activity_name" id="add_activity_name"
                                        class="form-control" placeholder="e.g. Debate Club Championship" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="add_merit_points">Points <span class="text-danger">*</span></label>
                                    <input type="number" name="merit_points" id="add_merit_points"
                                        class="form-control" placeholder="e.g. 10" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="add_date_earned">Date Earned <span class="text-danger">*</span></label>
                            <input type="date" name="date_earned" id="add_date_earned" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="add_description">Description</label>
                            <textarea name="description" id="add_description" class="form-control" rows="3"
                                    placeholder="Briefly describe the activity or achievement..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i> Submit for Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- MODAL --EDIT MERIT (Admin | Student, only pending status) -->
    <div class="modal fade" id="editMeritModal" tabindex="-1" role="dialog" aria-labelledby="editMeritLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" novalidate>
                    <input type="hidden" name="action"   value="edit">
                    <input type="hidden" name="merit_id" id="edit_merit_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMeritLabel">
                            <i class="fas fa-edit text-info mr-1"></i> Edit Merit Record
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="edit_activity_name">Activity Name <span class="text-danger">*</span></label>
                                    <input type="text" name="activity_name" id="edit_activity_name"
                                        class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_merit_points">Points <span class="text-danger">*</span></label>
                                    <input type="number" name="merit_points" id="edit_merit_points"
                                        class="form-control" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-<?php echo ($role === 'admin') ? '6' : '12'; ?>">
                                <div class="form-group">
                                    <label for="edit_date_earned">Date Earned <span class="text-danger">*</span></label>
                                    <input type="date" name="date_earned" id="edit_date_earned" class="form-control" required>
                                </div>
                            </div>
                            <!-- Admin can change status, student cannot -->
                            <?php if ($role === 'admin'): ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="edit_status" class="form-control" required>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save mr-1"></i> Update Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // Optional DataTable for sortable/searchable records
        if (document.getElementById('meritsTable')) {
            $('#meritsTable').DataTable({ order: [] });
        }

        // Populate Edit modal when an edit button is clicked
        document.querySelectorAll('.edit-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('edit_merit_id').value      = this.dataset.id;
                document.getElementById('edit_activity_name').value = this.dataset.name;
                document.getElementById('edit_merit_points').value  = this.dataset.points;
                document.getElementById('edit_date_earned').value   = this.dataset.date;
                document.getElementById('edit_description').value   = this.dataset.desc;

                var statusSelect = document.getElementById('edit_status');
                if (statusSelect) {
                    statusSelect.value = this.dataset.status;
                }
            });
        });
    });
    </script>

</body>
</html>