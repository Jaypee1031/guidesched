import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/appointment_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/appointment_provider.dart';
import '../../theme/app_theme.dart';
import '../../widgets/appointment_card.dart';
import '../../widgets/empty_state_view.dart';

class CounselorAppointmentsScreen extends StatefulWidget {
  const CounselorAppointmentsScreen({super.key});

  @override
  State<CounselorAppointmentsScreen> createState() => _CounselorAppointmentsScreenState();
}

class _CounselorAppointmentsScreenState extends State<CounselorAppointmentsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _showActionDialog(int appointmentId, String action) {
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
                labelText: isApprove ? 'Counselor Notes (optional)' : 'Reason for declining',
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
                if (mounted) {
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

    return Scaffold(
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Appointment Management',
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
                ),
                const SizedBox(height: 10),
                TextField(
                  onChanged: (val) => setState(() => _searchQuery = val.toLowerCase()),
                  decoration: InputDecoration(
                    hintText: 'Search by student name, LRN, or concern...',
                    prefixIcon: const Icon(Icons.search, color: AppTheme.primary),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    fillColor: Colors.grey.shade50,
                  ),
                ),
              ],
            ),
          ),
          TabBar(
            controller: _tabController,
            indicatorColor: AppTheme.primary,
            indicatorWeight: 3,
            labelColor: AppTheme.primary,
            unselectedLabelColor: AppTheme.textMuted,
            labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
            tabs: const [
              Tab(text: 'Pending'),
              Tab(text: 'Approved'),
              Tab(text: 'All Sessions'),
            ],
          ),
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildList(
                  aptProvider.pendingAppointments.where((a) => _matchesSearch(a)).toList(),
                  'No pending requests',
                  'When students book an appointment, it will appear here for review.',
                  user,
                ),
                _buildList(
                  aptProvider.approvedAppointments.where((a) => _matchesSearch(a)).toList(),
                  'No approved sessions',
                  'Approved appointments will appear here.',
                  user,
                ),
                _buildList(
                  aptProvider.appointments.where((a) => _matchesSearch(a)).toList(),
                  'No appointments found',
                  'There are no appointment records matching your criteria.',
                  user,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  bool _matchesSearch(AppointmentModel apt) {
    if (_searchQuery.isEmpty) return true;
    final text = '${apt.studentName} ${apt.studentNumber} ${apt.appointmentDate} ${apt.concern} ${apt.status}'.toLowerCase();
    return text.contains(_searchQuery);
  }

  Widget _buildList(List<AppointmentModel> list, String emptyTitle, String emptySubtitle, dynamic user) {
    if (list.isEmpty) {
      return EmptyStateView(
        icon: Icons.event_busy_outlined,
        title: emptyTitle,
        description: emptySubtitle,
      );
    }

    return RefreshIndicator(
      color: AppTheme.primary,
      onRefresh: () async {
        if (user != null) {
          await context.read<AppointmentProvider>().fetchAppointments(
            userId: user.id,
            role: user.role,
          );
        }
      },
      child: ListView.builder(
        padding: const EdgeInsets.all(20),
        itemCount: list.length,
        itemBuilder: (context, index) {
          final apt = list[index];
          return AppointmentCard(
            appointment: apt,
            isCounselorView: true,
            onApprove: () => _showActionDialog(apt.id, 'approve'),
            onDecline: () => _showActionDialog(apt.id, 'decline'),
            onComplete: () async {
              if (user != null) {
                await context.read<AppointmentProvider>().updateStatus(
                  appointmentId: apt.id,
                  action: 'complete',
                  changedBy: user.id,
                );
              }
            },
          );
        },
      ),
    );
  }
}
