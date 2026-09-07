import 'package:flutter/material.dart';
import '../models/appointment_model.dart';
import '../models/availability_slot_model.dart';
import '../models/user_model.dart';
import '../services/appointment_service.dart';

class AppointmentProvider extends ChangeNotifier {
  final AppointmentService _service = AppointmentService();

  List<AppointmentModel> _appointments = [];
  List<UserModel> _counselors = [];
  List<AvailabilitySlotModel> _availabilitySlots = [];

  bool _isLoading = false;
  bool _isSlotsLoading = false;
  String? _errorMessage;

  List<AppointmentModel> get appointments => _appointments;
  List<UserModel> get counselors => _counselors;
  List<AvailabilitySlotModel> get availabilitySlots => _availabilitySlots;
  bool get isLoading => _isLoading;
  bool get isSlotsLoading => _isSlotsLoading;
  String? get errorMessage => _errorMessage;

  // Filter helpers
  List<AppointmentModel> get upcomingAppointments => _appointments
      .where((a) => a.isPending || a.isApproved)
      .toList();

  List<AppointmentModel> get pastAppointments => _appointments
      .where((a) => a.isCompleted || a.isCancelled || a.isDeclined || a.isNoShow)
      .toList();

  List<AppointmentModel> get pendingAppointments => _appointments
      .where((a) => a.isPending)
      .toList();

  List<AppointmentModel> get approvedAppointments => _appointments
      .where((a) => a.isApproved)
      .toList();

  AppointmentModel? get nextUpcomingAppointment {
    final upcoming = upcomingAppointments;
    if (upcoming.isEmpty) return null;
    upcoming.sort((a, b) => a.appointmentDate.compareTo(b.appointmentDate));
    return upcoming.first;
  }

  Future<void> fetchAppointments({required int userId, required String role}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _appointments = await _service.getAppointments(userId: userId, role: role);
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchCounselors() async {
    try {
      _counselors = await _service.getCounselors();
      notifyListeners();
    } catch (_) {}
  }

  Future<void> fetchAvailability({required int counselorId, required String date}) async {
    _isSlotsLoading = true;
    _availabilitySlots = [];
    notifyListeners();

    try {
      _availabilitySlots = await _service.getAvailability(counselorId: counselorId, date: date);
      _isSlotsLoading = false;
      notifyListeners();
    } catch (e) {
      _isSlotsLoading = false;
      notifyListeners();
    }
  }

  Future<bool> bookAppointment({
    required int studentId,
    required int counselorId,
    required String date,
    required String startTime,
    required String endTime,
    required String mode,
    required String concernCategory,
    String? details,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final newApt = await _service.bookAppointment(
        studentId: studentId,
        counselorId: counselorId,
        date: date,
        startTime: startTime,
        endTime: endTime,
        mode: mode,
        concernCategory: concernCategory,
        details: details,
      );

      _appointments.insert(0, newApt);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateStatus({
    required int appointmentId,
    required String action,
    required int changedBy,
    String? adminNotes,
  }) async {
    _isLoading = true;
    notifyListeners();

    try {
      await _service.updateStatus(
        appointmentId: appointmentId,
        action: action,
        changedBy: changedBy,
        adminNotes: adminNotes,
      );

      // Locally update status
      final statusMap = {
        'approve': 'approved',
        'decline': 'declined',
        'complete': 'completed',
        'cancel': 'cancelled',
        'noshow': 'no_show',
      };
      final newSt = statusMap[action] ?? action;
      final idx = _appointments.indexWhere((a) => a.id == appointmentId);
      if (idx != -1) {
        final cur = _appointments[idx];
        _appointments[idx] = AppointmentModel(
          id: cur.id,
          studentId: cur.studentId,
          counselorId: cur.counselorId,
          appointmentDate: cur.appointmentDate,
          startTime: cur.startTime,
          endTime: cur.endTime,
          concern: cur.concern,
          status: newSt,
          adminNotes: adminNotes ?? cur.adminNotes,
          createdAt: cur.createdAt,
          counselorName: cur.counselorName,
          counselorEmail: cur.counselorEmail,
          counselorSpecialization: cur.counselorSpecialization,
          studentName: cur.studentName,
          studentEmail: cur.studentEmail,
          studentNumber: cur.studentNumber,
          course: cur.course,
          yearLevel: cur.yearLevel,
        );
      }

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateCounselorNotes({
    required int appointmentId,
    required String notes,
    required int changedBy,
  }) async {
    _isLoading = true;
    notifyListeners();

    try {
      await _service.updateNotes(
        appointmentId: appointmentId,
        adminNotes: notes,
        changedBy: changedBy,
      );

      final idx = _appointments.indexWhere((a) => a.id == appointmentId);
      if (idx != -1) {
        final cur = _appointments[idx];
        _appointments[idx] = AppointmentModel(
          id: cur.id,
          studentId: cur.studentId,
          counselorId: cur.counselorId,
          appointmentDate: cur.appointmentDate,
          startTime: cur.startTime,
          endTime: cur.endTime,
          concern: cur.concern,
          status: cur.status,
          adminNotes: notes,
          createdAt: cur.createdAt,
          counselorName: cur.counselorName,
          counselorEmail: cur.counselorEmail,
          counselorSpecialization: cur.counselorSpecialization,
          studentName: cur.studentName,
          studentEmail: cur.studentEmail,
          studentNumber: cur.studentNumber,
          course: cur.course,
          yearLevel: cur.yearLevel,
        );
      }

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
