-- Academic Planner & Timetable Generator Schema

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