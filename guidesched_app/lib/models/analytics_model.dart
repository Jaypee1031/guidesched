class MonthlyDataPoint {
  final String month;
  final int count;

  MonthlyDataPoint({required this.month, required this.count});

  factory MonthlyDataPoint.fromJson(Map<String, dynamic> json) {
    return MonthlyDataPoint(
      month: json['month']?.toString() ?? '',
      count: json['count'] is int ? json['count'] : int.tryParse(json['count'].toString()) ?? 0,
    );
  }
}

class StudentAnalyticsModel {
  final int totalSessions;
  final int completedSessions;
  final int upcomingSessions;
  final String topConcern;
  final int consistencyStreakMonths;
  final List<MonthlyDataPoint> monthlyChart;
  final Map<String, int> concernBreakdown;

  StudentAnalyticsModel({
    required this.totalSessions,
    required this.completedSessions,
    required this.upcomingSessions,
    required this.topConcern,
    required this.consistencyStreakMonths,
    required this.monthlyChart,
    required this.concernBreakdown,
  });

  factory StudentAnalyticsModel.fromJson(Map<String, dynamic> json) {
    final chartRaw = json['monthly_chart'] as List<dynamic>? ?? [];
    final chart = chartRaw.map((e) => MonthlyDataPoint.fromJson(e as Map<String, dynamic>)).toList();

    final breakdownRaw = json['concern_breakdown'] as Map<String, dynamic>? ?? {};
    final breakdown = breakdownRaw.map((k, v) => MapEntry(k, v is int ? v : int.tryParse(v.toString()) ?? 0));

    return StudentAnalyticsModel(
      totalSessions: json['total_sessions'] is int ? json['total_sessions'] : int.tryParse(json['total_sessions'].toString()) ?? 0,
      completedSessions: json['completed_sessions'] is int ? json['completed_sessions'] : int.tryParse(json['completed_sessions'].toString()) ?? 0,
      upcomingSessions: json['upcoming_sessions'] is int ? json['upcoming_sessions'] : int.tryParse(json['upcoming_sessions'].toString()) ?? 0,
      topConcern: json['top_concern']?.toString() ?? 'None',
      consistencyStreakMonths: json['consistency_streak_months'] is int ? json['consistency_streak_months'] : int.tryParse(json['consistency_streak_months'].toString()) ?? 1,
      monthlyChart: chart,
      concernBreakdown: breakdown,
    );
  }
}

class CounselorAnalyticsModel {
  final int todayAppointments;
  final int pendingRequests;
  final int approvedSessions;
  final int completedSessions;
  final int totalAppointments;
  final double noShowRate;
  final List<MonthlyDataPoint> monthlyTrends;
  final Map<String, int> concernDistribution;
  final Map<String, int> statusSummary;

  CounselorAnalyticsModel({
    required this.todayAppointments,
    required this.pendingRequests,
    required this.approvedSessions,
    required this.completedSessions,
    required this.totalAppointments,
    required this.noShowRate,
    required this.monthlyTrends,
    required this.concernDistribution,
    required this.statusSummary,
  });

  factory CounselorAnalyticsModel.fromJson(Map<String, dynamic> json) {
    final trendsRaw = json['monthly_trends'] as List<dynamic>? ?? [];
    final trends = trendsRaw.map((e) => MonthlyDataPoint.fromJson(e as Map<String, dynamic>)).toList();

    final concernsRaw = json['concern_distribution'] as Map<String, dynamic>? ?? {};
    final concerns = concernsRaw.map((k, v) => MapEntry(k, v is int ? v : int.tryParse(v.toString()) ?? 0));

    final statusRaw = json['status_summary'] as Map<String, dynamic>? ?? {};
    final statusMap = statusRaw.map((k, v) => MapEntry(k, v is int ? v : int.tryParse(v.toString()) ?? 0));

    return CounselorAnalyticsModel(
      todayAppointments: json['today_appointments'] is int ? json['today_appointments'] : int.tryParse(json['today_appointments'].toString()) ?? 0,
      pendingRequests: json['pending_requests'] is int ? json['pending_requests'] : int.tryParse(json['pending_requests'].toString()) ?? 0,
      approvedSessions: json['approved_sessions'] is int ? json['approved_sessions'] : int.tryParse(json['approved_sessions'].toString()) ?? 0,
      completedSessions: json['completed_sessions'] is int ? json['completed_sessions'] : int.tryParse(json['completed_sessions'].toString()) ?? 0,
      totalAppointments: json['total_appointments'] is int ? json['total_appointments'] : int.tryParse(json['total_appointments'].toString()) ?? 0,
      noShowRate: json['no_show_rate'] is num ? (json['no_show_rate'] as num).toDouble() : double.tryParse(json['no_show_rate'].toString()) ?? 0.0,
      monthlyTrends: trends,
      concernDistribution: concerns,
      statusSummary: statusMap,
    );
  }
}
