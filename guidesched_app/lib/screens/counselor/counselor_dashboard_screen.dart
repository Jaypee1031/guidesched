import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/appointment_provider.dart';
import '../../theme/app_theme.dart';
import '../../widgets/green_banner.dart';
import '../../widgets/stat_summary_card.dart';
import '../../widgets/appointment_card.dart';

class CounselorDashboardScreen extends StatelessWidget {
  final VoidCallback onNavigateToAppointments;

  const CounselorDashboardScreen({super.key, required this.onNavigateToAppointments});

  void _showActionDialog(BuildContext context, int appointmentId, String action) {
    final noteController = TextEditingController();
    final isApprove = action == 'approve';

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isApprove ? 'Approve Appointment' : 'Decline Appointment'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isApprove
                  ? 'Confirm this session with the student? You can optionally include instructions or meeting details.'
                  : 'Please state the reason for declining or suggest another date.',
            ),
            const SizedBox(height: 12),
            TextField(
              controller: noteController,
              decoration: InputDecoration(
                labelText: isApprove ? 'Counselor Notes (optional)' : 'Reason / Note to student',
                hintText: isApprove ? 'e.g. Please bring your midterm grades' : 'e.g. Schedule conflict, please book another day',
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              final user = context.read<AuthProvider>().currentUser;
              if (user != null) {
                Navigator.pop(ctx);
                final aptProvider = context.read<AppointmentProvider>();
                await aptProvider.updateStatus(
                  appointmentId: appointmentId,
                  action: action,
                  changedBy: user.id,
                  adminNotes: noteController.text.trim(),
                );
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Appointment $action${action.endsWith('e') ? 'd' : 'ed'}'),
                      backgroundColor: isApprove ? AppTheme.primary : AppTheme.statusCancelled,
                    ),
                  );
                }
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: isApprove ? AppTheme.primary : AppTheme.statusCancelled,
            ),
            child: Text(isApprove ? 'Approve' : 'Decline'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().currentUser;
    final aptProvider = context.watch<AppointmentProvider>();

    final pending = aptProvider.pendingAppointments;
    final approved = aptProvider.approvedAppointments;
    final all = aptProvider.appointments;
    final completedCount = all.where((a) => a.isCompleted).length;

    return RefreshIndicator(
      color: AppTheme.primary,
      onRefresh: () async {
        if (user != null) {
          await aptProvider.fetchAppointments(userId: user.id, role: user.role);
        }
      },
      child: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        children: [
          // Banner
          GreenBanner(
            title: 'Welcome, ${user?.name ?? "Counselor"}',
            subtitle: 'Guidance & Counseling Portal • Manage student sessions and availability.',
            badgeText: 'Counselor On-Duty',
          ),
          const SizedBox(height: 18),

          // 4 Metric cards
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 1.25,
            children: [
              StatSummaryCard(
                title: 'Pending Requests',
                value: '${pending.length}',
                icon: Icons.hourglass_top_rounded,
                color: Colors.amber.shade700,
                subtitle: 'Action needed',
                onTap: onNavigateToAppointments,
              ),
              StatSummaryCard(
                title: 'Approved Sessions',
                value: '${approved.length}',
                icon: Icons.check_circle_outline,
                color: AppTheme.primary,
                onTap: onNavigateToAppointments,
              ),
              StatSummaryCard(
                title: 'Completed',
                value: '$completedCount',
                icon: Icons.task_alt_rounded,
                color: Colors.teal.shade700,
              ),
              StatSummaryCard(
                title: 'Total Bookings',
                value: '${all.length}',
                icon: Icons.calendar_today_rounded,
                color: Colors.blue.shade700,
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Pending Requests section
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Text(
                    'Pending Requests',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                  ),
                  if (pending.isNotEmpty) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppTheme.statusPendingBg,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '${pending.length}',
                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: AppTheme.statusPending),
                      ),
                    ),
                  ],
                ],
              ),
              TextButton(
                onPressed: onNavigateToAppointments,
                child: const Text('View All', style: TextStyle(fontSize: 13)),
              ),
            ],
          ),
          const SizedBox(height: 8),

          if (pending.isEmpty)
            Card(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: const BorderSide(color: AppTheme.borderSubtle),
              ),
              child: const Padding(
                padding: EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                child: Center(
                  child: Column(
                    children: [
                      Icon(Icons.done_all_rounded, color: AppTheme.primary, size: 32),
                      SizedBox(height: 8),
                      Text(
                        'All caught up!',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppTheme.textDark),
                      ),
                      Text(
                        'No pending appointment requests at the moment.',
                        style: TextStyle(fontSize: 12.5, color: AppTheme.textMuted),
                      ),
                    ],
                  ),
                ),
              ),
            )
          else
            ...pending.take(3).map(
              (apt) => AppointmentCard(
                appointment: apt,
                isCounselorView: true,
                onApprove: () => _showActionDialog(context, apt.id, 'approve'),
                onDecline: () => _showActionDialog(context, apt.id, 'decline'),
              ),
            ),
          const SizedBox(height: 24),

          // Approved Sessions section
          if (approved.isNotEmpty) ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Upcoming Approved Sessions',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                ),
                TextButton(
                  onPressed: onNavigateToAppointments,
                  child: const Text('View All', style: TextStyle(fontSize: 13)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            ...approved.take(3).map(
              (apt) => AppointmentCard(
                appointment: apt,
                isCounselorView: true,
                onComplete: () async {
                  if (user != null) {
                    await aptProvider.updateStatus(
                      appointmentId: apt.id,
                      action: 'complete',
                      changedBy: user.id,
                    );
                  }
                },
              ),
            ),
          ],
        ],
      ),
    );
  }
}
