# GuideSched — Guidance Counseling Appointment & Scheduling System

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/Database-MySQL_/_MariaDB-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Chart.js](https://img.shields.io/badge/Analytics-Chart.js-FF6384?style=flat-square&logo=chartdotjs&logoColor=white)](https://chartjs.org)
[![License](https://img.shields.io/badge/License-Educational-6D28D9?style=flat-square)](#license)

**GuideSched** is a modern, responsive web application designed for university guidance counseling offices (QSU Diffun Campus). It simplifies appointment booking for students, streamlines schedule management for guidance counselors, and provides analytics dashboards for guidance administrators.

---

## 🎨 Modern Violet Design System

The system features a custom, modern violet design system built with custom CSS, Google Fonts (`Sora` for headings, `Inter` for body), Chart.js graphs, SVG iconography, and responsive cards:

- **Primary Violet Palette**: `--violet-950: #2B1153`, `--violet-700: #5B21B6`, `--violet-600: #6D28D9`, `--violet-500: #7C3AED`, `--violet-100: #EDE6FB`, `--violet-50: #F6F3FD`
- **Status Pills & Alerts**: Green (`#1C9A5B`), Amber (`#B7791F`), Red (`#C0392B`)
- **Typography**: Google Fonts (`Sora` & `Inter`)
- **Interactive Components**: Visual time-slot grids, tabbed navigation, real-time unread notification dots, and Chart.js analytical charts.

---

## 🎯 Key Features

### 🎓 Student Portal (`/student/`)
- **Dashboard**: Greeting topbar, quick booking banner ("Ready to talk to someone?"), upcoming appointment summary, daily motivation card with consistency streak, and recent notifications.
- **Appointment Booking**: Interactive time-slot grid (`.slot-grid`) that fetches availability dynamically. Select counselor, mode (*Face-to-face* or *Online*), and concern topic (*Academic stress, Anxiety, Family concerns, Peer relationships, Career guidance*).
- **Notifications Feed**: Real-time updates on appointment status (Approved, Rescheduled, Cancelled, Reminders) with "Mark all read" capabilities.
- **My Insights (Analytics)**: Visual metrics for total sessions attended, top concern topic, consistency streak, and a Chart.js monthly session bar graph.
- **Student Profile**: Update personal and academic details, view overall appointment summary, and change password securely.

### 🏛️ Admin & Counselor Portal (`/admin/`)
- **Admin Dashboard**: Real-time stats (Today's appointments, Pending approvals, Weekly sessions, No-show rate), today's schedule agenda, and quick 1-click Approve / Decline action buttons.
- **Appointment Management**: Tabbed view (*Pending*, *Approved*, *Time Slots*) with status indicators and quick response actions.
- **Schedule Management**: Create custom availability slots, manage counselor working hours, and view weekly grid overview (`.week-grid`).
- **Analytics & Data Insights**: Key metrics and 3 Chart.js graphs:
  1. **Appointment Trends**: Monthly bar chart
  2. **Common Concerns**: Topic distribution doughnut chart
  3. **Status Breakdown**: Horizontal stacked status bar chart
- **Counselor Profile**: Counselor specialization, office location, room number, working hours, and password updates.
- **Student & Counselor Management**: Register new counselors, toggle counselor active/inactive status, and search student profiles & appointment history.
- **Reports & Export**: Filter appointments by status, counselor, and date range with **1-click CSV export** and print-ready view.

---

## 🔒 Security Features

- **Password Hashing**: Uses PHP `password_hash()` with `PASSWORD_BCRYPT`.
- **SQL Injection Defense**: Prepared statements (`$stmt->prepare()`) for database operations.
- **CSRF Protection**: Token validation on sensitive forms.
- **Rate Limiting**: Protection against brute-force login attempts (5 attempts per 5 minutes).
- **Graceful DB Error Handling**: Custom database alert card when MySQL service is disconnected.

---

## 🔑 Default Login Credentials

After importing the database, you can log in with these default accounts:

| Role | Email | Password | Access Portal |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@guidesched.com` | `admin123` | Admin Portal (`/admin/dashboard.php`) |
| **Counselor** | `maria.santos@guidesched.com` | `counselor123` | Counselor Portal (`/admin/dashboard.php`) |
| **Student** | *(Register via Sign Up)* | *(Set during registration)* | Student Portal (`/student/dashboard.php`) |

---

## 🚀 Installation & Local Setup

### Prerequisites
- **XAMPP** (or WAMP/LAMP stack)
- **PHP 8.0+**
- **MySQL 5.7+ / MariaDB**

### Setup Steps

1. **Clone or Copy Project**:
   ```bash
   git clone https://github.com/Jaypee1031/guidesched.git "C:\xampp\htdocs\APPOINTMENT IN GUIDANCE"
   ```

2. **Start XAMPP Control Panel**:
   - Start **Apache** and **MySQL**.

3. **Import Database**:
   - Open phpMyAdmin at [`http://localhost/phpmyadmin`](http://localhost/phpmyadmin).
   - Create a database named `guidesched`.
   - Click **Import** and select `database/guidesched_full.sql` from the project folder, then click **Go**.
   - *(Or simply visit [`http://localhost/APPOINTMENT%20IN%20GUIDANCE/setup_database.php`](http://localhost/APPOINTMENT%20IN%20GUIDANCE/setup_database.php) in your browser)*.

4. **Launch Application**:
   - **Landing Page**: [`http://localhost/APPOINTMENT%20IN%20GUIDANCE/`](http://localhost/APPOINTMENT%20IN%20GUIDANCE/)
   - **Login Page**: [`http://localhost/APPOINTMENT%20IN%20GUIDANCE/login.php`](http://localhost/APPOINTMENT%20IN%20GUIDANCE/login.php)
   - **Student Sign Up**: [`http://localhost/APPOINTMENT%20IN%20GUIDANCE/register.php`](http://localhost/APPOINTMENT%20IN%20GUIDANCE/register.php)

---

## 📁 Directory Structure

```
APPOINTMENT IN GUIDANCE/
├── admin/                      # Admin & Counselor Portal
│   ├── dashboard.php          # Stats, today's schedule, pending approvals
│   ├── appointments.php       # Tabbed appointment management
│   ├── notifications.php      # Admin notifications feed
│   ├── analytics.php          # 3 Chart.js graphs & metrics
│   ├── profile.php            # Counselor profile & office info
│   ├── schedule.php           # Availability slot creator & weekly grid
│   ├── students.php           # Student directory & profile viewer
│   ├── counselors.php         # Counselor management
│   ├── add-counselor.php      # Register new counselor
│   └── reports.php            # Filterable reports with CSV export
├── student/                   # Student Portal
│   ├── dashboard.php          # Student home dashboard
│   ├── appointments.php       # Interactive visual slot grid booking
│   ├── notifications.php      # Student notification feed
│   ├── analytics.php          # My Insights analytics with Chart.js
│   ├── profile.php            # Student profile & history
│   └── get-availability.php   # AJAX availability slot resolver
├── assets/                    # Static Assets
│   └── css/style.css          # Main custom CSS design system
├── config/                    # Configuration Files
│   ├── config.php             # Site settings & session management
│   └── database.php           # MySQL connection & graceful error card
├── includes/                  # Core PHP Functions & Shared Components
│   ├── head.php               # HTML <head>, Google Fonts, Chart.js
│   ├── icons.php              # Inline SVG symbol icons registry
│   ├── student_sidebar.php    # Student navigation sidebar
│   ├── admin_sidebar.php      # Admin navigation sidebar
│   ├── auth_functions.php     # Authentication & role authorization
│   ├── appointment_functions.php # Booking logic & analytics data
│   └── admin_functions.php    # Admin analytics calculations
├── database/                  # Database Export Files
│   ├── guidesched_full.sql    # Complete MySQL database export
│   └── schema.sql             # Schema definitions
├── index.php                  # Landing page
├── login.php                  # Login page
├── register.php               # Student registration
├── forgot-password.php        # Password recovery form
├── logout.php                 # Logout script
└── README.md                  # System documentation
```

---

## 📄 License

Developed for educational and institutional guidance appointment management purposes at **QSU Diffun Campus**.
