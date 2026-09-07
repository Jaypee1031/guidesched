import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/appointment_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/appointment_provider.dart';
import '../../services/sound_service.dart';
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
  String _statusFilter = 'all';

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

  void _showEditNotesDialog(AppointmentModel apt) {
    final noteController = TextEditingController(text: apt.adminNotes ?? '');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.edit_note_rounded, color: AppTheme.primary, size: 24),
            SizedBox(width: 8),
            Text('Counselor Remarks', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceMint,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppTheme.borderGreen),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Student: ${apt.studentName ?? "Student #${apt.studentId}"}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppTheme.primaryDark),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${apt.formattedDate} • ${apt.formattedTime} • Mode: ${apt.consultationMode}',
                      style: const TextStyle(fontSize: 11.5, color: AppTheme.textMuted),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              const Text(
                'Clinical & Guidance Notes:',
                style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: AppTheme.textDark),
              ),
              const SizedBox(height: 6),
              TextField(
                controller: noteController,
                maxLines: 4,
                decoration: const InputDecoration(
                  hintText: 'Enter session notes, recommended follow-ups, or instructions for the student...',
                ),
              ),
              const SizedBox(height: 10),
              const Text('Quick templates:', style: TextStyle(fontSize: 11, color: AppTheme.textMuted)),
              const SizedBox(height: 4),
              Wrap(
                spacing: 6,
                runSpacing: 4,
                children: [
                  _buildQuickChip('Session completed; follow-up scheduled.', noteController),
                  _buildQuickChip('Academic consultation completed.', noteController),
                  _buildQuickChip('Referral to subject teacher advised.', noteController),
                ],
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton.icon(
            onPressed: () async {
              final user = context.read<AuthProvider>().currentUser;
              if (user != null) {
                Navigator.pop(ctx);
                final aptProvider = context.read<AppointmentProvider>();
                final ok = await aptProvider.updateCounselorNotes(
                  appointmentId: apt.id,
                  notes: noteController.text.trim(),
                  changedBy: user.id,
                );
                if (ok) {
                  SoundService.playSuccessChime();
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Counselor remarks updated successfully!'),
                        backgroundColor: AppTheme.primary,
                      ),
                    );
                  }
                }
              }
            },
            icon: const Icon(Icons.check, size: 16),
            label: const Text('Save Remarks'),
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickChip(String text, TextEditingController controller) {
    return InkWell(
      onTap: () {
        if (controller.text.isEmpty) {
          controller.text = text;
        } else {
          controller.text = '${controller.text.trim()} $text';
        }
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: Colors.grey.shade100,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: Text(text, style: const TextStyle(fontSize: 10.5, color: AppTheme.textDark)),
      ),
    );
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
                if (isApprove) {
                  SoundService.playSuccessChime();
                } else {
                  SoundService.playAlertSound();
                }
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
                _buildAllSessionsTab(aptProvider, user),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAllSessionsTab(AppointmentProvider aptProvider, dynamic user) {
    List<AppointmentModel> all = aptProvider.appointments.where((a) => _matchesSearch(a)).toList();
    if (_statusFilter != 'all') {
      all = all.where((a) => a.status == _statusFilter).toList();
    }

    return Column(
      children: [
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
          child: Row(
            children: [
              _buildFilterChip('All', 'all'),
              const SizedBox(width: 8),
              _buildFilterChip('Approved', 'approved'),
              const SizedBox(width: 8),
              _buildFilterChip('Completed', 'completed'),
              const SizedBox(width: 8),
              _buildFilterChip('Pending', 'pending'),
              const SizedBox(width: 8),
              _buildFilterChip('Cancelled', 'cancelled'),
            ],
          ),
        ),
        Expanded(
          child: _buildList(
            all,
            'No appointments found',
            'There are no appointment records matching your criteria.',
            user,
          ),
        ),
      ],
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _statusFilter == value;
    return FilterChip(
      selected: isSelected,
      label: Text(label),
      labelStyle: TextStyle(
        fontSize: 11.5,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        color: isSelected ? Colors.white : AppTheme.textDark,
      ),
      selectedColor: AppTheme.primary,
      backgroundColor: Colors.grey.shade100,
      showCheckmark: false,
      padding: const EdgeInsets.symmetric(horizontal: 4),
      onSelected: (selected) {
        SoundService.playActionFeedback();
        setState(() {
          _statusFilter = value;
        });
      },
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
            onEditNotes: () => _showEditNotesDialog(apt),
            onComplete: () async {
              if (user != null) {
                SoundService.playSuccessChime();
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
