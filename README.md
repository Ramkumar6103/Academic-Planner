# Academic Planner - College Department Management System

A comprehensive web-based academic management system built with PHP, MySQL, HTML, CSS, and JavaScript. This system provides role-based access for Admins, Faculty, and Students with features for managing academic activities, timetables, assignments, and more.

## 🚀 Features

### Admin Panel
- **User Management**: Add, edit, and delete faculty and students
- **Timetable Generation**: Create class timetables based on faculty availability
- **Exam Seating**: Generate random seating arrangements ensuring no adjacent same-class students
- **Event Management**: Create and manage academic events and announcements
- **Attendance Tracking**: Monitor and manage student attendance

### Faculty Panel
- **Personal Timetable**: View assigned teaching schedule
- **Assignment Management**: Upload and manage assignments by subject
- **Notice Board**: Post notices and updates for students

### Student Panel
- **Assignment Access**: View and download assignments by subject
- **Academic Calendar**: View events and important dates with image gallery
- **Class Timetable**: Access personal class schedule
- **Exam Information**: View exam timetable and seating arrangements
- **Attendance Tracking**: Monitor personal attendance records

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## 🛠️ Installation

1. **Clone or download** the project files to your web server directory

2. **Database Setup**:
   - Create a new MySQL database named `academic_planner`
   - Import the `database.sql` file to create tables and sample data
   - Update database credentials in `includes/db.php` if needed

3. **File Permissions**:
   - Ensure write permissions for `assets/images/events/` and `assets/assignments/` directories
   - Create these directories if they don't exist

4. **Configuration**:
   - Update database connection settings in `includes/db.php`
   - Modify any paths or settings as needed for your environment

## 🔐 Default Login Credentials

- **Admin**: admin@college.edu / password
- **Faculty**: Create via admin panel (default password: password)
- **Student**: Create via admin panel (default password: password)

## 📁 Project Structure

```
academic-planner/
├── index.php                 # Login page
├── database.sql             # Database schema and sample data
├── README.md               # This file
├── includes/
│   ├── db.php              # Database connection
│   └── auth.php            # Authentication functions
├── assets/
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   ├── js/
│   │   └── script.js       # JavaScript functions
│   ├── images/
│   │   └── events/         # Event images directory
│   └── assignments/        # Assignment files directory
├── admin/
│   ├── dashboard.php       # Admin dashboard
│   ├── manage_students.php # Student management
│   ├── manage_faculty.php  # Faculty management
│   ├── generate_timetable.php # Timetable generation
│   ├── generate_exam_seating.php # Exam seating
│   ├── manage_events.php   # Event management
│   └── manage_attendance.php # Attendance tracking
├── faculty/
│   ├── dashboard.php       # Faculty dashboard
│   ├── view_timetable.php  # Personal timetable
│   ├── upload_assignment.php # Assignment management
│   └── post_notice.php     # Notice posting
└── student/
    ├── dashboard.php       # Student dashboard
    ├── view_assignments.php # Assignment viewing
    ├── view_timetable.php  # Class timetable
    ├── view_exam_schedule.php # Exam schedule
    └── view_calendar.php   # Academic calendar
```

## 🎯 Key Features Explained

### Exam Seating Algorithm
- Randomly assigns students to benches (3 per bench)
- Ensures students from the same class are not seated adjacently
- Supports multiple exam rooms
- Generates printable seating charts

### Timetable Management
- Flexible time slot configuration
- Faculty-subject assignment
- Room allocation
- Conflict detection and resolution

### Role-Based Access Control
- Secure session management
- Role-specific dashboards and navigation
- Protected routes and functions

### Responsive Design
- Mobile-friendly interface
- Print-optimized layouts
- Modern CSS with flexbox/grid
- Cross-browser compatibility

## 🔧 Customization

### Adding New Subjects
1. Access admin panel
2. Add subjects via database or create management interface
3. Assign to faculty members

### Modifying Time Slots
Update the `$time_slots` array in timetable generation files:
```php
$time_slots = ['9:00-10:00', '10:00-11:00', '11:30-12:30', '12:30-1:30', '2:30-3:30', '3:30-4:30'];
```

### Styling Changes
- Main styles: `assets/css/style.css`
- Color scheme: Update CSS custom properties
- Layout: Modify grid/flexbox properties

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check credentials in `includes/db.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **File Upload Issues**:
   - Check directory permissions
   - Verify PHP upload settings
   - Ensure directories exist

3. **Session Issues**:
   - Check PHP session configuration
   - Verify write permissions for session directory

### Error Logging
Enable PHP error reporting for development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🔒 Security Considerations

- All user inputs are sanitized
- Password hashing using PHP's password_hash()
- SQL injection prevention with prepared statements
- Session-based authentication
- Role-based access control

## 📱 Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the MIT License.

## 📞 Support

For support or questions:
- Check the troubleshooting section
- Review the code comments
- Create an issue for bugs or feature requests

---

**Academic Planner** - Streamlining college department management with modern web technology.