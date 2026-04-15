<?php
// 1. Start the session to check if the user is logged in
session_start();

// 2. Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
require_once 'config.php';

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

// ── DATA RETRIEVAL FOR DASHBOARD STATS ──────────────────────────────────────
// Define queries based on role
if ($role === 'admin') {
    // Admin sees totals for all students
    $q_events   = "SELECT COUNT(*) as count FROM events";
    $q_merits   = "SELECT COUNT(*) as count FROM merits";
    $q_achieve  = "SELECT COUNT(*) as count FROM achievements";
    $q_clubs    = "SELECT COUNT(*) as count FROM clubs";
} else {
    $q_events   = "SELECT COUNT(*) as count FROM events WHERE user_id = $user_id";
    $q_merits   = "SELECT COUNT(*) as count FROM merits WHERE user_id = $user_id";
    $q_achieve  = "SELECT COUNT(*) as count FROM achievements WHERE user_id = $user_id";
    $q_clubs    = "SELECT COUNT(*) as count FROM clubs WHERE user_id = $user_id";
}

// Execute queries
$total_events       = mysqli_fetch_assoc(mysqli_query($conn, $q_events))['count'] ?? 0;
$total_merits       = mysqli_fetch_assoc(mysqli_query($conn, $q_merits))['count'] ?? 0;
$total_achievements = mysqli_fetch_assoc(mysqli_query($conn, $q_achieve))['count'] ?? 0;
$total_clubs        = mysqli_fetch_assoc(mysqli_query($conn, $q_clubs))['count'] ?? 0;

// 3. Include the UI fragments
include('includes/header.php'); 
include('includes/sidebar.php'); 
?>

<div id="content">
    <div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-home"></i> Dashboard</h1>
        <a href="auth.php?action=logout" class="btn btn-danger btn-sm">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <?php if ($role === 'admin'): ?>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Students' Events</div>
                            <?php endif; ?>
                            <?php if ($role === 'student'): ?>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Events</div>
                            <?php endif; ?>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_events; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <?php if ($role === 'admin'): ?>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Students' Merits</div>
                            <?php endif; ?>
                            <?php if ($role === 'student'): ?>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Merits</div>
                            <?php endif; ?>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_merits; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <?php if ($role === 'admin'): ?>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Students' Achievements</div>
                            <?php endif; ?>
                            <?php if ($role === 'student'): ?>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Achievements</div>
                            <?php endif; ?>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_achievements; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <?php if ($role === 'admin'): ?>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Students' Clubs</div>
                            <?php endif; ?>
                            <?php if ($role === 'student'): ?>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Clubs</div>
                            <?php endif; ?>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_clubs; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div>
                            <h6 class="m-0 font-weight-bold text-white">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</h6>
                        </div>
                        <span class="dashboard-badge"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'student')); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($role === 'student'): ?>
                    <p class="mb-4">The Student Co-curricular Management System is designed to help you organize your university journey in one place. Seamlessly record your event participation, track your club memberships, log your merit hours, and store your achievements to build a comprehensive record of your non-academic growth.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Quick Start</h5>
                            <ul class="dashboard-list">
                                <li>Navigate between modules using the dashboard menu to add or edit your records.</li>
                                <li>Track your contribution hours and leadership roles in real-time.</li>
                                <li>Review your co-curricullar information at any time.</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">What you can do</h5>
                            <ul class="dashboard-list">
                                <li>Centralize Data: Keep all your co-curricular information in one place.</li>
                                <li>Manage Records: Easily update and maintain your co-curricular information.</li>
                                <li>Prepare Reporting: Access a structured summary of your activities for future applications.</li>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($role === 'admin'): ?>
                    <p class="mb-4">This portal provides a high-level summary of the Student Co-curricular Management System. As an administrator, you can monitor registered students and review the collective co-curricular contributions of the student body.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Administrative Insights</h5>
                            <ul class="dashboard-list">
                                <li>User Management: View all students registered within the centralized authentication system.</li>
                                <li>Usage Summaries: Access data on total participation across the Event, Club, Merit and Achievement modules.</li>
                                <li>Data Oversight: Monitor and analyze students' co-curricular data for informed decision-making.</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Key Responsibilities</h5>
                            <ul class="dashboard-list">
                                <li>Audit Activity: Ensure student records align with university co-curricular standards.</li>
                                <li>Monitor Access: Verify secure session-based control and user registration integrity.</li>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100 text-white bg-primary">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title text-white">Launch Module</h5>
                    </div>
                    <div>
                        <a href="/Student-Co-curricular-Management-System-main/modules/event/event_tracker.php" class="btn btn-light btn-block btn-lg">
                            <i class="fas fa-calendar-alt"></i> Open Event Tracker
                        </a>
                        <a href="/Student-Co-curricular-Management-System-main/modules/club/club_tracker.php" class="btn btn-light btn-block btn-lg">
                            <i class="fas fa-users"></i> Open Club Tracker
                        </a>
                        <a href="/Student-Co-curricular-Management-System-main/modules/merit/merit_tracker.php" class="btn btn-light btn-block btn-lg">
                            <i class="fas fa-star"></i> Open Merit Tracker
                        </a>
                        <a href="/Student-Co-curricular-Management-System-main/modules/achievement/achievement_tracker.php" class="btn btn-light btn-block btn-lg">
                            <i class="fas fa-trophy"></i> Open Achievement Tracker
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card module-card">
                <div class="card-body">
                    <div class="module-card-icon bg-light text-primary"><i class="fas fa-calendar-check"></i></div>
                    <h5>Co-curricular Tracking</h5>
                    <p>Record workshops, activities, clubs, competitions, certificates and other more easily.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card module-card">
                <div class="card-body">
                    <div class="module-card-icon bg-light text-success"><i class="fas fa-chart-line"></i></div>
                    <h5>Quick Insights</h5>
                    <p>See your completed hours and engagement status at a glance.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card module-card">
                <div class="card-body">
                    <div class="module-card-icon bg-light text-warning"><i class="fas fa-book-open"></i></div>
                    <h5>Submit Records</h5>
                    <p>Keep your achievements and participation logs up to date.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card module-card">
                <div class="card-body">
                    <div class="module-card-icon bg-light text-danger"><i class="fas fa-heart"></i></div>
                    <h5>Stay Organized</h5>
                    <p>Manage your activity history with a clean, modern layout.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
// 4. Close the layout
include('includes/footer.php'); 
?>