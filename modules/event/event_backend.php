<?php

// This file contains all PHP logic

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
        $role_held = trim($_POST['role_held'] ?? '');
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
                $stmt->bind_param("isssssidis", $user_id, $event_name, $event_type, $event_date, $location, $description, $hours, $role_held, $certificate, $status);
                
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
        $role_held = trim($_POST['role_held'] ?? '');
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
                $stmt->bind_param("ssssssiisii", $event_name, $event_type, $event_date, $location, $description, $hours, $role_held, $certificate, $status, $event_id, $user_id);
                
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

// Sorting and filtering logic
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$sort_order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Valid sort/status options
$valid_sorts = ['date', 'hours', 'name', 'status'];
$valid_statuses = ['completed', 'ongoing', 'upcoming'];

if (!in_array($sort_by, $valid_sorts)) {
    $sort_by = 'date';
}
if ($status_filter && !in_array($status_filter, $valid_statuses)) {
    $status_filter = '';
}

// Build ORDER BY clause
$order_by_clause = '';
switch ($sort_by) {
    case 'hours':
        $order_by_clause = "hours_participated $sort_order";
        break;
    case 'name':
        $order_by_clause = "event_name $sort_order";
        break;
    case 'status':
        $order_by_clause = "status $sort_order";
        break;
    case 'date':
    default:
        $order_by_clause = "event_date $sort_order";
        break;
}

// Pagination
$events_per_page = 4;
$current_page = max(1, intval($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $events_per_page;

// Fetch global stats
if ($role === 'admin') {
    $stats_query = "SELECT COUNT(*) AS total_events, 
                           SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_events, 
                           COALESCE(SUM(hours_participated), 0) AS total_hours 
                    FROM events" . ($status_filter ? " WHERE status = '$status_filter'" : "");
    $stats_result = $conn->query($stats_query);
} else {
    if ($status_filter) {
        $stats_stmt = $conn->prepare(
            "SELECT COUNT(*) AS total_events, 
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_events, 
                    COALESCE(SUM(hours_participated), 0) AS total_hours 
             FROM events WHERE user_id = ? AND status = ?"
        );
        $stats_stmt->bind_param("is", $user_id, $status_filter);
    } else {
        $stats_stmt = $conn->prepare(
            "SELECT COUNT(*) AS total_events, 
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_events, 
                    COALESCE(SUM(hours_participated), 0) AS total_hours 
             FROM events WHERE user_id = ?"
        );
        $stats_stmt->bind_param("i", $user_id);
    }
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
}

$total_events = 0;
$completed_events = 0;
$total_hours = 0;

if ($stats_result && $row = $stats_result->fetch_assoc()) {
    $total_events = intval($row['total_events']);
    $completed_events = intval($row['completed_events']);
    $total_hours = floatval($row['total_hours']);
}

$total_pages = $total_events > 0 ? max(1, ceil($total_events / $events_per_page)) : 1;

// Fetch events with filtering and pagination
$events = [];
if ($role === 'admin') {
    if ($status_filter) {
        $list_query = "SELECT e.*, u.username 
                       FROM events e 
                       LEFT JOIN users u ON e.user_id = u.user_id 
                       WHERE e.status = ?
                       ORDER BY e.$order_by_clause LIMIT ? OFFSET ?";
        $list_stmt = $conn->prepare($list_query);
        $list_stmt->bind_param("sii", $status_filter, $events_per_page, $offset);
    } else {
        $list_query = "SELECT e.*, u.username 
                       FROM events e 
                       LEFT JOIN users u ON e.user_id = u.user_id 
                       ORDER BY e.$order_by_clause LIMIT ? OFFSET ?";
        $list_stmt = $conn->prepare($list_query);
        $list_stmt->bind_param("ii", $events_per_page, $offset);
    }
} else {
    if ($status_filter) {
        $list_query = "SELECT * FROM events WHERE user_id = ? AND status = ?
                       ORDER BY $order_by_clause LIMIT ? OFFSET ?";
        $list_stmt = $conn->prepare($list_query);
        $list_stmt->bind_param("isii", $user_id, $status_filter, $events_per_page, $offset);
    } else {
        $list_query = "SELECT * FROM events WHERE user_id = ?
                       ORDER BY $order_by_clause LIMIT ? OFFSET ?";
        $list_stmt = $conn->prepare($list_query);
        $list_stmt->bind_param("iii", $user_id, $events_per_page, $offset);
    }
}

if ($list_stmt) {
    $list_stmt->execute();
    $result = $list_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $list_stmt->close();
}
