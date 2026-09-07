import '../models/analytics_model.dart';
import 'api_service.dart';
import 'mock_data_service.dart';

class AnalyticsService {
  final ApiService _api = ApiService();
  final MockDataService _mock = MockDataService();

  Future<StudentAnalyticsModel> getStudentAnalytics(int userId) async {
    try {
      final res = await _api.get('analytics.php', queryParams: {
        'user_id': userId.toString(),
        'role': 'student',
      });
      if (res['data'] != null) {
        return StudentAnalyticsModel.fromJson(res['data']);
      }
      throw ApiException('Invalid analytics data');
    } catch (_) {
      return _mock.getStudentAnalytics(userId);
    }
  }

  Future<CounselorAnalyticsModel> getCounselorAnalytics(int userId) async {
    try {
      final res = await _api.get('analytics.php', queryParams: {
        'user_id': userId.toString(),
        'role': 'counselor',
      });
      if (res['data'] != null) {
        return CounselorAnalyticsModel.fromJson(res['data']);
      }
      throw ApiException('Invalid analytics data');
    } catch (_) {
      return _mock.getCounselorAnalytics(userId);
    }
  }
}
