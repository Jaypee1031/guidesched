import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../theme/app_theme.dart';
import '../../widgets/empty_state_view.dart';

class StudentNotificationsScreen extends StatelessWidget {
  const StudentNotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().currentUser;
    final notifProvider = context.watch<NotificationProvider>();
    final notifications = notifProvider.notifications;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          if (notifications.isNotEmpty && notifProvider.unreadCount > 0)
            TextButton.icon(
              onPressed: () {
                if (user != null) {
                  notifProvider.markAllAsRead(user.id);
                }
              },
              icon: const Icon(Icons.done_all, size: 16, color: AppTheme.primary),
              label: const Text('Mark all read', style: TextStyle(fontSize: 12.5)),
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: notifications.isEmpty
          ? const EmptyStateView(
              icon: Icons.notifications_none_rounded,
              title: 'No notifications',
              description: 'You are all caught up! Updates regarding your bookings will appear here.',
            )
          : RefreshIndicator(
              color: AppTheme.primary,
              onRefresh: () async {
                if (user != null) {
                  await notifProvider.fetchNotifications(user.id);
                }
              },
              child: ListView.separated(
                padding: const EdgeInsets.all(16),
                itemCount: notifications.length,
                separatorBuilder: (_, __) => const SizedBox(height: 10),
                itemBuilder: (context, index) {
                  final notif = notifications[index];
                  return Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: notif.isRead ? Colors.white : AppTheme.surfaceMint,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: notif.isRead ? AppTheme.borderSubtle : AppTheme.borderGreen,
                        width: notif.isRead ? 1 : 1.5,
                      ),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: notif.color.withValues(alpha: 0.12),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(notif.icon, color: notif.color, size: 20),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                notif.message,
                                style: TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: notif.isRead ? FontWeight.w500 : FontWeight.bold,
                                  color: AppTheme.textDark,
                                  height: 1.35,
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                notif.timeAgo,
                                style: const TextStyle(fontSize: 11.5, color: AppTheme.textMuted),
                              ),
                            ],
                          ),
                        ),
                        if (!notif.isRead)
                          Container(
                            width: 8,
                            height: 8,
                            margin: const EdgeInsets.only(top: 4, left: 6),
                            decoration: const BoxDecoration(
                              color: AppTheme.primary,
                              shape: BoxShape.circle,
                            ),
                          ),
                      ],
                    ),
                  );
                },
              ),
            ),
    );
  }
}
