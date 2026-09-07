import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class StatusBadge extends StatelessWidget {
  final String status;
  final bool compact;

  const StatusBadge({
    super.key,
    required this.status,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color fg;
    IconData icon;
    String label;

    switch (status.toLowerCase()) {
      case 'approved':
        bg = AppTheme.statusApprovedBg;
        fg = AppTheme.statusApproved;
        icon = Icons.check_circle_rounded;
        label = 'Approved';
        break;
      case 'completed':
        bg = AppTheme.statusCompletedBg;
        fg = AppTheme.statusCompleted;
        icon = Icons.task_alt_rounded;
        label = 'Completed';
        break;
      case 'pending':
        bg = AppTheme.statusPendingBg;
        fg = AppTheme.statusPending;
        icon = Icons.schedule_rounded;
        label = 'Pending';
        break;
      case 'cancelled':
        bg = AppTheme.statusCancelledBg;
        fg = AppTheme.statusCancelled;
        icon = Icons.cancel_outlined;
        label = 'Cancelled';
        break;
      case 'declined':
        bg = AppTheme.statusDeclinedBg;
        fg = AppTheme.statusDeclined;
        icon = Icons.block_rounded;
        label = 'Declined';
        break;
      case 'rescheduled':
        bg = AppTheme.statusRescheduledBg;
        fg = AppTheme.statusRescheduled;
        icon = Icons.update_rounded;
        label = 'Rescheduled';
        break;
      case 'no_show':
        bg = AppTheme.statusNoShowBg;
        fg = AppTheme.statusNoShow;
        icon = Icons.person_off_outlined;
        label = 'No Show';
        break;
      default:
        bg = AppTheme.primaryContainer;
        fg = AppTheme.primaryDark;
        icon = Icons.info_outline;
        label = status;
    }

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: compact ? 8 : 12,
        vertical: compact ? 4 : 6,
      ),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: fg.withValues(alpha: 0.3), width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: compact ? 12 : 14, color: fg),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: compact ? 11 : 12.5,
              fontWeight: FontWeight.w700,
              color: fg,
            ),
          ),
        ],
      ),
    );
  }
}
