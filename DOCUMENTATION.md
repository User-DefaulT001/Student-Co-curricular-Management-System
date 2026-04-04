# Student Co-curricular Management System - Event Tracker Module

## 📋 Project Overview

A modern, creative web application for students to record and manage their co-curricular activities. The system uses a three-tier architecture with PHP and MySQL, featuring secure authentication, session management, and complete CRUD operations.

**Focus: Event Tracker Module** - Allows students to record formal organized programmes, competitions, workshops, talks, and other events.

---

## 🎨 Design Features

### UI/UX Highlights
- **Modern Gradient Design**: Eye-friendly color palette (purple/blue gradients) with smooth transitions
- **Responsive Layout**: Mobile-first approach with proper breakpoints for all devices
- **Card-based Interface**: Intuitive event cards with status badges and detailed information
- **Professional Typography**: Clean, readable fonts with proper contrast ratios
- **Subtle Animations**: Smooth hover effects and transitions without being distracting

### Color Scheme
- **Primary Gradient**: #667eea → #764ba2 (Purple with gradient)
- **Secondary Gradient**: #f093fb → #f5576c (Pink-Red)
- **Success Gradient**: #00d2fc → #3677ff (Cyan-Blue)
- **Backgrounds**: Light sky (#f8fafc) for comfortable viewing

---

## 🗄️ Database Schema

### Users Table
```sql
- user_id (Primary Key)
- username (Unique)
- email (Unique)
- password (Hashed)
- full_name
- role (student/admin)
- created_at, updated_at
```

### Events Table
```sql
- event_id (Primary Key)
- user_id (Foreign Key)
- event_name
- event_type (Competition, Workshop, Conference, etc.)
- event_date
- location
- description
- hours_participated
- role_held
- certificate_obtained (Boolean)
- status (completed/ongoing/upcoming)
- created_at, updated_at
```

---

## 🚀 Setup Instructions

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache Web Server (XAMPP recommended)
- Modern web browser

### Installation Steps

#### 1. Database Setup
```bash
# Option A: Using phpMyAdmin
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create new database: student_cms
3. Import the SQL file: database/setup.sql
4. Verify tables are created

# Option B: Using MySQL Command Line
mysql -u root -p < database/setup.sql
```

#### 2. Configuration
The system is pre-configured for local development:
- **Host**: localhost
- **Database**: student_cms
- **User**: root
- **Password**: (empty for XAMPP)

To modify, edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_cms');
```

#### 3. Access the Application
```
http://localhost/Student-Co-curricular-Management-System-main/
```

---

## 👤 Test Credentials

**Student Account:**
- Username: `student1`
- Password: `password`
- Full Name: John Doe

**Admin Account:**
- Username: `admin`
- Password: `password`
- Full Name: Administrator

---

## 📚 Core Modules

### 1. **Event Tracker Module** (**Currently Implemented**)
**Path**: `/modules/event/event_tracker.php`

**Features:**
- ✅ Add new events with comprehensive details
- ✅ View all events in beautiful card layout
- ✅ Edit existing events with pre-filled data modal
- ✅ Delete events with confirmation dialog
- ✅ Track event statistics (total events, completed events, total hours)
- ✅ Filter by status (completed, ongoing, upcoming)
- ✅ Certificate tracking
- ✅ Role and hour tracking

**Event Fields:**
- Event Name (required)
- Event Type (dropdown: Competition, Workshop, Conference, Seminar, Talk, Other)
- Date (required)
- Location
- Hours Participated
- Role/Position
- Status (Completed, Ongoing, Upcoming)
- Certificate Obtained (checkbox)
- Description (textarea)

---

## 🔐 Security Features

### Authentication
- Session-based user authentication
- Password hashing using PHP `password_hash()` (bcrypt)
- Logout functionality with session destruction
- Automatic redirect to login for unauthorized access

### Data Protection
- Prepared statements to prevent SQL injection
- Input validation and sanitization
- htmlspecialchars() for XSS prevention
- User-specific data isolation (users can only view/edit their own records)

### Error Handling
- User-friendly error messages
- Form validation on both client and server
- Graceful error recovery without exposing system details

---

## 📊 Statistics Dashboard

The Event Tracker displays:
1. **Total Events**: Count of all user events
2. **Completed Events**: Count of events with "completed" status
3. **Total Hours**: Sum of hours_participated for completed events
4. **Upcoming Events**: Count of non-completed events

---

## 🎯 CRUD Operations

### Create (Add Event)
- Modal form with all event details
- Client-side form validation
- Server-side input validation
- Returns to event list on success

### Read (View Events)
- List all events in descending date order
- Beautiful event cards with all key information
- Status-based color coding
- Certificate indicator with icon

### Update (Edit Event)
- Click "Edit" button on any event
- Pre-fills modal with current data
- Updates only user's own events
- Form validation before save

### Delete (Remove Event)
- Click "Delete" button to confirm
- Modal confirmation dialog prevents accidental deletion
- Soft delete support (can add date-based soft deletes)
- Only user's own events can be deleted

---

## 📁 Project Structure

```
Student-Co-curricular-Management-System-main/
├── index.php                 # Dashboard/Home page
├── login.php                 # Login interface
├── auth.php                  # Authentication handler
├── config.php                # Database configuration
├── assets/
│   └── style.css             # Modern custom CSS
├── database/
│   ├── setup.sql             # Database schema & sample data
│   └── putdatabasesplfilehere.txt
├── includes/
│   ├── header.php            # HTML head & structure
│   ├── footer.php            # Footer & scripts
│   └── sidebar.php           # Navigation sidebar
├── modules/
│   └── event/
│       └── event_tracker.php  # Event Tracker Module
├── vendor/
│   ├── bootstrap/            # Bootstrap 4.6
│   ├── fontawesome-free/     # Font Awesome icons
│   ├── jquery/              # jQuery library
│   ├── datatables/          # DataTables (for future tables)
│   └── chart.js/            # Chart.js (for future analytics)
└── README.md                 # This file
```

---

## 🛠️ Technical Implementation

### Backend Stack
- **Language**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Architecture**: 3-tier (Presentation, Business Logic, Data Access)

### Frontend Stack
- **Framework**: Bootstrap 4.6
- **CSS**: Custom modern CSS with gradients
- **Icons**: Font Awesome 5
- **JavaScript**: jQuery for interactive features

### Key Technologies
- Session management for authentication
- Prepared statements for secure queries
- Modal dialogs for forms and confirmations
- Responsive grid system
- Smooth animations and transitions

---

## 🔄 Workflow Example

1. **Login**: Navigate to login page with test credentials
2. **Dashboard**: View welcome message and system overview
3. **Event Tracker**: Click "Event Tracker" in sidebar
4. **Add Event**: Click "Add New Event" button
5. **Fill Form**: Complete event details in modal
6. **Save**: Submit form (validated and saved to database)
7. **View**: Event appears in the list with all details
8. **Edit**: Click edit button, modify details, save
9. **Delete**: Click delete button, confirm in dialog
10. **Logout**: Click logout button to end session

---

## 🧪 Testing Checklist

- [x] User registration and login functioning
- [x] Session management and logout
- [x] Add new events with all fields
- [x] View events in list format
- [x] Edit existing events
- [x] Delete events with confirmation
- [x] Statistics updating correctly
- [x] Responsive design on mobile
- [x] Form validation working
- [x] User data isolation (can't access other users' events)

---

## 📈 Scalability Features

### Ready for Future Expansion
- Database supports additional modules (clubs, merits, achievements)
- User roles (admin/student) for permission system
- Same authentication system for all modules
- Modular CSS and HTML structure
- API-ready endpoint architecture

---

## 🐛 Known Limitations

1. No image upload for events (can be added)
2. No bulk operations (can be added)
3. No event search/filtering UI (can be added)
4. No email notifications (can be added)
5. No calendar view (can be added with Chart.js)

---

## 📝 Future Enhancements

1. **Club Tracker Module** - Track club memberships and activities
2. **Merit Tracker Module** - Record volunteer hours and merit contributions
3. **Achievement Tracker Module** - Document awards and recognitions
4. **Admin Dashboard** - View all users and their statistics
5. **Advanced Analytics** - Charts and graphs of involvement
6. **Email Notifications** - Confirmations and reminders
7. **Calendar Integration** - View events on calendar
8. **Photo Gallery** - Upload event photos
9. **Export to PDF** - Generate transcripts
10. **Mobile App** - Native mobile application

---

## 🤝 Contributing

To extend or modify:
1. Follow existing code structure
2. Use prepared statements for database queries
3. Maintain responsive design principles
4. Add form validation for all inputs
5. Include appropriate error handling
6. Update documentation

---

## 📞 Support & Contact

For issues or questions:
1. Check the setup.sql for database issues
2. Verify PHP version is 7.4+
3. Ensure MySQL is running
4. Check config.php database credentials
5. Clear browser cache for CSS/JS changes

---

## 📄 License

This is an educational project for learning PHP, MySQL, and web development.

---

## ✨ Features Highlights

✅ **Modern UI** - Creative gradient design  
✅ **Fully Responsive** - Works on all devices  
✅ **Secure** - Password hashing and input validation  
✅ **User-Friendly** - Intuitive interface and workflows  
✅ **Fast** - Optimized queries and minimal load times  
✅ **Documented** - Clear code and comprehensive documentation  
✅ **Extensible** - Ready for additional modules  
✅ **Professional** - Production-ready code structure  

---

**Last Updated**: April 2026  
**Version**: 1.0.0  
**Status**: Complete & Tested
