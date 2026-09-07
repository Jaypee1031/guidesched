import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../theme/app_theme.dart';

class NotificationModel {
  final int id;
  final int userId;
  final int? appointmentId;
  final String message;
  final String type; // 'approved', 'declined', 'rescheduled', 'reminder', 'info'
  final bool isRead;
  final String? createdAt;

  NotificationModel({
    required this.id,
    required this.userId,
    this.appointmentId,
    required this.message,
    required this.type,
    required this.isRead,
    this.createdAt,
  });

  IconData get icon {
    switch (type) {
      case 'approved':
        return Icons.check_circle_rounded;
      case 'declined':
        return Icons.cancel_rounded;
      case 'rescheduled':
        return Icons.event_repeat_rounded;
      case 'reminder':
        return Icons.notifications_active_rounded;
      default:
        return Icons.info_rounded;
    }
  }

  Color get color {
    switch (type) {
      case 'approved':
        return AppTheme.statusApproved;
      case 'declined':
        return AppTheme.statusDeclined;
      case 'rescheduled':
        return AppTheme.statusRescheduled;
      case 'reminder':
        return AppTheme.statusPending;
      default:
        return AppTheme.primary;
    }
  }

  String get timeAgo {
    if (createdAt == null || createdAt!.isEmpty) return 'Just now';
    try {
      final dt = DateTime.parse(createdAt!);
      final diff = DateTime.now().difference(dt);
      if (diff.inMinutes < 1) return 'Just now';
      if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
      if (diff.inHours < 24) return '${diff.inHours}h ago';
      if (diff.inDays < 7) return '${diff.inDays}d ago';
      return DateFormat('MMM d').format(dt);
    } catch (_) {
      return createdAt!;
    }
  }

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id'].toString()) ?? 0,
      appointmentId: json['appointment_id'] != null ? int.tryParse(json['appointment_id'].toString()) : null,
      message: json['message']?.toString() ?? '',
      type: json['type']?.toString() ?? 'info',
      isRead: json['is_read'] == 1 || json['is_read'] == true || json['is_read'] == '1',
      createdAt: json['created_at']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'appointment_id': appointmentId,
      'message': message,
      'type': type,
      'is_read': isRead,
      'created_at': createdAt,
    };
  }
}
