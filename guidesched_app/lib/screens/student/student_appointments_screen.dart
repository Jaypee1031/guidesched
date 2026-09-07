import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/appointment_provider.dart';
import '../../theme/app_theme.dart';
import '../../widgets/appointment_card.dart';
import '../../widgets/empty_state_view.dart';

class StudentAppointmentsScreen extends StatefulWidget {
  const StudentAppointmentsScreen({super.key});

  @override
  State<StudentAppointmentsScreen> createState() => _StudentAppointmentsScreenState();
}

class _StudentAppointmentsScreenState extends State<StudentAppointmentsScreen> with SingleTickerProviderStateMixin {
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

  Future<void> _cancelAppointment(int appointmentId) async {
    final reasonController = TextEditingController();
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Appointment?'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Are you sure you want to cancel this booking? This will free up the slot for other students.'),
            const SizedBox(height: 12),
            TextField(
              controller: reasonController,
              decoration: const InputDecoration(
                labelText: 'Reason for cancellation (optional)',
                hintText: 'e.g. Schedule conflict with exams',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Keep')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.statusCancelled),
            child: const Text('Cancel Booking'),
          ),
        ],
      ),
    );

    if (confirm == true && mounted) {
      final user = context.read<AuthProvider>().currentUser;
      if (user != null) {
        await context.read<AppointmentProvider>().updateStatus(
          appointmentId: appointmentId,
          action: 'cancel',
          changedBy: user.id,
          adminNotes: reasonController.text.trim().isNotEmpty ? 'Student note: ${reasonController.text.trim()}' : null,
        );
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Appointment cancelled'), backgroundColor: AppTheme.statusCancelled),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().currentUser;
    final aptProvider = context.watch<AppointmentProvider>();

    return Scaffold(
      body: Column(
        children: [
          // Header & Search
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'My Sessions',
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
                ),
                const SizedBox(height: 10),
                TextField(
                  onChanged: (val) => setState(() => _searchQuery = val.toLowerCase()),
                  decoration: InputDecoration(
                    hintText: 'Search by counselor, date, or topic...',
                    prefixIcon: const Icon(Icons.search, color: AppTheme.primary),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    fillColor: Colors.grey.shade50,
                  ),
                ),
              ],
            ),
          ),

          // Tabs
          TabBar(
            controller: _tabController,
            indicatorColor: AppTheme.primary,
            indicatorWeight: 3,
            labelColor: AppTheme.primary,
            unselectedLabelColor: AppTheme.textMuted,
            labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5),
            tabs: const [
              Tab(text: 'Upcoming'),
              Tab(text: 'Past Sessions'),
              Tab(text: 'All Sessions'),
            ],
          ),

          // Tab Views
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildList(
                  aptProvider.upcomingAppointments.where((a) => _matchesSearch(a)).toList(),
                  'No upcoming appointments',
                  'Book a session when you need assistance.',
                  user,
                ),
                _buildList(
                  aptProvider.pastAppointments.where((a) => _matchesSearch(a)).toList(),
                  'No past sessions yet',
                  'Your completed and past sessions will appear here.',
                  user,
                ),
                _buildList(
                  aptProvider.appointments.where((a) => _matchesSearch(a)).toList(),
                  'No appointments found',
                  'You have not booked any appointments yet.',
                  user,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  bool _matchesSearch(dynamic apt) {
    if (_searchQuery.isEmpty) return true;
    final text = '${apt.counselorName} ${apt.appointmentDate} ${apt.concern} ${apt.status}'.toLowerCase();
    return text.contains(_searchQuery);
  }

  Widget _buildList(List<dynamic> list, String emptyTitle, String emptySubtitle, dynamic user) {
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
            role: 'student',
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
            isCounselorView: false,
            onCancel: apt.isPending ? () => _cancelAppointment(apt.id) : null,
          );
        },
      ),
    );
  }
}
