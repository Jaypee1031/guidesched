import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class CounselorProfileScreen extends StatelessWidget {
  const CounselorProfileScreen({super.key});

  void _showChangePasswordDialog(BuildContext context) {
    final curController = TextEditingController();
    final newController = TextEditingController();
    final formKey = GlobalKey<FormState>();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Change Password'),
        content: Form(
          key: formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextFormField(
                controller: curController,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'Current Password'),
                validator: (v) => (v == null || v.isEmpty) ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: newController,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'New Password'),
                validator: (v) => (v == null || v.length < 6) ? 'Min 6 characters' : null,
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              if (formKey.currentState!.validate()) {
                final auth = context.read<AuthProvider>();
                final ok = await auth.changePassword(curController.text, newController.text);
                if (ctx.mounted) Navigator.pop(ctx);
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(ok ? 'Password updated successfully' : (auth.errorMessage ?? 'Failed')),
                      backgroundColor: ok ? AppTheme.primary : AppTheme.statusCancelled,
                    ),
                  );
                }
              }
            },
            child: const Text('Update'),
          ),
        ],
      ),
    );
  }

  void _showApiSettingsDialog(BuildContext context) {
    final urlController = TextEditingController(text: ApiService().baseUrl);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Backend API Settings'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Customize the PHP backend URL if connecting to a real device or custom LAN IP:',
              style: TextStyle(fontSize: 12.5, color: AppTheme.textMuted),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: urlController,
              decoration: const InputDecoration(
                labelText: 'API Base URL',
                hintText: 'http://192.168.1.X/.../api',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () {
              ApiService().setBaseUrl(urlController.text.trim());
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('API URL updated'), backgroundColor: AppTheme.primary),
              );
            },
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().currentUser;

    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        // Header Card
        Card(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
            side: const BorderSide(color: AppTheme.borderSubtle),
          ),
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              children: [
                CircleAvatar(
                  radius: 38,
                  backgroundColor: AppTheme.primary,
                  child: Text(
                    user?.initials ?? 'C',
                    style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.white),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  user?.name ?? 'Dr. Maria Santos',
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryContainer,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'Employee ID: ${user?.userId ?? "COUNSELOR001"}',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  user?.specialization ?? 'Academic & Career Guidance Counselor',
                  style: const TextStyle(fontSize: 13.5, color: AppTheme.textMuted),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 20),

        // Counselor Office Information
        const Text(
          'Office & Contact Details',
          style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textDark),
        ),
        const SizedBox(height: 10),
        Card(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.borderSubtle),
          ),
          child: Column(
            children: [
              _buildInfoTile(Icons.email_outlined, 'Email', user?.email ?? 'maria.santos@guidesched.com'),
              const Divider(height: 1),
              _buildInfoTile(Icons.phone_outlined, 'Contact', user?.contactNumber?.isNotEmpty == true ? user!.contactNumber! : '+63 912 345 6789'),
              const Divider(height: 1),
              _buildInfoTile(Icons.meeting_room_outlined, 'Office Location', 'Guidance Center, Room 204'),
              const Divider(height: 1),
              _buildInfoTile(Icons.schedule_outlined, 'Office Hours', 'Mon–Fri 8:00 AM – 5:00 PM'),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // System Configuration
        const Text(
          'Settings & Preferences',
          style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textDark),
        ),
        const SizedBox(height: 10),
        Card(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.borderSubtle),
          ),
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.lock_outline, color: AppTheme.primary),
                title: const Text('Change Password', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                trailing: const Icon(Icons.chevron_right, color: AppTheme.textMuted),
                onTap: () => _showChangePasswordDialog(context),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.tune_rounded, color: AppTheme.primary),
                title: const Text('Backend API Settings', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                subtitle: Text(ApiService().baseUrl, style: const TextStyle(fontSize: 11.5, color: AppTheme.textMuted)),
                trailing: const Icon(Icons.chevron_right, color: AppTheme.textMuted),
                onTap: () => _showApiSettingsDialog(context),
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),

        // Sign Out Button
        OutlinedButton.icon(
          onPressed: () {
            context.read<AuthProvider>().logout();
          },
          icon: const Icon(Icons.logout_rounded, color: AppTheme.statusCancelled),
          label: const Text('Sign Out', style: TextStyle(color: AppTheme.statusCancelled, fontWeight: FontWeight.bold)),
          style: OutlinedButton.styleFrom(
            side: const BorderSide(color: AppTheme.statusCancelled),
            padding: const EdgeInsets.symmetric(vertical: 14),
          ),
        ),
        const SizedBox(height: 20),
      ],
    );
  }

  Widget _buildInfoTile(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      child: Row(
        children: [
          Icon(icon, size: 20, color: AppTheme.primary),
          const SizedBox(width: 14),
          Text(label, style: const TextStyle(fontSize: 13, color: AppTheme.textMuted)),
          const Spacer(),
          Text(value, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: AppTheme.textDark)),
        ],
      ),
    );
  }
}
