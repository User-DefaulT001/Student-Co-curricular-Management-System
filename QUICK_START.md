# Quick Start Guide - Event Tracker System

## 🚀 Get Started in 5 Minutes

### Step 1: Database Setup (2 minutes)

1. **Open phpMyAdmin**
   - Go to: http://localhost/phpmyadmin
   - Login with default XAMPP credentials (no password usually)

2. **Create Database**
   - Click "New" in left sidebar
   - Database name: `student_cms`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import SQL File**
   - Select `student_cms` database (click on it)
   - Go to "Import" tab
   - Choose file: `database/setup.sql`
   - Click "Go"
   - **Done!** Tables and sample data created

---

### Step 2: Start Using the App (1 minute)

1. **Open Application**
   - URL: `http://localhost/Student-Co-curricular-Management-System-main/`

2. **Login with Demo Account**
   - Username: `student1`
   - Password: `password`
   - Click "Login"

3. **You're In!**
   - Dashboard appears with welcome message
   - Click "Event Tracker" in sidebar to manage events

---

### Step 3: Try the Features (2 minutes)

#### Add an Event
1. Click "Add New Event" button
2. Fill in the form:
   - Event Name: "My Amazing Workshop"
   - Type: "Workshop"
   - Date: Pick any date
   - Location: "Building A"
   - Hours: "8"
   - Role: "Participant"
   - Check "Certificate Obtained"
   - Click "Save Event"

#### View Your Event
- Event appears in the list below
- See all details beautifully displayed

#### Edit the Event
- Click "Edit" button on your event
- Form pre-fills with current data
- Make changes and save

#### Delete the Event  
- Click "Delete" button
- Confirm in the dialog
- Event removed from list

---

## 📊 Key Features at a Glance

| Feature | Description |
|---------|-------------|
| **Add Events** | Create new event records with full details |
| **View Events** | Beautiful card layout showing all events |
| **Edit Events** | Modify any of your event records |
| **Delete Events** | Remove unwanted event records |
| **Statistics** | See total events, hours, and completion status |
| **Status Tracking** | Mark events as completed, ongoing, or upcoming |
| **Certificate Tracking** | Record which events gave you certificates |

---

## 🎨 Modern Design Features

- **Gradient Backgrounds** - Beautiful purple/blue gradients
- **Card-Based UI** - Clean, organized information layout
- **Status Badges** - Color-coded event status indicators
- **Responsive Design** - Works perfectly on phones, tablets, and desktops
- **Smooth Animations** - Professional transitions and hover effects
- **Easy Navigation** - Intuitive sidebar with clear labels

---

## 🔐 Security

Your data is protected with:
- ✅ Secure login system
- ✅ Password encryption
- ✅ Session management
- ✅ Input validation
- ✅ SQL injection protection
- ✅ XSS protection

---

## 📱 Responsive Design

The system works great on:
- ✅ Desktop computers
- ✅ Tablets
- ✅ Mobile phones
- ✅ All modern browsers

---

## 🆘 Troubleshooting

### Can't Login?
- Check database was imported (see Step 1)
- Verify credentials: username=`student1`, password=`password`
- Clear browser cache (Ctrl+Shift+Delete)

### Page Shows Errors?
- Ensure MySQL is running in XAMPP
- Check database credentials in `config.php`
- Verify `database/setup.sql` was fully imported

### Styles Look Wrong?
- Clear browser cache
- Try different browser
- Check `assets/style.css` file exists

### Can't Add Events?
- Make sure you're logged in
- Check all required fields are filled (name, date)
- Look for error message in form

---

## 💡 Tips & Tricks

1. **Statistics Update Automatically** - As you add/delete events, stats change
2. **Color-Coded Status** - Green=Completed, Yellow=Ongoing, Blue=Upcoming
3. **Modal Forms** - All forms appear in popup modals for convenience
4. **Date Sorting** - Events automatically sort by date (newest first)
5. **Fast Edits** - Click edit to pre-fill form with current data

---

## 📚 Next Steps

After trying the demo:

1. **Create Your Own Events** - Add your real co-curricular activities
2. **Track Hours** - Record your total involvement hours
3. **Organize Activities** - Use status to track completion
4. **Save Records** - Keep certificates and achievements

---

## 🎓 Educational Value

This system demonstrates:
- Modern PHP development
- MySQL database design
- Secure user authentication
- Responsive web design
- Professional UI/UX principles
- Form handling and validation
- CRUD operations
- Session management

---

## 📞 Need Help?

1. Check `DOCUMENTATION.md` for detailed information
2. Review code comments in PHP files
3. Check browser console (F12 → Console) for JavaScript errors
4. Verify all files exist in the directory structure

---

## ✨ Enjoy!

You're all set! Start managing your co-curricular activities with this modern, professional system.

**Happy tracking!** 🎯

---

*Version 1.0.0 | Last Updated: April 2026*
