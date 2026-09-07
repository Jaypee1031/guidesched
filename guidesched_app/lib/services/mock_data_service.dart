import 'package:intl/intl.dart';
import '../models/user_model.dart';
import '../models/appointment_model.dart';
import '../models/availability_slot_model.dart';
import '../models/notification_model.dart';
import '../models/analytics_model.dart';

class MockDataService {
  static final MockDataService _instance = MockDataService._internal();
  factory MockDataService() => _instance;
  MockDataService._internal() {
    _initMockData();
  }

  late List<UserModel> _users;
  late List<AppointmentModel> _appointments;
  late List<NotificationModel> _notifications;
  late List<AvailabilitySlotModel> _defaultSlots;

  void _initMockData() {
    // 1. Users
    _users = [
      UserModel(
        id: 2,
        userId: 'COUNSELOR001',
        role: 'counselor',
        name: 'Dr. Maria Santos',
        email: 'maria.santos@guidesched.com',
        status: 'active',
        specialization: 'Academic & Career Counseling',
        contactNumber: '+63 912 345 6789',
      ),
      UserModel(
        id: 3,
        userId: 'STU758065',
        role: 'student',
        name: 'Juan Santos',
        email: 'juan.santos@cagasaths.edu.ph',
        status: 'active',
        studentNumber: '2024-00129',
        course: 'Grade 11 - STEM Strand',
        yearLevel: 11,
        contactNumber: '+63 963 403 2919',
      ),
      UserModel(
        id: 5,
        userId: 'STU797983',
        role: 'student',
        name: 'Aira Delos Santos',
        email: 'aira@gmail.com',
        status: 'active',
        studentNumber: '2024-00542',
        course: 'Grade 12 - HUMSS Strand',
        yearLevel: 12,
        contactNumber: '+63 935 264 0530',
      ),
    ];

    // 2. Appointments
    final today = DateFormat('yyyy-MM-dd').format(DateTime.now());
    final tomorrow = DateFormat('yyyy-MM-dd').format(DateTime.now().add(const Duration(days: 1)));
    final yesterday = DateFormat('yyyy-MM-dd').format(DateTime.now().subtract(const Duration(days: 1)));

    _appointments = [
      AppointmentModel(
        id: 101,
        studentId: 3,
        counselorId: 2,
        appointmentDate: tomorrow,
        startTime: '10:00:00',
        endTime: '11:00:00',
        concern: '[Face-to-face] Academic stress: Need guidance regarding midterm exam preparation and heavy subject load',
        status: 'pending',
        counselorName: 'Dr. Maria Santos',
        counselorEmail: 'maria.santos@guidesched.com',
        counselorSpecialization: 'Academic & Career Counseling',
        studentName: 'Juan Santos',
        studentEmail: 'juan.santos@cagasaths.edu.ph',
        studentNumber: '2024-00129',
        course: 'Grade 11 - STEM Strand',
        yearLevel: 11,
      ),
      AppointmentModel(
        id: 102,
        studentId: 5,
        counselorId: 2,
        appointmentDate: today,
        startTime: '13:00:00',
        endTime: '14:00:00',
        concern: '[Online] Career & Strand guidance: Seeking advice for college admissions and scholarship requirements',
        status: 'approved',
        adminNotes: 'Online meeting link has been prepared and sent via email.',
        counselorName: 'Dr. Maria Santos',
        counselorEmail: 'maria.santos@guidesched.com',
        counselorSpecialization: 'Academic & Career Counseling',
        studentName: 'Aira Delos Santos',
        studentEmail: 'aira@gmail.com',
        studentNumber: '2024-00542',
        course: 'Grade 12 - HUMSS Strand',
        yearLevel: 12,
      ),
      AppointmentModel(
        id: 103,
        studentId: 3,
        counselorId: 2,
        appointmentDate: yesterday,
        startTime: '09:00:00',
        endTime: '10:00:00',
        concern: '[Face-to-face] Anxiety: Managing performance anxiety during classroom presentations',
        status: 'completed',
        adminNotes: 'Student completed session well. Provided breathing techniques and weekly journal.',
        counselorName: 'Dr. Maria Santos',
        counselorEmail: 'maria.santos@guidesched.com',
        counselorSpecialization: 'Academic & Career Counseling',
        studentName: 'Juan Santos',
        studentEmail: 'juan.santos@cagasaths.edu.ph',
        studentNumber: '2024-00129',
        course: 'Grade 11 - STEM Strand',
        yearLevel: 11,
      ),
    ];

    // 3. Notifications
    _notifications = [
      NotificationModel(
        id: 1,
        userId: 3,
        appointmentId: 101,
        message: 'Your appointment request for $tomorrow at 10:00 AM has been submitted and is pending approval.',
        type: 'info',
        isRead: false,
        createdAt: DateTime.now().subtract(const Duration(minutes: 25)).toIso8601String(),
      ),
      NotificationModel(
        id: 2,
        userId: 3,
        appointmentId: 103,
        message: 'Your appointment on $yesterday at 9:00 AM was marked as completed. Keep up the great consistency!',
        type: 'approved',
        isRead: true,
        createdAt: DateTime.now().subtract(const Duration(days: 1, hours: 2)).toIso8601String(),
      ),
      NotificationModel(
        id: 3,
        userId: 2,
        appointmentId: 101,
        message: 'New appointment request from Juan Santos for $tomorrow at 10:00 AM.',
        type: 'info',
        isRead: false,
        createdAt: DateTime.now().subtract(const Duration(minutes: 25)).toIso8601String(),
      ),
    ];

    // 4. Default slots
    _defaultSlots = [
      AvailabilitySlotModel(id: 1, startTime: '09:00:00', endTime: '10:00:00', status: 'available', isAvailable: true),
      AvailabilitySlotModel(id: 2, startTime: '10:00:00', endTime: '11:00:00', status: 'available', isAvailable: true),
      AvailabilitySlotModel(id: 3, startTime: '11:00:00', endTime: '12:00:00', status: 'available', isAvailable: true),
      AvailabilitySlotModel(id: 4, startTime: '13:00:00', endTime: '14:00:00', status: 'available', isAvailable: true),
      AvailabilitySlotModel(id: 5, startTime: '14:00:00', endTime: '15:00:00', status: 'available', isAvailable: true),
      AvailabilitySlotModel(id: 6, startTime: '15:00:00', endTime: '16:00:00', status: 'available', isAvailable: true),
      AvailabilitySlotModel(id: 7, startTime: '16:00:00', endTime: '17:00:00', status: 'available', isAvailable: true),
    ];
  }

  // --- Auth Mock ---
  Future<UserModel> login(String email, String password) async {
    await Future.delayed(const Duration(milliseconds: 300));
    final user = _users.firstWhere(
      (u) => u.email.toLowerCase() == email.toLowerCase().trim(),
      orElse: () {
        // Fallback demo user if typed anything else
        if (email.contains('counselor') || email.contains('maria') || email.contains('admin')) {
          return _users[0];
        }
        return _users[1];
      },
    );
    return user;
  }

  Future<UserModel> register(Map<String, dynamic> data) async {
    await Future.delayed(const Duration(milliseconds: 400));
    final newUser = UserModel(
      id: _users.length + 10,
      userId: 'STU${DateTime.now().millisecondsSinceEpoch % 1000000}',
      role: 'student',
      name: data['name'] ?? 'New Student',
      email: data['email'] ?? 'student@guidesched.com',
      status: 'active',
      studentNumber: data['student_number'] ?? '2024-99999',
      course: data['course'] ?? 'Grade 11 - STEM',
      yearLevel: data['year_level'] ?? 11,
      contactNumber: data['contact_number'] ?? '',
    );
    _users.add(newUser);
    return newUser;
  }

  // --- Counselors Mock ---
  Future<List<UserModel>> getCounselors() async {
    return _users.where((u) => u.isCounselor).toList();
  }

  // --- Availability Mock ---
  Future<List<AvailabilitySlotModel>> getAvailability(int counselorId, String date) async {
    await Future.delayed(const Duration(milliseconds: 200));
    // Mark slot booked if there is an appointment on this date and time
    final bookedStartTimes = _appointments
        .where((a) => a.counselorId == counselorId && a.appointmentDate == date && (a.isPending || a.isApproved))
        .map((a) => a.startTime.substring(0, 5))
        .toList();

    return _defaultSlots.map((slot) {
      final slotStart = slot.startTime.substring(0, 5);
      final isBooked = bookedStartTimes.contains(slotStart);
      return AvailabilitySlotModel(
        id: slot.id,
        startTime: slot.startTime,
        endTime: slot.endTime,
        status: isBooked ? 'booked' : 'available',
        isAvailable: !isBooked,
      );
    }).toList();
  }

  // --- Appointments Mock ---
  Future<List<AppointmentModel>> getAppointments({required int userId, required String role, String? status}) async {
    await Future.delayed(const Duration(milliseconds: 250));
    List<AppointmentModel> list;
    if (role == 'student') {
      list = _appointments.where((a) => a.studentId == userId).toList();
    } else {
      list = _appointments.where((a) => a.counselorId == userId || role == 'admin').toList();
    }

    if (status != null && status.isNotEmpty && status != 'all') {
      list = list.where((a) => a.status == status).toList();
    }
    return list;
  }

  Future<AppointmentModel> bookAppointment({
    required int studentId,
    required int counselorId,
    required String date,
    required String startTime,
    required String endTime,
    required String concern,
  }) async {
    await Future.delayed(const Duration(milliseconds: 300));
    final counselor = _users.firstWhere((u) => u.id == counselorId, orElse: () => _users[0]);
    final student = _users.firstWhere((u) => u.id == studentId, orElse: () => _users[1]);

    final newApt = AppointmentModel(
      id: _appointments.length + 101,
      studentId: studentId,
      counselorId: counselorId,
      appointmentDate: date,
      startTime: startTime,
      endTime: endTime,
      concern: concern,
      status: 'pending',
      counselorName: counselor.name,
      counselorEmail: counselor.email,
      counselorSpecialization: counselor.specialization,
      studentName: student.name,
      studentEmail: student.email,
      studentNumber: student.studentNumber,
      course: student.course,
      yearLevel: student.yearLevel,
    );

    _appointments.insert(0, newApt);

    _notifications.insert(0, NotificationModel(
      id: _notifications.length + 1,
      userId: studentId,
      appointmentId: newApt.id,
      message: 'Your appointment request for $date at $startTime has been placed.',
      type: 'info',
      isRead: false,
      createdAt: DateTime.now().toIso8601String(),
    ));

    return newApt;
  }

  Future<void> updateAppointmentStatus({
    required int appointmentId,
    required String newStatus,
    String? adminNotes,
  }) async {
    await Future.delayed(const Duration(milliseconds: 200));
    final index = _appointments.indexWhere((a) => a.id == appointmentId);
    if (index != -1) {
      final old = _appointments[index];
      _appointments[index] = AppointmentModel(
        id: old.id,
        studentId: old.studentId,
        counselorId: old.counselorId,
        appointmentDate: old.appointmentDate,
        startTime: old.startTime,
        endTime: old.endTime,
        concern: old.concern,
        status: newStatus,
        adminNotes: adminNotes ?? old.adminNotes,
        createdAt: old.createdAt,
        counselorName: old.counselorName,
        counselorEmail: old.counselorEmail,
        counselorSpecialization: old.counselorSpecialization,
        studentName: old.studentName,
        studentEmail: old.studentEmail,
        studentNumber: old.studentNumber,
        course: old.course,
        yearLevel: old.yearLevel,
      );

      _notifications.insert(0, NotificationModel(
        id: _notifications.length + 1,
        userId: old.studentId,
        appointmentId: old.id,
        message: 'Your appointment on ${old.formattedDate} was updated to $newStatus.',
        type: newStatus == 'approved' ? 'approved' : (newStatus == 'declined' ? 'declined' : 'info'),
        isRead: false,
        createdAt: DateTime.now().toIso8601String(),
      ));
    }
  }

  // --- Notifications Mock ---
  Future<List<NotificationModel>> getNotifications(int userId) async {
    return _notifications.where((n) => n.userId == userId).toList();
  }

  Future<void> markAllNotificationsRead(int userId) async {
    _notifications = _notifications.map((n) {
      if (n.userId == userId) {
        return NotificationModel(
          id: n.id,
          userId: n.userId,
          appointmentId: n.appointmentId,
          message: n.message,
          type: n.type,
          isRead: true,
          createdAt: n.createdAt,
        );
      }
      return n;
    }).toList();
  }

  // --- Analytics Mock ---
  StudentAnalyticsModel getStudentAnalytics(int userId) {
    return StudentAnalyticsModel(
      totalSessions: 4,
      completedSessions: 3,
      upcomingSessions: 1,
      topConcern: 'Academic stress',
      consistencyStreakMonths: 3,
      monthlyChart: [
        MonthlyDataPoint(month: 'May', count: 1),
        MonthlyDataPoint(month: 'Jun', count: 0),
        MonthlyDataPoint(month: 'Jul', count: 1),
        MonthlyDataPoint(month: 'Aug', count: 2),
        MonthlyDataPoint(month: 'Sep', count: 1),
      ],
      concernBreakdown: {
        'Academic stress': 2,
        'Anxiety': 1,
        'Career guidance': 1,
      },
    );
  }

  CounselorAnalyticsModel getCounselorAnalytics(int userId) {
    return CounselorAnalyticsModel(
      todayAppointments: 2,
      pendingRequests: 1,
      approvedSessions: 4,
      completedSessions: 8,
      totalAppointments: 15,
      noShowRate: 6.7,
      monthlyTrends: [
        MonthlyDataPoint(month: 'May', count: 3),
        MonthlyDataPoint(month: 'Jun', count: 5),
        MonthlyDataPoint(month: 'Jul', count: 4),
        MonthlyDataPoint(month: 'Aug', count: 9),
        MonthlyDataPoint(month: 'Sep', count: 6),
      ],
      concernDistribution: {
        'Academic stress': 6,
        'Anxiety': 4,
        'Family concerns': 2,
        'Career & Strand': 3,
      },
      statusSummary: {
        'pending': 1,
        'approved': 4,
        'completed': 8,
        'declined': 1,
        'no_show': 1,
      },
    );
  }

  List<UserModel> getStudentsDirectory() {
    return _users.where((u) => u.isStudent).toList();
  }
}
