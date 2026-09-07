import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../theme/app_theme.dart';

class AppointmentModel {
  final int id;
  final int studentId;
  final int counselorId;
  final String appointmentDate; // 'YYYY-MM-DD'
  final String startTime; // 'HH:MM:SS'
  final String endTime; // 'HH:MM:SS'
  final String concern;
  final String status; // 'pending', 'approved', 'declined', 'rescheduled', 'completed', 'cancelled', 'no_show'
  final String? adminNotes;
  final String? createdAt;
  // Joined fields
  final String? counselorName;
  final String? counselorEmail;
  final String? counselorSpecialization;
  final String? studentName;
  final String? studentEmail;
  final String? studentNumber;
  final String? course;
  final int? yearLevel;

  AppointmentModel({
    required this.id,
    required this.studentId,
    required this.counselorId,
    required this.appointmentDate,
    required this.startTime,
    required this.endTime,
    required this.concern,
    required this.status,
    this.adminNotes,
    this.createdAt,
    this.counselorName,
    this.counselorEmail,
    this.counselorSpecialization,
    this.studentName,
    this.studentEmail,
    this.studentNumber,
    this.course,
    this.yearLevel,
  });

  bool get isPending => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isCompleted => status == 'completed';
  bool get isCancelled => status == 'cancelled';
  bool get isDeclined => status == 'declined';
  bool get isRescheduled => status == 'rescheduled';
  bool get isNoShow => status == 'no_show';

  Color get statusColor {
    switch (status) {
      case 'approved':
        return AppTheme.statusApproved;
      case 'completed':
        return AppTheme.statusCompleted;
      case 'pending':
        return AppTheme.statusPending;
      case 'cancelled':
        return AppTheme.statusCancelled;
      case 'declined':
        return AppTheme.statusDeclined;
      case 'rescheduled':
        return AppTheme.statusRescheduled;
      case 'no_show':
        return AppTheme.statusNoShow;
      default:
        return AppTheme.primary;
    }
  }

  Color get statusBgColor {
    switch (status) {
      case 'approved':
        return AppTheme.statusApprovedBg;
      case 'completed':
        return AppTheme.statusCompletedBg;
      case 'pending':
        return AppTheme.statusPendingBg;
      case 'cancelled':
        return AppTheme.statusCancelledBg;
      case 'declined':
        return AppTheme.statusDeclinedBg;
      case 'rescheduled':
        return AppTheme.statusRescheduledBg;
      case 'no_show':
        return AppTheme.statusNoShowBg;
      default:
        return AppTheme.primaryContainer;
    }
  }

  String get displayStatus {
    switch (status) {
      case 'no_show':
        return 'No Show';
      default:
        if (status.isEmpty) return 'Pending';
        return status[0].toUpperCase() + status.substring(1);
    }
  }

  // Parse mode: [Face-to-face] or [Online]
  String get consultationMode {
    final match = RegExp(r'^\[(.*?)\]').firstMatch(concern);
    if (match != null && match.groupCount >= 1) {
      return match.group(1) ?? 'Face-to-face';
    }
    return 'Face-to-face';
  }

  // Parse concern category
  String get concernTopic {
    String clean = concern.replaceFirst(RegExp(r'^\[.*?\]\s*'), '');
    final parts = clean.split(':');
    return parts.first.trim();
  }

  // Parse details note
  String get concernDetails {
    String clean = concern.replaceFirst(RegExp(r'^\[.*?\]\s*'), '');
    final parts = clean.split(':');
    if (parts.length > 1) {
      return parts.sublist(1).join(':').trim();
    }
    return '';
  }

  String get formattedDate {
    try {
      final dt = DateTime.parse(appointmentDate);
      return DateFormat('EEE, MMM d, yyyy').format(dt);
    } catch (_) {
      return appointmentDate;
    }
  }

  String get formattedTime {
    try {
      final s = startTime.length >= 5 ? startTime.substring(0, 5) : startTime;
      final e = endTime.length >= 5 ? endTime.substring(0, 5) : endTime;
      
      final sDt = DateFormat('HH:mm').parse(s);
      final eDt = DateFormat('HH:mm').parse(e);
      
      final sStr = DateFormat('h:mm a').format(sDt);
      final eStr = DateFormat('h:mm a').format(eDt);
      return '$sStr – $eStr';
    } catch (_) {
      return '$startTime – $endTime';
    }
  }

  factory AppointmentModel.fromJson(Map<String, dynamic> json) {
    return AppointmentModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      studentId: json['student_id'] is int ? json['student_id'] : int.tryParse(json['student_id'].toString()) ?? 0,
      counselorId: json['counselor_id'] is int ? json['counselor_id'] : int.tryParse(json['counselor_id'].toString()) ?? 0,
      appointmentDate: json['appointment_date']?.toString() ?? '',
      startTime: json['start_time']?.toString() ?? '',
      endTime: json['end_time']?.toString() ?? '',
      concern: json['concern']?.toString() ?? '',
      status: json['status']?.toString() ?? 'pending',
      adminNotes: json['admin_notes']?.toString(),
      createdAt: json['created_at']?.toString(),
      counselorName: json['counselor_name']?.toString(),
      counselorEmail: json['counselor_email']?.toString(),
      counselorSpecialization: json['specialization']?.toString(),
      studentName: json['student_name']?.toString(),
      studentEmail: json['student_email']?.toString(),
      studentNumber: json['student_number']?.toString(),
      course: json['course']?.toString(),
      yearLevel: json['year_level'] is int ? json['year_level'] : int.tryParse(json['year_level']?.toString() ?? ''),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'student_id': studentId,
      'counselor_id': counselorId,
      'appointment_date': appointmentDate,
      'start_time': startTime,
      'end_time': endTime,
      'concern': concern,
      'status': status,
      'admin_notes': adminNotes,
      'created_at': createdAt,
      'counselor_name': counselorName,
      'counselor_email': counselorEmail,
      'specialization': counselorSpecialization,
      'student_name': studentName,
      'student_email': studentEmail,
      'student_number': studentNumber,
      'course': course,
      'year_level': yearLevel,
    };
  }
}
