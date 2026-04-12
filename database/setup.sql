-- Create Student CMS Database
CREATE DATABASE IF NOT EXISTS student_cms;
USE student_cms;

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Events Table (for Event Tracker Module)
CREATE TABLE IF NOT EXISTS events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_name VARCHAR(150) NOT NULL,
    event_type VARCHAR(100),
    event_date DATE NOT NULL,
    location VARCHAR(200),
    description LONGTEXT,
    hours_participated DECIMAL(5, 2),
    role_held VARCHAR(100),
    certificate_obtained BOOLEAN DEFAULT FALSE,
    status ENUM('completed', 'ongoing', 'upcoming') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_event_date (event_date)
);

-- Create Merit Table (For Merit Tracker Module)
CREATE TABLE IF NOT EXISTS merits (
    merit_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity_name VARCHAR(255) NOT NULL,
    merit_points INT NOT NULL,
    date_earned DATE NOT NULL,
    description LONGTEXT,
    status ENUM('approved', 'pending', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_date_earned (date_earned)
);

-- Insert sample admin user
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('admin', 'admin@student-cms.local', 'password', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Insert sample student user (password: password)
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('student1', 'student1@student-cms.local', 'password', 'John Doe', 'student')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Insert sample student user (password: password)
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('Jack2', 'Jack2@student-cms.local', 'password', 'Jack Smith', 'student')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Insert sample student user (password: password)
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('Adam3', 'Adam3@student-cms.local', 'password', 'Adam Johnson', 'student')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Insert sample events
INSERT INTO events (user_id, event_name, event_type, event_date, location, description, hours_participated, role_held, certificate_obtained, status) 
VALUES 
(2, 'Winter Hackathon 2025', 'Competition', '2025-12-15', 'University Building A', 'Participated in a 24-hour hackathon event focused on IoT solutions', 24, 'Team Member', TRUE, 'completed'),
(2, 'Web Development Workshop', 'Workshop', '2025-11-20', 'Computer Lab 3', 'Attended a 3-day workshop on modern web development practices', 8, 'Participant', TRUE, 'completed'),
(2, 'Annual Tech Conference', 'Conference', '2025-12-01', 'Convention Center', 'Attended keynote sessions on AI and Machine Learning trends', 6, 'Attendee', TRUE, 'completed');


-- User 1 is ADMIN, sample merits for User 2
INSERT INTO merits (user_id, activity_name, merit_points, date_earned, description, status) 
VALUES 
(2, 'Community Garden Setup', 10, '2026-03-20', 'Assisted in building 5 raised beds for the local community center garden.', 'pending'),
(2, 'Peer Mentor Session', 3, '2026-04-05', 'Conducted a 1-hour mentoring session for freshman students in Web Dev.', 'approved'),
(2, 'Dean List Peer Mentoring', 10, '2026-03-15', 'Provided 5 hours of intensive coding tutorials for first-year students struggling with PHP logic.', 'rejected'),
(2, 'Student Council Election Committee', 15, '2026-04-02', 'Assisted in the physical setup and ballot counting for the 2026 Campus Election Day.', 'approved');

-- sample merits for User 3
INSERT INTO merits (user_id, activity_name, merit_points, date_earned, description, status) 
VALUES 
(3, 'Inter-University Debate Competition', 15, '2026-02-10', 'Represented the university in the national debate championship reaching the semi-finals.', 'pending'),
(3, 'IEEE Student Branch Secretary', 20, '2026-03-01', 'Managed administrative duties and documentation for the IEEE student branch for the semester.', 'pending'),
(3, 'Basketball Team Captain', 10, '2026-01-20', 'Led the faculty basketball team during the Annual Sports Carnival.', 'pending'),
(3, 'Environmental Awareness Talk', 5, '2026-04-02', 'Organized and hosted a virtual talk on sustainable living for 50+ participants.', 'pending');

-- sample merits for User 4
INSERT INTO merits (user_id, activity_name, merit_points, date_earned, description, status) 
VALUES 
(4, 'Open Source Project Contributor', 12, '2026-03-25', 'Contributed 5 significant bug fixes and documentation updates to a community web framework.', 'pending'),
(4, 'Charity Food Bank Volunteer', 8, '2026-02-14', 'Assisted in packing and distributing food parcels to 100 local families.', 'pending'),
(4, 'Academic Excellence Award', 10, '2026-01-05', 'Received a certificate of excellence for achieving the highest GPA in the technical track.', 'pending'),
(4, 'Cultural Night Choreographer', 10, '2025-11-30', 'Choreographed a 10-minute performance for the International Student Cultural Night.', 'pending');