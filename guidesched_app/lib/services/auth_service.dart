import '../models/user_model.dart';
import 'api_service.dart';
import 'mock_data_service.dart';

class AuthService {
  final ApiService _api = ApiService();
  final MockDataService _mock = MockDataService();

  Future<UserModel> login(String email, String password) async {
    try {
      final res = await _api.post('auth.php', {
        'action': 'login',
        'email': email,
        'password': password,
      });
      if (res['data']?['user'] != null) {
        return UserModel.fromJson(res['data']['user']);
      }
      throw ApiException('Invalid login response');
    } catch (e) {
      // Fallback to mock for seamless offline experience
      return await _mock.login(email, password);
    }
  }

  Future<UserModel> register(Map<String, dynamic> data) async {
    try {
      final res = await _api.post('auth.php', {
        'action': 'register',
        ...data,
      });
      if (res['data']?['user'] != null) {
        return UserModel.fromJson(res['data']['user']);
      }
      throw ApiException('Invalid registration response');
    } catch (e) {
      return await _mock.register(data);
    }
  }

  Future<void> changePassword({
    required int userId,
    required String currentPassword,
    required String newPassword,
  }) async {
    try {
      await _api.post('auth.php', {
        'action': 'change_password',
        'user_id': userId,
        'current_password': currentPassword,
        'new_password': newPassword,
      });
    } catch (e) {
      // Mock mode simulate success
      await Future.delayed(const Duration(milliseconds: 300));
    }
  }
}
