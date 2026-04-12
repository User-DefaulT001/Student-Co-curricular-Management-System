<?php
// 1. Start the session to check if the user is logged in
session_start();

// 2. Access Control: If no session exists, send them back to login.php (bypass login comment this section out)

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


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
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div>
                            <h6 class="m-0 font-weight-bold text-white">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</h6>
                            <p class="mb-0 text-white-50">You can log, manage, and review your co-curricular events from the Event Tracker module.</p>
                        </div>
                        <span class="dashboard-badge"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'student')); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-4">The Event Tracker helps you capture participation, store certificates, and visualize your progress over time.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Quick Start</h5>
                            <ul class="dashboard-list">
                                <li>Open Event Tracker to add participation records.</li>
                                <li>Track completed and upcoming events.</li>
                                <li>Review hours, certificates, and roles.</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">What you can do</h5>
                            <ul class="dashboard-list">
                                <li>Manage events easily with one click.</li>
                                <li>Keep your records centralized and secure.</li>
                                <li>Prepare faster for your co-curricular reporting.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100 text-white bg-primary">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title text-white">Launch Module</h5>
                        <p class="card-text text-white">Start your journey with the Event Tracker and keep your activity records neat and visual.</p>
                    </div>
                    <div>
                        <a href="/Student-Co-curricular-Management-System-main/modules/event/event_tracker.php" class="btn btn-light btn-block btn-lg">
                            <i class="fas fa-calendar-alt"></i> Open Event Tracker
                        </a>
                        <div class="mt-4 text-white-75">
                            <small>Other modules coming soon:</small>
                            <div class="mt-2">
                                <span class="badge badge-light text-dark">Club Tracker</span>
                                <span class="badge badge-light text-dark">Merit Tracker</span>
                                <span class="badge badge-light text-dark">Achievement Tracker</span>
                            </div>
                        </div>
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
                    <h5>Event Tracking</h5>
                    <p>Record workshops, seminars, competitions, and certificates easily.</p>
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
