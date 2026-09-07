import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/appointment_provider.dart';
import '../../theme/app_theme.dart';
import '../../widgets/green_banner.dart';
import '../../widgets/appointment_card.dart';

class StudentDashboardScreen extends StatelessWidget {
  final VoidCallback onNavigateToBook;
  final VoidCallback onNavigateToAppointments;

  const StudentDashboardScreen({
    super.key,
    required this.onNavigateToBook,
    required this.onNavigateToAppointments,
  });

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().currentUser;
    final aptProvider = context.watch<AppointmentProvider>();

    final nextAppointment = aptProvider.nextUpcomingAppointment;
    final recentAppointments = aptProvider.appointments.take(3).toList();

    return RefreshIndicator(
      color: AppTheme.primary,
      onRefresh: () async {
        if (user != null) {
          await aptProvider.fetchAppointments(userId: user.id, role: 'student');
        }
      },
      child: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        children: [
          // Greeting Green Banner
          GreenBanner(
            title: 'Hello, ${user?.name.split(' ').first ?? 'Student'}! 👋',
            subtitle: 'Welcome to your guidance portal. How are you doing today?',
            badgeText: user?.course ?? 'Cagasat High School',
            trailing: CircleAvatar(
              radius: 24,
              backgroundColor: Colors.white.withValues(alpha: 0.25),
              child: Text(
                user?.initials ?? 'S',
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
              ),
            ),
          ),
          const SizedBox(height: 18),

          // Daily Motivation Card with Consistency Streak
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppTheme.surfaceMint,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppTheme.borderGreen),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(color: AppTheme.primary.withValues(alpha: 0.1), blurRadius: 6),
                    ],
                  ),
                  child: const Text('🌱', style: TextStyle(fontSize: 24)),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Daily Mental Wellness',
                        style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
                      ),
                      SizedBox(height: 3),
                      Text(
                        '"You don’t have to struggle in silence. Reach out whenever you need guidance."',
                        style: TextStyle(fontSize: 12, color: AppTheme.textMedium, fontStyle: FontStyle.italic),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 22),

          // Next Upcoming Appointment Section
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Next Session',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textDark),
              ),
              if (nextAppointment != null)
                TextButton(
                  onPressed: onNavigateToAppointments,
                  child: const Text('View All', style: TextStyle(fontSize: 13)),
                ),
            ],
          ),
          const SizedBox(height: 10),

          if (nextAppointment != null)
            AppointmentCard(
              appointment: nextAppointment,
              isCounselorView: false,
              onCancel: () async {
                final confirm = await showDialog<bool>(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    title: const Text('Cancel Appointment?'),
                    content: const Text('Are you sure you want to cancel your scheduled appointment?'),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Keep')),
                      ElevatedButton(
                        onPressed: () => Navigator.pop(ctx, true),
                        style: ElevatedButton.styleFrom(backgroundColor: AppTheme.statusCancelled),
                        child: const Text('Cancel Request'),
                      ),
                    ],
                  ),
                );

                if (confirm == true && user != null) {
                  await aptProvider.updateStatus(
                    appointmentId: nextAppointment.id,
                    action: 'cancel',
                    changedBy: user.id,
                  );
                }
              },
            )
          else
            Card(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: const BorderSide(color: AppTheme.borderSubtle),
              ),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: const BoxDecoration(
                        color: AppTheme.surfaceMint,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.calendar_today_outlined, size: 28, color: AppTheme.primary),
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'No Upcoming Sessions',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'You have no scheduled counseling sessions at the moment.',
                      style: TextStyle(fontSize: 12.5, color: AppTheme.textMuted),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton.icon(
                      onPressed: onNavigateToBook,
                      icon: const Icon(Icons.add, size: 18),
                      label: const Text('Book an Appointment'),
                    ),
                  ],
                ),
              ),
            ),
          const SizedBox(height: 24),

          // Quick Action Booking Banner
          InkWell(
            onTap: onNavigateToBook,
            borderRadius: BorderRadius.circular(16),
            child: Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppTheme.borderGreen, width: 1.2),
                boxShadow: [
                  BoxShadow(color: AppTheme.primary.withValues(alpha: 0.08), blurRadius: 10, offset: const Offset(0, 4)),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppTheme.primary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.edit_calendar_rounded, color: Colors.white, size: 24),
                  ),
                  const SizedBox(width: 14),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Need Guidance?',
                          style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
                        ),
                        SizedBox(height: 2),
                        Text(
                          'Schedule face-to-face or online counseling with Dr. Maria Santos.',
                          style: TextStyle(fontSize: 12, color: AppTheme.textMedium),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right, color: AppTheme.primary),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Recent Activity Section
          if (recentAppointments.isNotEmpty) ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Recent Activity',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                ),
                TextButton(
                  onPressed: onNavigateToAppointments,
                  child: const Text('See All', style: TextStyle(fontSize: 13)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            ...recentAppointments.map(
              (apt) => AppointmentCard(
                appointment: apt,
                isCounselorView: false,
              ),
            ),
          ],
        ],
      ),
    );
  }
}
