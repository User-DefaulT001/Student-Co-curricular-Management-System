<?php
/**
 * Achievement Tracker Module
 * Manages achievement records with full CRUD operations
 */

session_start();
require_once '../../config.php';

// ── Authentication ──────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'student';

if (!in_array($role, ['student', 'admin'], true)) {
    session_destroy();
    header("Location: ../../login.php");
    exit();
}

// ── Flash messages ──────────────────────────────────────────────────────────
$success = '';
$error   = '';

if (isset($_SESSION['success_message'])) { $success = $_SESSION['success_message']; unset($_SESSION['success_message']); }
if (isset($_SESSION['error_message']))   { $error   = $_SESSION['error_message'];   unset($_SESSION['error_message']);   }

// ── Handle POST actions ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── ADD ──
    if ($action === 'add') {
        $title        = trim($_POST['achievement_title'] ?? '');
        $type         = $_POST['achievement_type']  ?? 'Award';
        $issuing_body = trim($_POST['issuing_body']  ?? '');
        $date         = trim($_POST['achievement_date'] ?? '');
        $description  = trim($_POST['description']   ?? '');
        $level        = $_POST['level']              ?? 'University';
        $position     = trim($_POST['position_rank'] ?? '');

        $allowed_types  = ['Award','Certificate','Recognition','Scholarship','Competition','Other'];
        $allowed_levels = ['International','National','State','University','Faculty','Club'];
        if (!in_array($type,  $allowed_types,  true)) $type  = 'Award';
        if (!in_array($level, $allowed_levels, true)) $level = 'University';

        if ($title === '' || $date === '') {
            $_SESSION['error_message'] = 'Achievement title and date are required.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO achievements
                 (user_id, achievement_title, achievement_type, issuing_body, achievement_date, description, level, position_rank)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isssssss", $user_id, $title, $type, $issuing_body, $date, $description, $level, $position);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_message'] = 'Achievement added successfully!';
        }

        header("Location: achievement_tracker.php");
        exit();
    }

    // ── EDIT ──
    if ($action === 'edit') {
        $achievement_id = (int) ($_POST['achievement_id'] ?? 0);
        $title          = trim($_POST['achievement_title'] ?? '');
        $type           = $_POST['achievement_type']   ?? 'Award';
        $issuing_body   = trim($_POST['issuing_body']   ?? '');
        $date           = trim($_POST['achievement_date'] ?? '');
        $description    = trim($_POST['description']    ?? '');
        $level          = $_POST['level']               ?? 'University';
        $position       = trim($_POST['position_rank']  ?? '');

        $allowed_types  = ['Award','Certificate','Recognition','Scholarship','Competition','Other'];
        $allowed_levels = ['International','National','State','University','Faculty','Club'];
        if (!in_array($type,  $allowed_types,  true)) $type  = 'Award';
        if (!in_array($level, $allowed_levels, true)) $level = 'University';

        if ($achievement_id < 1 || $title === '' || $date === '') {
            $_SESSION['error_message'] = 'Please fill in all required fields.';
        } else {
            if ($role === 'admin') {
                $stmt = $conn->prepare(
                    "UPDATE achievements
                     SET achievement_title=?, achievement_type=?, issuing_body=?, achievement_date=?, description=?, level=?, position_rank=?
                     WHERE achievement_id=?"
                );
                $stmt->bind_param("sssssssi", $title, $type, $issuing_body, $date, $description, $level, $position, $achievement_id);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE achievements
                     SET achievement_title=?, achievement_type=?, issuing_body=?, achievement_date=?, description=?, level=?, position_rank=?
                     WHERE achievement_id=? AND user_id=?"
                );
                $stmt->bind_param("sssssssii", $title, $type, $issuing_body, $date, $description, $level, $position, $achievement_id, $user_id);
            }
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = 'Achievement updated successfully!';
            } else {
                $_SESSION['error_message'] = 'Update failed — record not found or no changes made.';
            }
            $stmt->close();
        }

        header("Location: achievement_tracker.php");
        exit();
    }

    // ── DELETE ──
    if ($action === 'delete') {
        $achievement_id = (int) ($_POST['achievement_id'] ?? 0);
        if ($achievement_id > 0) {
            if ($role === 'admin') {
                $stmt = $conn->prepare("DELETE FROM achievements WHERE achievement_id=?");
                $stmt->bind_param("i", $achievement_id);
            } else {
                $stmt = $conn->prepare("DELETE FROM achievements WHERE achievement_id=? AND user_id=?");
                $stmt->bind_param("ii", $achievement_id, $user_id);
            }
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = 'Achievement deleted.';
            } else {
                $_SESSION['error_message'] = 'Delete failed — record not found.';
            }
            $stmt->close();
        }

        header("Location: achievement_tracker.php");
        exit();
    }
}

// ── Data retrieval ──────────────────────────────────────────────────────────
$achievements = [];

if ($role === 'admin') {
    $result = $conn->query(
        "SELECT a.*, u.username
         FROM achievements a
         LEFT JOIN users u ON a.user_id = u.user_id
         ORDER BY a.achievement_date DESC"
    );
    if ($result) {
        while ($row = $result->fetch_assoc()) $achievements[] = $row;
        $result->free();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM achievements WHERE user_id=? ORDER BY achievement_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $achievements[] = $row;
    $stmt->close();
}

// Summary stats
$total        = count($achievements);
$by_type      = [];
$by_level     = [];
$intl_count   = 0;
foreach ($achievements as $a) {
    $by_type[$a['achievement_type']]  = ($by_type[$a['achievement_type']]  ?? 0) + 1;
    $by_level[$a['level']]            = ($by_level[$a['level']]            ?? 0) + 1;
    if ($a['level'] === 'International' || $a['level'] === 'National') $intl_count++;
}

$conn->close();

// ── Type badge colours ──────────────────────────────────────────────────────
function typeBadgeClass(string $type): string {
    return match($type) {
        'Award'        => 'badge-warning',
        'Certificate'  => 'badge-primary',
        'Recognition'  => 'badge-info',
        'Scholarship'  => 'badge-success',
        'Competition'  => 'badge-danger',
        default        => 'badge-secondary',
    };
}

function levelBadgeClass(string $level): string {
    return match($level) {
        'International' => 'badge-danger',
        'National'      => 'badge-warning',
        'State'         => 'badge-info',
        'University'    => 'badge-primary',
        'Faculty'       => 'badge-success',
        default         => 'badge-secondary',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Achievement Tracker - Student CMS</title>

    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Segoe+UI:400,600,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/style.css" rel="stylesheet">

    <style>
        /* ── Achievement Tracker custom styles ── */
        .ach-hero {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            border-radius: 20px;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .ach-hero::before {
            content: '\f091';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 7rem;
            opacity: 0.15;
        }
        .ach-hero h1 { color: #fff; font-size: 2rem; margin-bottom: 0.4rem; }
        .ach-hero p  { color: rgba(255,255,255,0.9); margin: 0; }

        .stat-pill {
            background: #fff;
            border-radius: 16px;
            padding: 1.2rem 1.5rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            text-align: center;
            border-top: 4px solid;
        }
        .stat-pill.gold   { border-color: #f6d365; }
        .stat-pill.orange { border-color: #fda085; }
        .stat-pill.purple { border-color: #a18cd1; }
        .stat-pill.teal   { border-color: #0abfbc; }
        .stat-pill .num   { font-size: 2.2rem; font-weight: 700; color: #1e293b; line-height: 1; }
        .stat-pill .lbl   { font-size: 0.78rem; text-transform: uppercase; letter-spacing: .08em; color: #64748b; margin-top: 0.4rem; }

        .ach-table thead th { background: linear-gradient(135deg,#f6d365,#fda085); color: #fff; border: none; }
        .ach-table tbody tr:hover { background-color: #fffbf0; }
        .ach-table td { vertical-align: middle; }

        .no-ach {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }
        .no-ach i { font-size: 3.5rem; margin-bottom: 1rem; color: #fda085; }

        .modal-header-ach {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            color: #fff;
            border-radius: 20px 20px 0 0;
        }
        .modal-header-ach .modal-title { color: #fff; font-weight: 700; }
        .modal-header-ach .close       { color: #fff; opacity: 1; }
        .modal-content { border-radius: 20px !important; overflow: hidden; border: none; box-shadow: 0 24px 80px rgba(0,0,0,0.2); }

        .btn-ach-primary {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1.3rem;
            transition: transform .2s, box-shadow .2s;
        }
        .btn-ach-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(253,160,133,0.45); color: #fff; }

        .badge { font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 8px; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">

    <?php include('../../includes/sidebar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid pt-4">

                <!-- ── Hero Header ── -->
                <div class="ach-hero d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h1><i class="fas fa-trophy mr-2"></i>Achievement Tracker</h1>
                        <p>
                            <?php if ($role === 'admin'): ?>
                                Review and manage all students' achievement records.
                            <?php else: ?>
                                Record your awards, certificates, and recognitions in one place.
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($role === 'student'): ?>
                        <button class="btn-ach-primary mt-3 mt-md-0 btn btn-sm"
                                data-toggle="modal" data-target="#addAchModal">
                            <i class="fas fa-plus mr-1"></i> Add Achievement
                        </button>
                    <?php endif; ?>
                </div>

                <!-- ── Flash messages ── -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-1"></i> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- ── Stats ── -->
                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="stat-pill gold">
                            <div class="num"><?php echo $total; ?></div>
                            <div class="lbl">Total Achievements</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="stat-pill orange">
                            <div class="num"><?php echo $by_type['Award'] ?? 0; ?></div>
                            <div class="lbl">Awards</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="stat-pill purple">
                            <div class="num"><?php echo $by_type['Certificate'] ?? 0; ?></div>
                            <div class="lbl">Certificates</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="stat-pill teal">
                            <div class="num"><?php echo $intl_count; ?></div>
                            <div class="lbl">Intl / National</div>
                        </div>
                    </div>
                </div>

                <!-- ── Table ── -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3" style="background: linear-gradient(135deg,#f6d365,#fda085);">
                        <h6 class="m-0 font-weight-bold text-white">
                            <?php echo ($role === 'admin') ? "All Students' Achievement Records" : "My Achievement Records"; ?>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($achievements)): ?>
                            <div class="no-ach">
                                <i class="fas fa-trophy d-block"></i>
                                <p class="font-weight-bold mb-1">No achievements yet</p>
                                <p class="text-muted small">Click "Add Achievement" to log your first record.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered ach-table mb-0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <?php if ($role === 'admin'): ?><th>Student</th><?php endif; ?>
                                            <th>Achievement</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-center">Level</th>
                                            <th>Issuing Body</th>
                                            <th class="text-center">Date</th>
                                            <th>Position / Rank</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($achievements as $i => $a): ?>
                                        <tr>
                                            <td class="text-center"><?php echo $i + 1; ?></td>
                                            <?php if ($role === 'admin'): ?>
                                                <td><?php echo htmlspecialchars($a['username'] ?? '—'); ?></td>
                                            <?php endif; ?>
                                            <td>
                                                <strong><?php echo htmlspecialchars($a['achievement_title']); ?></strong>
                                                <?php if (!empty($a['description'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($a['description'], 0, 80)) . (mb_strlen($a['description']) > 80 ? '…' : ''); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo typeBadgeClass($a['achievement_type']); ?>">
                                                    <?php echo htmlspecialchars($a['achievement_type']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo levelBadgeClass($a['level']); ?>">
                                                    <?php echo htmlspecialchars($a['level']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($a['issuing_body'] ?: '—'); ?></td>
                                            <td class="text-center text-nowrap">
                                                <?php echo date('d M Y', strtotime($a['achievement_date'])); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($a['position_rank'] ?: '—'); ?></td>
                                            <td class="text-center text-nowrap">
                                                <!-- Edit -->
                                                <button class="btn btn-info btn-sm edit-btn"
                                                    data-id="<?php echo (int)$a['achievement_id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($a['achievement_title'], ENT_QUOTES); ?>"
                                                    data-type="<?php echo htmlspecialchars($a['achievement_type'],  ENT_QUOTES); ?>"
                                                    data-body="<?php echo htmlspecialchars($a['issuing_body'] ?? '', ENT_QUOTES); ?>"
                                                    data-date="<?php echo htmlspecialchars($a['achievement_date'],  ENT_QUOTES); ?>"
                                                    data-desc="<?php echo htmlspecialchars($a['description'] ?? '', ENT_QUOTES); ?>"
                                                    data-level="<?php echo htmlspecialchars($a['level'],           ENT_QUOTES); ?>"
                                                    data-pos="<?php echo htmlspecialchars($a['position_rank'] ?? '',ENT_QUOTES); ?>"
                                                    data-toggle="modal" data-target="#editAchModal"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Delete -->
                                                <form method="POST" class="d-inline ml-1"
                                                      onsubmit="return confirm('Permanently delete this achievement?');">
                                                    <input type="hidden" name="action"         value="delete">
                                                    <input type="hidden" name="achievement_id" value="<?php echo (int)$a['achievement_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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


<!-- ═══════════════════════════════════════════
     MODAL — ADD ACHIEVEMENT (student only)
════════════════════════════════════════════ -->
<?php if ($role === 'student'): ?>
<div class="modal fade" id="addAchModal" tabindex="-1" role="dialog" aria-labelledby="addAchLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" novalidate>
                <input type="hidden" name="action" value="add">
                <div class="modal-header modal-header-ach">
                    <h5 class="modal-title" id="addAchLabel">
                        <i class="fas fa-plus-circle mr-1"></i> Add New Achievement
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Achievement Title <span class="text-danger">*</span></label>
                                <input type="text" name="achievement_title" class="form-control"
                                       placeholder="e.g. Best Project Award – Hackathon 2025" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <select name="achievement_type" class="form-control" required>
                                    <option value="Award">Award</option>
                                    <option value="Certificate">Certificate</option>
                                    <option value="Recognition">Recognition</option>
                                    <option value="Scholarship">Scholarship</option>
                                    <option value="Competition">Competition</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Issuing Body / Organisation</label>
                                <input type="text" name="issuing_body" class="form-control"
                                       placeholder="e.g. UTAR Faculty of ICT">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Received <span class="text-danger">*</span></label>
                                <input type="date" name="achievement_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Level</label>
                                <select name="level" class="form-control">
                                    <option value="International">International</option>
                                    <option value="National">National</option>
                                    <option value="State">State</option>
                                    <option value="University" selected>University</option>
                                    <option value="Faculty">Faculty</option>
                                    <option value="Club">Club</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position / Rank</label>
                                <input type="text" name="position_rank" class="form-control"
                                       placeholder="e.g. 1st Place, Dean's List">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Briefly describe the achievement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ach-primary btn">
                        <i class="fas fa-save mr-1"></i> Save Achievement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     MODAL — EDIT ACHIEVEMENT (student & admin)
════════════════════════════════════════════ -->
<div class="modal fade" id="editAchModal" tabindex="-1" role="dialog" aria-labelledby="editAchLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" novalidate>
                <input type="hidden" name="action"         value="edit">
                <input type="hidden" name="achievement_id" id="edit_ach_id">
                <div class="modal-header modal-header-ach">
                    <h5 class="modal-title" id="editAchLabel">
                        <i class="fas fa-edit mr-1"></i> Edit Achievement
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Achievement Title <span class="text-danger">*</span></label>
                                <input type="text" name="achievement_title" id="edit_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <select name="achievement_type" id="edit_type" class="form-control" required>
                                    <option value="Award">Award</option>
                                    <option value="Certificate">Certificate</option>
                                    <option value="Recognition">Recognition</option>
                                    <option value="Scholarship">Scholarship</option>
                                    <option value="Competition">Competition</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Issuing Body / Organisation</label>
                                <input type="text" name="issuing_body" id="edit_body" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Received <span class="text-danger">*</span></label>
                                <input type="date" name="achievement_date" id="edit_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Level</label>
                                <select name="level" id="edit_level" class="form-control">
                                    <option value="International">International</option>
                                    <option value="National">National</option>
                                    <option value="State">State</option>
                                    <option value="University">University</option>
                                    <option value="Faculty">Faculty</option>
                                    <option value="Club">Club</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position / Rank</label>
                                <input type="text" name="position_rank" id="edit_pos" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ach-primary btn">
                        <i class="fas fa-save mr-1"></i> Update Achievement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ── Scripts ── -->
<script src="../../vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_ach_id').value = this.dataset.id;
            document.getElementById('edit_title').value  = this.dataset.title;
            document.getElementById('edit_type').value   = this.dataset.type;
            document.getElementById('edit_body').value   = this.dataset.body;
            document.getElementById('edit_date').value   = this.dataset.date;
            document.getElementById('edit_desc').value   = this.dataset.desc;
            document.getElementById('edit_level').value  = this.dataset.level;
            document.getElementById('edit_pos').value    = this.dataset.pos;
        });
    });
});
</script>

</body>
</html>