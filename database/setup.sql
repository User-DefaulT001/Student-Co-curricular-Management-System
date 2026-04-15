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

-- Create Achievements Table
CREATE TABLE IF NOT EXISTS achievements (
    achievement_id    INT PRIMARY KEY AUTO_INCREMENT,
    user_id           INT NOT NULL,
    achievement_title VARCHAR(200) NOT NULL,
    achievement_type  ENUM('Award','Certificate','Recognition','Scholarship','Competition','Other') DEFAULT 'Award',
    issuing_body      VARCHAR(200),
    achievement_date  DATE NOT NULL,
    description       LONGTEXT,
    level             ENUM('International','National','State','University','Faculty','Club') DEFAULT 'University',
    position_rank     VARCHAR(100),
    certificate_file  VARCHAR(255),
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id        (user_id),
    INDEX idx_achievement_date (achievement_date)
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

-- Insert sample events
INSERT INTO events (user_id, event_name, event_type, event_date, location, description, hours_participated, role_held, certificate_obtained, status) 
VALUES 
(2, 'Winter Hackathon 2025', 'Competition', '2025-12-15', 'University Building A', 'Participated in a 24-hour hackathon event focused on IoT solutions', 24, 'Team Member', TRUE, 'completed'),
(2, 'Web Development Workshop', 'Workshop', '2025-11-20', 'Computer Lab 3', 'Attended a 3-day workshop on modern web development practices', 8, 'Participant', TRUE, 'completed'),
(2, 'Annual Tech Conference', 'Conference', '2025-12-01', 'Convention Center', 'Attended keynote sessions on AI and Machine Learning trends', 6, 'Attendee', TRUE, 'completed');

-- Insert sample events
INSERT INTO events (user_id, event_name, event_type, event_date, location, description, hours_participated, role_held, certificate_obtained, status) 
VALUES 
(3, 'AI Ethics Debate Night', 'Discussion', '2025-03-22', 'Innovation Hub', 'Participated in a panel discussion regarding the societal impact of LLMs', 3, 'Team Member', TRUE, 'completed'),
(3, 'Spring Startup Pitch', 'Competition', '2025-04-12', 'Business School', 'Presented a business model for a sustainable tech startup to investors', 15, 'Team Lead', TRUE, 'pending'),
(3, 'Data Science Bootcamp', 'Workshop', '2025-05-20', 'Science Park', 'Intensive hands-on training for Python data analysis and visualization', 16, 'Participant', TRUE, 'completed');

-- Insert sample clubs
INSERT INTO clubs (user_id, club_name, role, join_date, status)
VALUES 
(2, 'Computer Science Society', 'Vice President', '2025-01-10', 'active'),
(2, 'Photography Club', 'Member', '2025-03-15', 'active');

-- Insert sample clubs
INSERT INTO clubs (user_id, club_name, role, join_date, status) 
VALUES 
(3, 'Blockchain Club', 'President', '2025-10-10', 'active'),
(3, 'Robotics Club', 'Member', '2025-11-5', 'active');

-- Sample merits for student1 
INSERT INTO merits (user_id, activity_name, merit_points, date_earned, description, status) 
VALUES 
(2, 'Community Garden Setup', 10, '2025-03-20', 'Assisted in building 5 raised beds for the local community center garden.', 'pending'),
(2, 'Peer Mentor Session', 3, '2025-04-05', 'Conducted a 1-hour mentoring session for freshman students in Web Dev.', 'approved'),
(2, 'Dean List Peer Mentoring', 10, '2025-03-15', 'Provided 5 hours of intensive coding tutorials for first-year students struggling with PHP logic.', 'rejected'),
(2, 'Student Council Election Committee', 15, '2025-04-02', 'Assisted in the physical setup and ballot counting for the 2025 Campus Election Day.', 'approved');

-- sample merits for Jack2
INSERT INTO merits (user_id, activity_name, merit_points, date_earned, description, status) 
VALUES 
(3, 'Inter-University Debate Competition', 15, '2025-02-10', 'Represented the university in the national debate championship reaching the semi-finals.', 'pending'),
(3, 'IEEE Student Branch Secretary', 20, '2025-03-01', 'Managed administrative duties and documentation for the IEEE student branch for the semester.', 'pending'),
(3, 'Basketball Team Captain', 10, '2025-01-20', 'Led the faculty basketball team during the Annual Sports Carnival.', 'pending'),
(3, 'Environmental Awareness Talk', 5, '2025-04-02', 'Organized and hosted a virtual talk on sustainable living for 50+ participants.', 'pending');

-- Sample achievement records for student1
INSERT INTO achievements (user_id, achievement_title, achievement_type, issuing_body, achievement_date, description, level, position_rank)
VALUES
(2, 'Best Project Award - Hackathon 2025',      'Award',       'UTAR Faculty of ICT',       '2025-12-16', 'Awarded best project for an IoT smart campus solution built during the Winter Hackathon.',          'Faculty',        '1st Place'),
(2, 'Dean''s List Certificate - Sem 1 2025/26', 'Certificate', 'UTAR Academic Office',      '2025-11-30', 'Achieved Dean''s List recognition for outstanding academic performance in Semester 1 2025/26.',   'University',     'Dean''s List'),
(2, 'Web Dev Workshop Completion Certificate',  'Certificate', 'UTAR ICT Department',       '2025-11-22', 'Completed a 3-day intensive workshop on modern web development practices and frameworks.',         'Faculty',        'Participant'),
(2, 'Annual Tech Conference Speaker Recognition','Recognition','UTAR Tech Society',          '2025-12-02', 'Recognised as a contributing speaker at the UTAR Annual Technology Conference.',                   'University',     'Speaker');

-- Sample achievements for Jack2
INSERT INTO achievements (user_id, achievement_title, achievement_type, issuing_body, achievement_date, description, level, position_rank)
VALUES
(3, 'National Debate Championship - Semi-Finalist', 'Competition', 'Malaysian Debate Council', '2026-02-11', 'Reached the semi-finals of the National Debate Championship representing UTAR.',  'National',  'Semi-Finalist'),
(3, 'IEEE Student Branch Excellence Award',          'Award',       'IEEE Malaysia Section',    '2026-03-05', 'Awarded for exceptional contributions to the UTAR IEEE Student Branch activities.', 'University','Best Secretary');