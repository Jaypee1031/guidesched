import 'package:flutter/material.dart';
import '../models/availability_slot_model.dart';
import '../theme/app_theme.dart';

class TimeSlotChip extends StatelessWidget {
  final AvailabilitySlotModel slot;
  final bool isSelected;
  final VoidCallback? onTap;

  const TimeSlotChip({
    super.key,
    required this.slot,
    required this.isSelected,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final available = slot.isAvailable;

    Color bg;
    Color border;
    Color textColor;

    if (!available) {
      bg = Colors.grey.shade100;
      border = Colors.grey.shade300;
      textColor = Colors.grey.shade400;
    } else if (isSelected) {
      bg = AppTheme.primary;
      border = AppTheme.primaryDark;
      textColor = Colors.white;
    } else {
      bg = AppTheme.surfaceMint;
      border = AppTheme.borderGreen;
      textColor = AppTheme.primaryDark;
    }

    return InkWell(
      onTap: available ? onTap : null,
      borderRadius: BorderRadius.circular(10),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: border, width: isSelected ? 1.5 : 1),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppTheme.primary.withValues(alpha: 0.25),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  )
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              available
                  ? (isSelected ? Icons.check_circle : Icons.schedule)
                  : Icons.do_not_disturb_on,
              size: 14,
              color: textColor,
            ),
            const SizedBox(width: 6),
            Text(
              slot.timeRangeLabel,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
                color: textColor,
                decoration: available ? null : TextDecoration.lineThrough,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
