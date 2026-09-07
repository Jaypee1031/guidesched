import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/analytics_model.dart';
import '../../providers/auth_provider.dart';
import '../../services/analytics_service.dart';
import '../../theme/app_theme.dart';
import '../../widgets/stat_summary_card.dart';

class CounselorAnalyticsScreen extends StatefulWidget {
  const CounselorAnalyticsScreen({super.key});

  @override
  State<CounselorAnalyticsScreen> createState() => _CounselorAnalyticsScreenState();
}

class _CounselorAnalyticsScreenState extends State<CounselorAnalyticsScreen> {
  final AnalyticsService _analyticsService = AnalyticsService();
  CounselorAnalyticsModel? _analytics;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAnalytics();
  }

  void _loadAnalytics() async {
    final user = context.read<AuthProvider>().currentUser;
    if (user != null) {
      final data = await _analyticsService.getCounselorAnalytics(user.id);
      if (mounted) {
        setState(() {
          _analytics = data;
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: AppTheme.primary));
    }

    final data = _analytics!;

    return RefreshIndicator(
      color: AppTheme.primary,
      onRefresh: () async => _loadAnalytics(),
      child: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        children: [
          const Text(
            'Counseling Reports & Analytics',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
          ),
          const SizedBox(height: 4),
          const Text(
            'Comprehensive statistical breakdown of counseling sessions.',
            style: TextStyle(fontSize: 13, color: AppTheme.textMuted),
          ),
          const SizedBox(height: 20),

          // Stat Cards
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 1.25,
            children: [
              StatSummaryCard(
                title: 'Total Appointments',
                value: '${data.totalAppointments}',
                icon: Icons.calendar_month_rounded,
                color: AppTheme.primary,
              ),
              StatSummaryCard(
                title: 'Completed Sessions',
                value: '${data.completedSessions}',
                icon: Icons.check_circle_rounded,
                color: AppTheme.statusCompleted,
              ),
              StatSummaryCard(
                title: 'Pending Requests',
                value: '${data.pendingRequests}',
                icon: Icons.hourglass_empty_rounded,
                color: Colors.amber.shade700,
              ),
              StatSummaryCard(
                title: 'No-Show Rate',
                value: '${data.noShowRate}%',
                icon: Icons.person_off_outlined,
                color: AppTheme.statusCancelled,
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Monthly Appointment Trends
          Card(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(color: AppTheme.borderSubtle),
            ),
            child: Padding(
              padding: const EdgeInsets.all(18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Monthly Appointment Trends',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                      ),
                      Icon(Icons.trending_up, color: AppTheme.primary),
                    ],
                  ),
                  const SizedBox(height: 4),
                  const Text('Total session bookings across school months', style: TextStyle(fontSize: 12, color: AppTheme.textMuted)),
                  const SizedBox(height: 24),

                  SizedBox(
                    height: 140,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: data.monthlyTrends.map((pt) {
                        final maxVal = data.monthlyTrends.map((e) => e.count).reduce((a, b) => a > b ? a : b);
                        final heightFactor = maxVal > 0 ? (pt.count / (maxVal < 10 ? 10 : maxVal)) : 0.05;

                        return Column(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            Text(
                              '${pt.count}',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: pt.count > 0 ? AppTheme.primaryDark : Colors.transparent,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Container(
                              width: 24,
                              height: (100 * heightFactor).clamp(10.0, 100.0),
                              decoration: BoxDecoration(
                                color: AppTheme.primary,
                                borderRadius: BorderRadius.circular(6),
                                gradient: const LinearGradient(
                                  begin: Alignment.bottomCenter,
                                  end: Alignment.topCenter,
                                  colors: [AppTheme.primaryDark, AppTheme.primaryLight],
                                ),
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(pt.month, style: const TextStyle(fontSize: 11.5, color: AppTheme.textMuted)),
                          ],
                        );
                      }).toList(),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Concern Distribution
          Card(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(color: AppTheme.borderSubtle),
            ),
            child: Padding(
              padding: const EdgeInsets.all(18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Common Concern Topics',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                  ),
                  const SizedBox(height: 14),
                  ...data.concernDistribution.entries.map((e) {
                    final total = data.totalAppointments > 0 ? data.totalAppointments : 1;
                    final pct = (e.value / total).clamp(0.0, 1.0);
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(e.key, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                              Text('${e.value} cases', style: const TextStyle(fontSize: 12, color: AppTheme.textMuted)),
                            ],
                          ),
                          const SizedBox(height: 6),
                          ClipRRect(
                            borderRadius: BorderRadius.circular(4),
                            child: LinearProgressIndicator(
                              value: pct,
                              backgroundColor: AppTheme.surfaceMint,
                              color: AppTheme.primary,
                              minHeight: 8,
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
