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

-- Create Clubs Table
CREATE TABLE IF NOT EXISTS clubs (
    club_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    club_name VARCHAR(150) NOT NULL,
    role VARCHAR(100) NOT NULL,
    join_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_club (user_id)
);

-- Insert sample admin user
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('admin', 'admin@student-cms.local', '$2y$10$Oys7sDIfGKbxULr8MZzFV.bF95dBB8dMXg.FshXphc.1UaGBdl/5y', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Insert sample student user (password: password)
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('student1', 'student1@student-cms.local', '$2y$10$Oys7sDIfGKbxULr8MZzFV.bF95dBB8dMXg.FshXphc.1UaGBdl/5y', 'John Doe', 'student')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Insert sample events
INSERT INTO events (user_id, event_name, event_type, event_date, location, description, hours_participated, role_held, certificate_obtained, status) 
VALUES 
(2, 'Winter Hackathon 2025', 'Competition', '2025-12-15', 'University Building A', 'Participated in a 24-hour hackathon event focused on IoT solutions', 24, 'Team Member', TRUE, 'completed'),
(2, 'Web Development Workshop', 'Workshop', '2025-11-20', 'Computer Lab 3', 'Attended a 3-day workshop on modern web development practices', 8, 'Participant', TRUE, 'completed'),
(2, 'Annual Tech Conference', 'Conference', '2025-12-01', 'Convention Center', 'Attended keynote sessions on AI and Machine Learning trends', 6, 'Attendee', TRUE, 'completed');

-- Insert sample clubs
INSERT INTO clubs (user_id, club_name, role, join_date, status) 
VALUES 
(2, 'Computer Science Society', 'Vice President', '2025-01-10', 'active'),
(2, 'Photography Club', 'Member', '2025-03-15', 'active');
