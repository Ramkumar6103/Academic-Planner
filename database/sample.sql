-- Academic Planner & Timetable Generator Schema
create database academic_planner;
use academic_planner;
-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE -- e.g., Admin, Faculty, Student
);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Exam halls table
CREATE TABLE IF NOT EXISTS exam_halls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    capacity INT NOT NULL
);

-- Faculty table
CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Faculty availability table
CREATE TABLE IF NOT EXISTS faculty_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    day_of_week VARCHAR(20) NOT NULL, -- e.g., Monday
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id)
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department_id INT NOT NULL,
    register_number VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Timetable table
CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    course_id INT,
    faculty_id INT,
    subject_id INT,
    day_of_week VARCHAR(20) NOT NULL, -- e.g., Monday
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    type ENUM('class', 'exam', 'extra') NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (faculty_id) REFERENCES faculty(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- Exams table
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    exam_hall_id INT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (exam_hall_id) REFERENCES exam_halls(id)
);

-- Seating arrangements table
CREATE TABLE IF NOT EXISTS seating_arrangements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    exam_hall_id INT NOT NULL,
    seat_number VARCHAR(20) NOT NULL,
    FOREIGN KEY (exam_id) REFERENCES exams(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (exam_hall_id) REFERENCES exam_halls(id)
); 
-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('exam', 'assignment', 'general') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Assignments table
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    faculty_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (faculty_id) REFERENCES faculty(id)
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);
INSERT INTO roles (name) VALUES 
('Admin'),
('Faculty'), 
('Student');

INSERT INTO departments (name) VALUES 
('Computer Science'),
('Electrical Engineering'),
('Mechanical Engineering'),
('Civil Engineering'),
('Information Technology');

INSERT INTO courses (department_id, name, code) VALUES 
(1, 'Bachelor of Technology in Computer Science', 'B.Tech CS'),
(1, 'Master of Technology in Computer Science', 'M.Tech CS'),
(2, 'Bachelor of Technology in Electrical Engineering', 'B.Tech EE'),
(3, 'Bachelor of Technology in Mechanical Engineering', 'B.Tech ME'),
(4, 'Bachelor of Technology in Civil Engineering', 'B.Tech CE'),
(5, 'Bachelor of Technology in Information Technology', 'B.Tech IT');

INSERT INTO subjects (course_id, name, code) VALUES 
(1, 'Programming Fundamentals', 'CS101'),
(1, 'Data Structures', 'CS102'),
(1, 'Database Management Systems', 'CS103'),
(1, 'Computer Networks', 'CS104'),
(1, 'Software Engineering', 'CS105'),
(2, 'Advanced Algorithms', 'CS201'),
(2, 'Machine Learning', 'CS202'),
(3, 'Circuit Theory', 'EE101'),
(3, 'Digital Electronics', 'EE102'),
(4, 'Engineering Mechanics', 'ME101'),
(4, 'Thermodynamics', 'ME102'),
(5, 'Structural Analysis', 'CE101'),
(5, 'Concrete Technology', 'CE102'),
(6, 'Web Development', 'IT101'),
(6, 'Mobile App Development', 'IT102');

INSERT INTO exam_halls (name, capacity) VALUES 
('Main Auditorium', 200),
('Hall A', 100),
('Hall B', 80),
('Hall C', 60),
('Computer Lab 1', 40),
('Computer Lab 2', 40);

-- Admin User (password: admin123)
INSERT INTO users (username, password, email, role_id) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@academic.com', 1);

-- Faculty Users (password: faculty123)
INSERT INTO users (username, password, email, role_id) VALUES 
('dr_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'smith@academic.com', 2),
('prof_jones', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jones@academic.com', 2),
('dr_brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'brown@academic.com', 2);

-- Student Users (password: student123)
INSERT INTO users (username, password, email, role_id) VALUES 
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@student.com', 3),
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jane@student.com', 3),
('mike_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mike@student.com', 3);

INSERT INTO faculty (user_id, department_id, name) VALUES 
(2, 1, 'Dr. John Smith'),
(3, 1, 'Prof. Sarah Jones'),
(4, 2, 'Dr. Robert Brown');

INSERT INTO students (user_id, department_id, register_number, name) VALUES 
(5, 1, 'STU20240001', 'John Doe'),
(6, 1, 'STU20240002', 'Jane Smith'),
(7, 2, 'STU20240003', 'Mike Wilson');

INSERT INTO faculty_availability (faculty_id, day_of_week, start_time, end_time) VALUES 
(1, 'Monday', '09:00:00', '11:00:00'),
(1, 'Tuesday', '14:00:00', '16:00:00'),
(1, 'Wednesday', '10:00:00', '12:00:00'),
(2, 'Monday', '13:00:00', '15:00:00'),
(2, 'Thursday', '09:00:00', '11:00:00'),
(3, 'Tuesday', '09:00:00', '11:00:00'),
(3, 'Friday', '14:00:00', '16:00:00');

INSERT INTO timetable (department_id, course_id, faculty_id, subject_id, day_of_week, start_time, end_time, type) VALUES 
(1, 1, 1, 1, 'Monday', '09:00:00', '10:00:00', 'class'),
(1, 1, 1, 2, 'Tuesday', '14:00:00', '15:00:00', 'class'),
(1, 1, 2, 3, 'Wednesday', '10:00:00', '11:00:00', 'class'),
(1, 1, 2, 4, 'Thursday', '13:00:00', '14:00:00', 'class'),
(1, 1, 1, 5, 'Friday', '09:00:00', '10:00:00', 'class'),
(2, 3, 3, 8, 'Monday', '09:00:00', '10:00:00', 'class'),
(2, 3, 3, 9, 'Tuesday', '14:00:00', '15:00:00', 'class');

INSERT INTO exams (subject_id, date, start_time, end_time, exam_hall_id) VALUES 
(1, '2024-12-20', '09:00:00', '11:00:00', 1),
(2, '2024-12-22', '14:00:00', '16:00:00', 2),
(3, '2024-12-25', '09:00:00', '11:00:00', 3);

INSERT INTO notifications (user_id, message, type) VALUES 
(5, 'Your Programming Fundamentals exam is scheduled for December 20th', 'exam'),
(6, 'New assignment uploaded for Data Structures', 'assignment'),
(7, 'Attendance marked for today\'s class', 'general');