import '../models/notification_model.dart';
import 'api_service.dart';
import 'mock_data_service.dart';

class NotificationService {
  final ApiService _api = ApiService();
  final MockDataService _mock = MockDataService();

  Future<List<NotificationModel>> getNotifications(int userId) async {
    try {
      final res = await _api.get('notifications.php', queryParams: {
        'user_id': userId.toString(),
      });
      final list = res['data']?['notifications'] as List<dynamic>? ?? [];
      return list.map((e) => NotificationModel.fromJson(e as Map<String, dynamic>)).toList();
    } catch (_) {
      return await _mock.getNotifications(userId);
    }
  }

  Future<void> markAllAsRead(int userId) async {
    try {
      await _api.post('notifications.php', {
        'user_id': userId,
        'mark_all': true,
      });
    } catch (_) {
      await _mock.markAllNotificationsRead(userId);
    }
  }
}
