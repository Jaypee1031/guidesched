import '../models/appointment_model.dart';
import '../models/availability_slot_model.dart';
import '../models/user_model.dart';
import 'api_service.dart';
import 'mock_data_service.dart';

class AppointmentService {
  final ApiService _api = ApiService();
  final MockDataService _mock = MockDataService();

  Future<List<UserModel>> getCounselors() async {
    try {
      final res = await _api.get('counselors.php');
      final list = res['data']?['counselors'] as List<dynamic>? ?? [];
      return list.map((e) => UserModel.fromJson(e as Map<String, dynamic>)).toList();
    } catch (_) {
      return await _mock.getCounselors();
    }
  }

  Future<List<AvailabilitySlotModel>> getAvailability({required int counselorId, required String date}) async {
    try {
      final res = await _api.get('availability.php', queryParams: {
        'counselor_id': counselorId.toString(),
        'date': date,
      });
      final list = res['data']?['slots'] as List<dynamic>? ?? [];
      return list.map((e) => AvailabilitySlotModel.fromJson(e as Map<String, dynamic>)).toList();
    } catch (_) {
      return await _mock.getAvailability(counselorId, date);
    }
  }

  Future<List<AppointmentModel>> getAppointments({required int userId, required String role, String? status}) async {
    try {
      final qParams = {
        'user_id': userId.toString(),
        'role': role,
      };
      if (status != null && status.isNotEmpty && status != 'all') {
        qParams['status'] = status;
      }
      final res = await _api.get('appointments.php', queryParams: qParams);
      final list = res['data']?['appointments'] as List<dynamic>? ?? [];
      return list.map((e) => AppointmentModel.fromJson(e as Map<String, dynamic>)).toList();
    } catch (_) {
      return await _mock.getAppointments(userId: userId, role: role, status: status);
    }
  }

  Future<AppointmentModel> bookAppointment({
    required int studentId,
    required int counselorId,
    required String date,
    required String startTime,
    required String endTime,
    required String mode,
    required String concernCategory,
    String? details,
  }) async {
    final concern = '[$mode] $concernCategory${details != null && details.isNotEmpty ? ': $details' : ''}';

    try {
      final res = await _api.post('appointments.php', {
        'action': 'book',
        'student_id': studentId,
        'counselor_id': counselorId,
        'appointment_date': date,
        'start_time': startTime,
        'end_time': endTime,
        'mode': mode,
        'concern_category': concernCategory,
        'details': details ?? '',
      });

      final aptId = res['data']?['appointment_id'] ?? 0;
      return AppointmentModel(
        id: aptId is int ? aptId : int.tryParse(aptId.toString()) ?? 0,
        studentId: studentId,
        counselorId: counselorId,
        appointmentDate: date,
        startTime: startTime,
        endTime: endTime,
        concern: concern,
        status: 'pending',
      );
    } catch (_) {
      return await _mock.bookAppointment(
        studentId: studentId,
        counselorId: counselorId,
        date: date,
        startTime: startTime,
        endTime: endTime,
        concern: concern,
      );
    }
  }

  Future<void> updateStatus({
    required int appointmentId,
    required String action, // 'approve', 'decline', 'complete', 'cancel', 'noshow'
    required int changedBy,
    String? adminNotes,
  }) async {
    try {
      await _api.post('appointments.php', {
        'id': appointmentId,
        'action': action,
        'changed_by': changedBy,
        'admin_notes': adminNotes ?? '',
      });
    } catch (_) {
      final statusMap = {
        'approve': 'approved',
        'decline': 'declined',
        'complete': 'completed',
        'cancel': 'cancelled',
        'noshow': 'no_show',
      };
      await _mock.updateAppointmentStatus(
        appointmentId: appointmentId,
        newStatus: statusMap[action] ?? action,
        adminNotes: adminNotes,
      );
    }
  }

  Future<void> updateNotes({
    required int appointmentId,
    required String adminNotes,
    required int changedBy,
  }) async {
    try {
      await _api.post('appointments.php', {
        'id': appointmentId,
        'action': 'update_notes',
        'changed_by': changedBy,
        'admin_notes': adminNotes,
      });
    } catch (_) {
      await _mock.updateAppointmentNotes(
        appointmentId: appointmentId,
        adminNotes: adminNotes,
      );
    }
  }
}
