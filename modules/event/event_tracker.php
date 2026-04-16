<?php
/**
 * This file serves as the main entry point and uses separate files for:
 * - event_backend.php: All business logic and database operations
 * - event_modals.php: Modal forms for add/edit/delete
 */

session_start();
require_once '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$error_message = '';
$success_message = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$event = null;

// Include backend logic
require_once 'event_backend.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Event Tracker - Student CMS</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Segoe+UI:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/style.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include('../../includes/sidebar.php'); ?>
        <div id="content">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="page-header-card mb-4">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
                        <div>
                            <h1 class="h3 mb-2 text-gray-800">Event Tracker</h1>
                            <p class="page-subtitle">Organize your participation records and monitor your activity hours in one place.</p>
                        </div>
                        <button class="btn btn-primary mt-3 mt-md-0" data-toggle="modal" data-target="#eventModal" onclick="resetForm()">
                            <i class="fas fa-plus"></i> Add New Event
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error mb-4">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics Section -->
                <div class="section-card mb-4">
                    <div class="section-card-header">
                        <div>
                            <h4>Summary</h4>
                            <p class="section-subtitle">Track your overall event participation and upcoming activities.</p>
                        </div>
                    </div>
                    <div class="row mb-4 justify-content-center">
                        <div class="col-lg-2 mb-2">
                            <div class="stat-card">
                                <h5 class="stat-label">Total Events</h5>
                                <div class="stat-value"><?php echo $total_events; ?></div>
                            </div>
                        </div>
                        <div class="col-lg-2 mb-2">
                            <div class="stat-card">
                                <h5 class="stat-label">Completed</h5>
                                <div class="stat-value"><?php echo $completed_events; ?></div>
                            </div>
                        </div>
                        <div class="col-lg-2 mb-2">
                            <div class="stat-card">
                                <h5 class="stat-label">Ongoing</h5>
                                <div class="stat-value"><?php echo $ongoing_events; ?></div>
                            </div>
                        </div>
                        <div class="col-lg-2 mb-2">
                            <div class="stat-card">
                                <h5 class="stat-label">Total Hours</h5>
                                <div class="stat-value"><?php echo number_format($total_hours, 1); ?></div>
                            </div>
                        </div>
                        <div class="col-lg-2 mb-2">
                            <div class="stat-card">
                                <h5 class="stat-label">Upcoming</h5>
                                <div class="stat-value"><?php echo $total_events - $completed_events - $ongoing_events; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Events List -->
                <div class="section-card">
                    <div class="section-card-header mb-3">
                        <div>
                            <h4>Events</h4>
                            <p class="section-subtitle">Your saved events are listed below. Edit or remove entries at any time.</p>
                        </div>
                    </div>

                    <!-- Sort Filter -->
                    <?php 
                    // Show sort filter if there are events OR if a filter is actively applied
                    $has_any_events = false;
                    if ($role === 'admin') {
                        $check_query = "SELECT COUNT(*) as cnt FROM events";
                        $check_result = $conn->query($check_query);
                        if ($check_result && $row = $check_result->fetch_assoc()) {
                            $has_any_events = intval($row['cnt']) > 0;
                        }
                    } else {
                        $check_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM events WHERE user_id = ?");
                        $check_stmt->bind_param("i", $user_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        if ($check_result && $row = $check_result->fetch_assoc()) {
                            $has_any_events = intval($row['cnt']) > 0;
                        }
                        $check_stmt->close();
                    }
                    ?>
                    <?php if ($has_any_events): ?>
                        <div class="sort-filter-section mb-4">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label for="sortBy" class="form-label"><i class="fas fa-sort"></i> Sort By:</label>
                                    <select id="sortBy" class="form-control" onchange="updateSort()">
                                        <option value="date" <?php echo $sort_by === 'date' ? 'selected' : ''; ?>>Event Date</option>
                                        <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Event Name</option>
                                        <option value="hours" <?php echo $sort_by === 'hours' ? 'selected' : ''; ?>>Hours Participated</option>
                                        <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="sortOrder" class="form-label">
                                        <span id="orderLabel"><i class="fas fa-arrow-up-down"></i> Order:</span>
                                        <span id="statusLabel" style="display: none;"><i class="fas fa-filter"></i> Filter:</span>
                                    </label>
                                    <select id="sortOrder" class="form-control" onchange="updateSort()">
                                        <option value="desc" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                                        <option value="asc" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                                    </select>
                                    <select id="statusFilter" class="form-control" style="display: none;" onchange="updateSort()">
                                        <option value="">All Statuses</option>
                                        <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="ongoing" <?php echo isset($_GET['status']) && $_GET['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                        <option value="upcoming" <?php echo isset($_GET['status']) && $_GET['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Events Display -->
                    <?php if ($total_events === 0): ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-alt"></i>
                            <h5>No Events Yet</h5>
                            <p>Start by adding your first event to track your co-curricular involvement.</p>
                            <button class="btn btn-primary mt-3" data-toggle="modal" data-target="#eventModal" onclick="resetForm()">
                                Add Your First Event
                            </button>
                        </div>
                    <?php elseif (count($events) === 0): ?>
                        <div class="no-data">
                            <i class="fas fa-search"></i>
                            <h5>No Results Found</h5>
                            <p>No events match the current filters. Try adjusting your selection.</p>
                            <button class="btn btn-secondary mt-3" onclick="location.reload()">
                                Reset Filters
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="event-grid">
                            <?php foreach ($events as $evt): ?>
                                <div class="event-card-wrap">
                                    <div class="card event-card mb-4">
                                        <div class="event-card-header">
                                            <div>
                                                <?php if ($role === 'admin' && !empty($evt['username'])): ?>
                                                    <div class="mb-1">
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($evt['username']); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                <h5 class="event-card-title"><?php echo htmlspecialchars($evt['event_name']); ?></h5>
                                                <?php if (!empty($evt['event_type'])): ?>
                                                    <span class="event-type-badge"><?php echo htmlspecialchars($evt['event_type']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="status-badge status-<?php echo $evt['status']; ?>">
                                                <?php echo ucfirst($evt['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="event-card-body">
                                            <div class="event-detail">
                                                <i class="fas fa-calendar"></i>
                                                <strong><?php echo date('M d, Y', strtotime($evt['event_date'])); ?></strong>
                                            </div>
                                            
                                            <?php if (!empty($evt['location'])): ?>
                                                <div class="event-detail">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo htmlspecialchars($evt['location']); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($evt['role_held'])): ?>
                                                <div class="event-detail">
                                                    <i class="fas fa-user-tag"></i>
                                                    <?php echo htmlspecialchars($evt['role_held']); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="event-detail">
                                                <i class="fas fa-hourglass"></i>
                                                <strong><?php echo number_format($evt['hours_participated'], 1); ?> hours</strong>
                                            </div>
                                            
                                            <?php if (!empty($evt['description'])): ?>
                                                <div class="mt-3">
                                                    <p class="text-muted"><?php echo htmlspecialchars(substr($evt['description'], 0, 150)); ?>...</p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($evt['certificate_obtained']): ?>
                                                <div class="event-detail">
                                                    <i class="fas fa-certificate" style="color: #f59e0b;"></i>
                                                    <strong>Certificate Obtained</strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="event-card-footer">
                                            <button class="btn btn-sm btn-info" onclick="editEvent(<?php echo $evt['event_id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEvent(<?php echo $evt['event_id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper mt-4">
                                <nav aria-label="Event page navigation">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo max(1, $current_page - 1); ?>&sort=<?php echo $sort_by; ?>&order=<?php echo strtolower($sort_order); ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Previous</a>
                                        </li>
                                        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                                            <li class="page-item <?php echo $page === $current_page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo strtolower($sort_order); ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>"><?php echo $page; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo min($total_pages, $current_page + 1); ?>&sort=<?php echo $sort_by; ?>&order=<?php echo strtolower($sort_order); ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php include('../../includes/footer.php'); ?>
    </div>

    <!-- Include Modal Forms -->
    <?php include 'event_modals.php'; ?>

    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function resetForm() {
            document.getElementById('eventForm').reset();
            document.getElementById('action_type').value = 'add';
            document.getElementById('event_id').value = '';
            document.getElementById('modalTitle').textContent = 'Add New Event';
            document.getElementById('hours_participated').value = '1';
        }

        function editEvent(eventId) {
            window.location.href = 'event_tracker.php?action=edit&id=' + eventId;
        }

        function deleteEvent(eventId) {
            document.getElementById('delete_event_id').value = eventId;
            $('#deleteModal').modal('show');
        }

        // If editing, populate the form
        <?php if ($action === 'edit' && $event): ?>
        document.getElementById('action_type').value = 'update';
        document.getElementById('event_id').value = '<?php echo $event['event_id']; ?>';
        document.getElementById('modalTitle').textContent = 'Edit Event';
        document.getElementById('event_name').value = '<?php echo htmlspecialchars($event['event_name']); ?>';
        document.getElementById('event_type').value = '<?php echo htmlspecialchars($event['event_type']); ?>';
        document.getElementById('event_date').value = '<?php echo $event['event_date']; ?>';
        document.getElementById('location').value = '<?php echo htmlspecialchars($event['location']); ?>';
        document.getElementById('hours_participated').value = '<?php echo $event['hours_participated']; ?>';
        document.getElementById('role_held').value = '<?php echo htmlspecialchars($event['role_held']); ?>';
        document.getElementById('status').value = '<?php echo $event['status']; ?>';
        document.getElementById('certificate_obtained').checked = <?php echo $event['certificate_obtained'] ? 'true' : 'false'; ?>;
        document.getElementById('description').value = '<?php echo htmlspecialchars($event['description']); ?>';
        
        $('#eventModal').modal('show');
        <?php endif; ?>

        function updateSort() {
            const sortBy = document.getElementById('sortBy').value;
            const sortOrder = document.getElementById('sortOrder').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            // Build URL with all parameters
            const url = new URL(window.location);
            url.searchParams.set('page', '1');
            url.searchParams.set('sort', sortBy);
            
            if (sortBy === 'status') {
                // When sorting by status, preserve status filter
                url.searchParams.set('order', 'asc');
                if (statusFilter) {
                    url.searchParams.set('status', statusFilter);
                } else {
                    url.searchParams.delete('status');
                }
            } else {
                // For other sorts, use sort order and preserve status filter
                url.searchParams.set('order', sortOrder);
                if (statusFilter) {
                    url.searchParams.set('status', statusFilter);
                } else {
                    url.searchParams.delete('status');
                }
            }
            
            window.location.href = url.toString();
        }
        
        function toggleSortOptions() {
            const sortBy = document.getElementById('sortBy').value;
            const sortOrder = document.getElementById('sortOrder');
            const statusFilter = document.getElementById('statusFilter');
            const orderLabel = document.getElementById('orderLabel');
            const statusLabel = document.getElementById('statusLabel');
            
            if (sortBy === 'status') {
                sortOrder.style.display = 'none';
                statusFilter.style.display = 'block';
                orderLabel.style.display = 'none';
                statusLabel.style.display = 'inline';
            } else {
                sortOrder.style.display = 'block';
                statusFilter.style.display = 'none';
                orderLabel.style.display = 'inline';
                statusLabel.style.display = 'none';
            }
        }
        
        // Initialize on page load
        document.getElementById('sortBy').addEventListener('change', toggleSortOptions);
        
        // Set initial state
        toggleSortOptions();
    </script>
</body>
</html>
