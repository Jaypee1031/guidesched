import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/analytics_model.dart';
import '../../providers/auth_provider.dart';
import '../../services/analytics_service.dart';
import '../../theme/app_theme.dart';
import '../../widgets/stat_summary_card.dart';

class StudentInsightsScreen extends StatefulWidget {
  const StudentInsightsScreen({super.key});

  @override
  State<StudentInsightsScreen> createState() => _StudentInsightsScreenState();
}

class _StudentInsightsScreenState extends State<StudentInsightsScreen> {
  final AnalyticsService _analyticsService = AnalyticsService();
  StudentAnalyticsModel? _analytics;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAnalytics();
  }

  void _loadAnalytics() async {
    final user = context.read<AuthProvider>().currentUser;
    if (user != null) {
      final data = await _analyticsService.getStudentAnalytics(user.id);
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
            'My Insights & Analytics',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
          ),
          const SizedBox(height: 4),
          const Text(
            'Track your counseling journey and personal growth over time.',
            style: TextStyle(fontSize: 13, color: AppTheme.textMuted),
          ),
          const SizedBox(height: 20),

          // Stat Cards Grid
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 1.25,
            children: [
              StatSummaryCard(
                title: 'Sessions Attended',
                value: '${data.completedSessions}',
                icon: Icons.task_alt_rounded,
                color: AppTheme.primary,
                subtitle: 'Completed',
              ),
              StatSummaryCard(
                title: 'Monthly Streak',
                value: '${data.consistencyStreakMonths} Mos',
                icon: Icons.local_fire_department_rounded,
                color: Colors.orange.shade700,
                subtitle: 'Active',
              ),
              StatSummaryCard(
                title: 'Top Concern',
                value: data.topConcern,
                icon: Icons.psychology_outlined,
                color: Colors.teal.shade700,
              ),
              StatSummaryCard(
                title: 'Upcoming Sessions',
                value: '${data.upcomingSessions}',
                icon: Icons.calendar_today_rounded,
                color: Colors.blue.shade700,
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Monthly Sessions Bar Chart Card
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
                        'Monthly Counseling Volume',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                      ),
                      Icon(Icons.bar_chart_rounded, color: AppTheme.primary),
                    ],
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Sessions completed throughout the school year',
                    style: TextStyle(fontSize: 12, color: AppTheme.textMuted),
                  ),
                  const SizedBox(height: 24),

                  // Custom visual bar columns
                  SizedBox(
                    height: 140,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: data.monthlyChart.map((pt) {
                        final maxVal = data.monthlyChart.map((e) => e.count).reduce((a, b) => a > b ? a : b);
                        final heightFactor = maxVal > 0 ? (pt.count / (maxVal < 4 ? 4 : maxVal)) : 0.05;

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
                              width: 22,
                              height: (100 * heightFactor).clamp(8.0, 100.0),
                              decoration: BoxDecoration(
                                color: pt.count > 0 ? AppTheme.primary : Colors.grey.shade200,
                                borderRadius: BorderRadius.circular(6),
                                gradient: pt.count > 0
                                    ? const LinearGradient(
                                        begin: Alignment.bottomCenter,
                                        end: Alignment.topCenter,
                                        colors: [AppTheme.primary, AppTheme.primaryLight],
                                      )
                                    : null,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              pt.month,
                              style: const TextStyle(fontSize: 11, color: AppTheme.textMuted),
                            ),
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

          // Concern Distribution Breakdown
          if (data.concernBreakdown.isNotEmpty) ...[
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
                      'Consultation Topics Distribution',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                    ),
                    const SizedBox(height: 14),
                    ...data.concernBreakdown.entries.map((e) {
                      final total = data.completedSessions > 0 ? data.completedSessions : 1;
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
                                Text('${e.value} sessions', style: const TextStyle(fontSize: 12, color: AppTheme.textMuted)),
                              ],
                            ),
                            const SizedBox(height: 6),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(4),
                              child: LinearProgressIndicator(
                                value: pct,
                                backgroundColor: AppTheme.surfaceMint,
                                color: AppTheme.primary,
                                minHeight: 7,
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
        ],
      ),
    );
  }
}
