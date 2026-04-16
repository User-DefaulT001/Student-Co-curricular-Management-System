<?php
?>

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
                        <input type="text" class="form-control" id="event_name" name="event_name" placeholder="e.g., Football Tournament" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_type">Event Type</label>
                        <input type="text" class="form-control" id="event_type" name="event_type" placeholder="e.g., Sports, Cultural, Academic">
                    </div>
                    
                    <div class="form-group">
                        <label for="event_date">Event Date *</label>
                        <input type="date" class="form-control" id="event_date" name="event_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Main Auditorium">
                    </div>
                    
                    <div class="form-group">
                        <label for="hours_participated">Hours Participated</label>
                        <input type="number" class="form-control" id="hours_participated" name="hours_participated" step="0.5" min="0" value="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="role_held">Role Held</label>
                        <input type="text" class="form-control" id="role_held" name="role_held" placeholder="e.g., Organizer, Participant, Volunteer">
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
