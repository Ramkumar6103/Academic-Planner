-- Academic Planner Database Schema
CREATE DATABASE academic_planner;
USE academic_planner;

-- Users table for authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'faculty', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    class_id INT,
    roll_no VARCHAR(20) UNIQUE NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- Faculty table
CREATE TABLE faculty (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    subject VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    qualification VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subjects table
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    credits INT DEFAULT 3
);

-- Timetable table
CREATE TABLE timetable (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT,
    subject_id INT,
    faculty_id INT,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    room VARCHAR(50),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE
);

-- Exam seating table
CREATE TABLE exam_seating (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    exam_date DATE NOT NULL,
    room VARCHAR(50) NOT NULL,
    bench_number INT NOT NULL,
    seat_position ENUM('left', 'center', 'right') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Assignments table
CREATE TABLE assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    subject_id INT,
    faculty_id INT,
    file_path VARCHAR(255),
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE
);

-- Attendance table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    subject_id INT,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') DEFAULT 'absent',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Events table
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: password)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample faculty users (password: password)
INSERT INTO users (name, email, password, role) VALUES 
('Dr. John Smith', 'john.smith@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty'),
('Prof. Sarah Johnson', 'sarah.johnson@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty'),
('Dr. Michael Brown', 'michael.brown@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty');

-- Insert sample student users (password: password)
INSERT INTO users (name, email, password, role) VALUES 
('Alice Wilson', 'alice.wilson@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Bob Davis', 'bob.davis@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Carol Martinez', 'carol.martinez@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Insert sample classes
INSERT INTO classes (name, semester) VALUES 
('Computer Science A', 1),
('Computer Science B', 1),
('Information Technology', 1);

-- Insert sample subjects
INSERT INTO subjects (name, code, credits) VALUES 
('Mathematics', 'MATH101', 4),
('Physics', 'PHY101', 3),
('Programming', 'CS101', 4),
('Database Systems', 'CS201', 3),
('Web Development', 'CS301', 3);

-- Insert faculty records
INSERT INTO faculty (user_id, subject, phone, qualification) VALUES 
(2, 'Mathematics', '555-0101', 'PhD in Mathematics'),
(3, 'Programming', '555-0102', 'MS in Computer Science'),
(4, 'Database Systems', '555-0103', 'PhD in Computer Science');

-- Insert student records
INSERT INTO students (user_id, class_id, roll_no, phone, address) VALUES 
(5, 1, 'CS001', '555-0201', '123 Main St, City'),
(6, 1, 'CS002', '555-0202', '456 Oak Ave, City'),
(7, 2, 'CS003', '555-0203', '789 Pine Rd, City');

-- Insert sample events
INSERT INTO events (title, description, event_date) VALUES 
('Orientation Day', 'Welcome ceremony for new students', '2024-01-15'),
('Mid-term Exams', 'Mid-semester examinations begin', '2024-03-01'),
('Tech Fest 2024', 'Annual technical festival with competitions and workshops', '2024-04-15'),
('Sports Day', 'Inter-class sports competitions', '2024-05-10');

-- Insert sample assignments
INSERT INTO assignments (title, description, subject_id, faculty_id, due_date) VALUES 
('Calculus Problem Set 1', 'Solve problems from Chapter 1-3', 1, 1, '2024-02-15'),
('Programming Assignment 1', 'Create a simple calculator program', 3, 2, '2024-02-20'),
('Database Design Project', 'Design a database for library management system', 4, 3, '2024-03-01');