# Academic Planner & Timetable Generator

A full-stack web-based system for academic planning, timetable generation, and exam management for Admin, Faculty, and Students.

## Features
- Modern landing page with academic theme
- User authentication (Admin, Faculty, Student)
- Admin dashboard: manage departments, courses, subjects, exam halls, faculty, timetables, exams, seating
- Faculty dashboard: view timetable, upload assignments, mark attendance, invigilation
- Student dashboard: view timetable, exams, notifications, download seating plan
- Conflict-free timetable and seating arrangement generation
- Calendar integration with FullCalendar.js (color-coded events)
- PDF export for seating plans (jsPDF)
- Responsive UI with Bootstrap and FontAwesome icons

## Tech Stack
- **Frontend:** HTML, CSS, JavaScript, Bootstrap, FontAwesome, FullCalendar.js, jsPDF
- **Backend:** PHP (PDO)
- **Database:** MySQL

## Setup Instructions

### 1. Database
- Import `database/schema.sql` into your MySQL server.
- Create a database named `academic_planner` (or update config in `backend/config/db.php`).

### 2. Backend
- Configure database credentials in `backend/config/db.php`.
- Place backend PHP files in a web-accessible directory (e.g., XAMPP's `htdocs`).

### 3. Frontend
- Open `frontend/index.html` in your browser.
- Ensure paths to backend endpoints are correct in AJAX/JS files.

### 4. Dependencies
- Bootstrap, FontAwesome, FullCalendar.js, and jsPDF are included via CDN in HTML files.

## Usage Notes
- Default roles: Admin, Faculty, Student (add roles in the `roles` table if not present).
- Use the admin dashboard to set up departments, courses, subjects, and faculty before generating timetables.
- For PDF export and calendar, ensure your browser allows popups/downloads.

## License
MIT 