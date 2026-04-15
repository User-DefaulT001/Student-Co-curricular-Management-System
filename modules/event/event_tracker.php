<?php
/**
 * Event Tracker Module
 * Manages event participation records with full CRUD operations
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = isset($_POST['action_type']) ? $_POST['action_type'] : '';
    
    if ($action_type === 'add') {
        // Add new event
        $event_name = trim($_POST['event_name'] ?? '');
        $event_type = trim($_POST['event_type'] ?? '');
        $event_date = trim($_POST['event_date'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $hours = floatval($_POST['hours_participated'] ?? 0);
        $role = trim($_POST['role_held'] ?? '');
        $certificate = isset($_POST['certificate_obtained']) ? 1 : 0;
        $status = $_POST['status'] ?? 'completed';
        
        // Validate input
        if (empty($event_name) || empty($event_date)) {
            $error_message = 'Event name and date are required.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO events (user_id, event_name, event_type, event_date, location, description, hours_participated, role_held, certificate_obtained, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            if ($stmt) {
                $stmt->bind_param("isssssidis", $user_id, $event_name, $event_type, $event_date, $location, $description, $hours, $role, $certificate, $status);
                
                if ($stmt->execute()) {
                    $success_message = 'Event added successfully!';
                    $action = 'list';
                } else {
                    $error_message = 'Error adding event. Please try again.';
                }
                $stmt->close();
            }
        }
    } 
    elseif ($action_type === 'update') {
        // Update event
        $event_id = intval($_POST['event_id'] ?? 0);
        $event_name = trim($_POST['event_name'] ?? '');
        $event_type = trim($_POST['event_type'] ?? '');
        $event_date = trim($_POST['event_date'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $hours = floatval($_POST['hours_participated'] ?? 0);
        $role = trim($_POST['role_held'] ?? '');
        $certificate = isset($_POST['certificate_obtained']) ? 1 : 0;
        $status = $_POST['status'] ?? 'completed';
        
        if (empty($event_name) || empty($event_date)) {
            $error_message = 'Event name and date are required.';
        } else {
            $stmt = $conn->prepare(
                "UPDATE events SET event_name = ?, event_type = ?, event_date = ?, location = ?, description = ?, hours_participated = ?, role_held = ?, certificate_obtained = ?, status = ? 
                 WHERE event_id = ? AND user_id = ?"
            );
            
            if ($stmt) {
                $stmt->bind_param("ssssssiisii", $event_name, $event_type, $event_date, $location, $description, $hours, $role, $certificate, $status, $event_id, $user_id);
                
                if ($stmt->execute()) {
                    $success_message = 'Event updated successfully!';
                    $action = 'list';
                } else {
                    $error_message = 'Error updating event. Please try again.';
                }
                $stmt->close();
            }
        }
    }
    elseif ($action_type === 'delete') {
        // Delete event
        $event_id = intval($_POST['event_id'] ?? 0);
        
        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $event_id, $user_id);
            
            if ($stmt->execute()) {
                $success_message = 'Event deleted successfully!';
            } else {
                $error_message = 'Error deleting event. Please try again.';
            }
            $stmt->close();
        }
        $action = 'list';
    }
}

// Handle edit action
if ($action === 'edit' && isset($_GET['id'])) {
    $event_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $event = $result->fetch_assoc();
            $action = 'edit';
        } else {
            $error_message = 'Event not found.';
            $action = 'list';
        }
        $stmt->close();
    }
}

// Pagination and data retrieval
$events_per_page = 4;
$current_page = max(1, intval($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $events_per_page;

$total_events = 0;
$total_hours = 0;
$completed_events = 0;

if ($role === 'admin') {
    $stats_query = "SELECT COUNT(*) AS total_events, 
                           SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_events, 
                           COALESCE(SUM(hours_participated), 0) AS total_hours 
                    FROM events";
    $stats_result = $conn->query($stats_query);
} else {
    $stats_stmt = $conn->prepare(
        "SELECT COUNT(*) AS total_events, 
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_events, 
                COALESCE(SUM(hours_participated), 0) AS total_hours 
         FROM events WHERE user_id = ?"
    );
    $stats_stmt->bind_param("i", $user_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
}

if ($stats_result && $row = $stats_result->fetch_assoc()) {
    $total_events = intval($row['total_events']);
    $completed_events = intval($row['completed_events']);
    $total_hours = floatval($row['total_hours']);
}

$total_pages = $total_events > 0 ? max(1, ceil($total_events / $events_per_page)) : 1;

$events = [];
if ($role === 'admin') {
    $list_query = "SELECT e.*, u.username 
                   FROM events e 
                   LEFT JOIN users u ON e.user_id = u.user_id 
                   ORDER BY e.event_date DESC LIMIT ? OFFSET ?";
    $list_stmt = $conn->prepare($list_query);
    $list_stmt->bind_param("ii", $events_per_page, $offset);
} else {
    // Student View: Fetch only personal records [cite: 19, 41]
    $list_query = "SELECT * FROM events WHERE user_id = ? 
                   ORDER BY event_date DESC LIMIT ? OFFSET ?";
    $list_stmt = $conn->prepare($list_query);
    $list_stmt->bind_param("iii", $user_id, $events_per_page, $offset);
}

if ($list_stmt) {
    $list_stmt->execute();
    $result = $list_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $list_stmt->close();
}

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
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <h5 class="stat-label">Total Events</h5>
                                    <div class="stat-value"><?php echo $total_events; ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <h5 class="stat-label">Completed</h5>
                                    <div class="stat-value"><?php echo $completed_events; ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <h5 class="stat-label">Total Hours</h5>
                                    <div class="stat-value"><?php echo number_format($total_hours, 1); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <h5 class="stat-label">Upcoming</h5>
                                    <div class="stat-value"><?php echo $total_events - $completed_events; ?></div>
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

                        <?php if ($total_events === 0): ?>
                            <div class="no-data">
                                <i class="fas fa-calendar-alt"></i>
                                <h5>No Events Yet</h5>
                                <p>Start by adding your first event to track your co-curricular involvement.</p>
                                <button class="btn btn-primary mt-3" data-toggle="modal" data-target="#eventModal" onclick="resetForm()">
                                    Add Your First Event
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

                            <?php if ($total_pages > 1): ?>
                                <div class="pagination-wrapper mt-4">
                                    <nav aria-label="Event page navigation">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo max(1, $current_page - 1); ?>">Previous</a>
                                            </li>
                                            <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                                                <li class="page-item <?php echo $page === $current_page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $page; ?>"><?php echo $page; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo min($total_pages, $current_page + 1); ?>">Next</a>
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
    </div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="eventForm">
                    <input type="hidden" name="action_type" id="action_type" value="add">
                    <input type="hidden" name="event_id" id="event_id" value="">
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="event_name">Event Name *</label>
                            <input type="text" class="form-control" id="event_name" name="event_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="event_type">Event Type</label>
                            <select class="form-control" id="event_type" name="event_type">
                                <option value="">Select Type...</option>
                                <option value="Competition">Competition</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Conference">Conference</option>
                                <option value="Seminar">Seminar</option>
                                <option value="Talk">Talk</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="event_date">Date *</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Building A, Room 101">
                        </div>
                        
                        <div class="form-group">
                            <label for="hours_participated">Hours Participated</label>
                            <input type="number" class="form-control" id="hours_participated" name="hours_participated" step="0.5" min="0" value="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="role_held">Role/Position</label>
                            <input type="text" class="form-control" id="role_held" name="role_held" placeholder="e.g., Team Member, Speaker">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="completed">Completed</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="upcoming">Upcoming</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="certificate_obtained" name="certificate_obtained">
                                <label class="form-check-label" for="certificate_obtained">
                                    Certificate Obtained
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe your experience..."></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <div class="footer-buttons">
                            <button type="button" class="btn btn-secondary modal-btn" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary modal-btn">Save Event</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action_type" value="delete">
                    <input type="hidden" name="event_id" id="delete_event_id" value="">
                    
                    <div class="modal-body">
                        <p>Are you sure you want to delete this event? This action cannot be undone.</p>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
            // Fetch event details via AJAX or redirect
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
        
        // Show modal with pre-filled data
        $('#eventModal').modal('show');
        <?php endif; ?>
    </script>
</body>
</html>
