# GuideSched Mobile & Web Application (Flutter)

A modern, responsive cross-platform appointment and guidance scheduling application built with **Flutter** and featuring an **Emerald & Forest Green Design System**.

Designed for **Cagasat High School Guidance Counseling Office**, supporting a 2-user role architecture:
1. **Students**: Book appointments with interactive time-slots, view upcoming sessions, track mental wellness consistency streaks, and review personal insights.
2. **Guidance Counselors**: Review pending requests, approve/decline sessions with custom notes, manage availability time slots, view student rosters, and analyze multi-year trend reports.

---

## 🌿 Emerald & Forest Green Design System

The application replaces the traditional violet palette with a refreshing, calming **Green Design System**:

- **Primary Colors**:
  - Emerald Green (`#059669` / `#10B981`)
  - Deep Forest Green (`#064E3B`)
- **Containers & Surfaces**:
  - Soft Mint Containers (`#ECFDF5`, `#D1FAE5`, `#F0FDF4`)
  - Crisp White Cards with subtle green borders (`#A7F3D0`)
- **Status Pills & Indicators**:
  - Approved / Completed: Emerald (`#059669`)
  - Pending: Amber (`#D97706`)
  - Declined / Cancelled: Crimson (`#DC2626`)
  - Rescheduled: Royal Blue (`#2563EB`)
- **Typography**: Google Fonts `Inter` & `Sora`.

---

## 📱 Features

### 🎓 Student Experience
- **Dashboard**: Greeting banner, 3-month consistency streak card, next session countdown, quick action "Schedule an Appointment", and recent activity.
- **Appointment Booking**:
  - Counselor selector cards
  - Calendar date picker (with 60-day window)
  - Interactive morning & afternoon time-slot chips with real-time status (green = available, gray = booked)
  - Consultation mode toggle (*Face-to-face* or *Online via Google Meet*)
  - Concern categories (*Academic stress, Anxiety, Family concerns, Peer relationships, Career & Strand guidance, Personal growth*)
  - Details note input & confirmation bottom sheet
- **My Sessions**: Filter by Upcoming, Past, or All; search by topic or counselor; one-tap cancellation dialog with reason note.
- **My Insights**: Visual metric cards (completed sessions, consistency streak, top concern topic), monthly session volume bar charts, and concern distribution progress bars.
- **Notifications Feed**: Real-time updates on approvals, rescheduling, and reminders with "Mark all read".
- **Student Profile**: Academic details (Grade, Strand, LRN, Contact), change password, and sign out.

### 🏛️ Counselor Experience
- **Dashboard**: 4 interactive stat cards (Today's count, Pending approvals, Approved count, Total bookings), quick 1-click Approve / Decline dialogs with custom counselor notes.
- **Appointments Management**: Segmented tabs for Pending, Approved, and All sessions with instant search and status updates.
- **Schedule Management**: Date selector, custom availability time slot creator, and slot status indicator.
- **Student Directory**: Searchable student roster with LRN, Grade/Strand, and detailed session history bottom sheet.
- **Analytics & Reports**: Monthly trend bar charts, concern distribution metrics, and no-show rate tracking.
- **Counselor Profile & API Config**: Specialization details, office hours (Room 204), and dynamic backend API configuration.

---

## ⚡ Dual Backend & Offline Demo Mode

The app comes with an **automatic dual-mode data layer**:
- **Live PHP REST API**: Connects to the accompanying endpoints located in `../api/` (`auth.php`, `appointments.php`, `availability.php`, `notifications.php`, `analytics.php`, `counselors.php`).
- **Offline / Standalone Fallback**: If the device cannot reach XAMPP (or if local MySQL is offline), the app seamlessly switches to `MockDataService` with realistic pre-populated data. You can test all features immediately without any setup!

---

## 🔑 Default Login Credentials

Use these pre-populated credentials (or tap the **"Quick Demo Accounts"** button on the login screen):

| User Role | Email | Password |
| :--- | :--- | :--- |
| **Guidance Counselor** | `maria.santos@guidesched.com` | `counselor123` |
| **Student** | `juan.santos@cagasaths.edu.ph` | `student123` |

---

## 🚀 How to Run the App

### Prerequisites
- [Flutter SDK](https://docs.flutter.dev/get-started/install) installed (version 3.0.0+)
- Google Chrome (for web), Android Studio / Emulator, or VS Code with Flutter extension

### Steps

1. **Navigate to the Flutter project folder**:
   ```bash
   cd "c:\xampp\htdocs\APPOINTMENT IN GUIDANCE APP\guidesched_app"
   ```

2. **Install dependencies**:
   ```bash
   flutter pub get
   ```

3. **Run on Chrome (Web)**:
   ```bash
   flutter run -d chrome
   ```

4. **Run on Android Emulator**:
   ```bash
   flutter run
   ```

5. **Run on Windows Desktop**:
   ```bash
   flutter run -d windows
   ```

---

## 📁 Project Directory Structure

```
guidesched_app/
├── lib/
│   ├── main.dart                       # Entry point & role-based routing
│   ├── theme/
│   │   └── app_theme.dart              # Emerald & Forest Green design system
│   ├── models/
│   │   ├── user_model.dart             # Student & counselor models
│   │   ├── appointment_model.dart      # Appointment schema & status helpers
│   │   ├── availability_slot_model.dart # Time slot chips model
│   │   ├── notification_model.dart     # Notifications model
│   │   └── analytics_model.dart        # Analytics & trend models
│   ├── services/
│   │   ├── api_service.dart            # REST API client with timeout & CORS
│   │   ├── mock_data_service.dart      # Realistic in-memory database
│   │   ├── auth_service.dart           # Authentication service
│   │   ├── appointment_service.dart    # Appointments & booking service
│   │   ├── notification_service.dart   # Notification service
│   │   └── analytics_service.dart      # Statistical calculation service
│   ├── providers/
│   │   ├── auth_provider.dart          # Session state & current user
│   │   ├── appointment_provider.dart   # Appointments list & slot management
│   │   ├── notification_provider.dart  # Notification tracking & unread badge
│   │   └── theme_provider.dart         # Light & Dark theme toggle
│   ├── widgets/
│   │   ├── appointment_card.dart       # Reusable session card
│   │   ├── status_badge.dart           # Green / Amber / Red status pill
│   │   ├── time_slot_chip.dart         # Interactive time slot selector
│   │   ├── stat_summary_card.dart      # Metric card
│   │   ├── green_banner.dart           # Gradient emerald header banner
│   │   └── empty_state_view.dart       # Empty state illustration & message
│   └── screens/
│       ├── auth/                       # Login, Register, Forgot Password
│       ├── student/                    # Dashboard, Booking, Sessions, Insights, Profile
│       └── counselor/                  # Dashboard, Approvals, Schedule, Students, Reports
├── pubspec.yaml
└── README.md
```
