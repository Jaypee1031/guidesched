import 'package:flutter/material.dart';
import '../models/appointment_model.dart';
import '../theme/app_theme.dart';
import 'status_badge.dart';

class AppointmentCard extends StatelessWidget {
  final AppointmentModel appointment;
  final bool isCounselorView;
  final VoidCallback? onApprove;
  final VoidCallback? onDecline;
  final VoidCallback? onComplete;
  final VoidCallback? onCancel;
  final VoidCallback? onDetails;

  const AppointmentCard({
    super.key,
    required this.appointment,
    this.isCounselorView = false,
    this.onApprove,
    this.onDecline,
    this.onComplete,
    this.onCancel,
    this.onDetails,
  });

  @override
  Widget build(BuildContext context) {
    final name = isCounselorView
        ? (appointment.studentName ?? 'Student #${appointment.studentId}')
        : (appointment.counselorName ?? 'Guidance Counselor');

    final subInfo = isCounselorView
        ? (appointment.course ?? 'Student')
        : (appointment.counselorSpecialization ?? 'Guidance Office');

    final isFaceToFace = appointment.consultationMode.toLowerCase().contains('face');

    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: appointment.isPending
              ? AppTheme.statusPending.withValues(alpha: 0.3)
              : AppTheme.borderSubtle,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Row: Name + Status
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 20,
                  backgroundColor: AppTheme.primaryContainer,
                  child: Text(
                    name.isNotEmpty ? name[0].toUpperCase() : 'U',
                    style: const TextStyle(
                      color: AppTheme.primaryDark,
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textDark,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        subInfo,
                        style: const TextStyle(
                          fontSize: 12.5,
                          color: AppTheme.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
                StatusBadge(status: appointment.status),
              ],
            ),
            const SizedBox(height: 14),

            // Date & Time Banner
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: AppTheme.surfaceMint,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppTheme.borderGreen, width: 0.8),
              ),
              child: Row(
                children: [
                  const Icon(Icons.event_outlined, size: 16, color: AppTheme.primary),
                  const SizedBox(width: 6),
                  Text(
                    appointment.formattedDate,
                    style: const TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.primaryDark,
                    ),
                  ),
                  const Spacer(),
                  const Icon(Icons.access_time_rounded, size: 16, color: AppTheme.primary),
                  const SizedBox(width: 6),
                  Text(
                    appointment.formattedTime,
                    style: const TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.primaryDark,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),

            // Mode & Concern
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: isFaceToFace ? Colors.blue.shade50 : Colors.purple.shade50,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isFaceToFace ? Icons.meeting_room_outlined : Icons.videocam_outlined,
                        size: 13,
                        color: isFaceToFace ? Colors.blue.shade700 : Colors.purple.shade700,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        appointment.consultationMode,
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                          color: isFaceToFace ? Colors.blue.shade700 : Colors.purple.shade700,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    appointment.concernTopic,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.textDark,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),

            if (appointment.concernDetails.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                appointment.concernDetails,
                style: const TextStyle(fontSize: 12.5, color: AppTheme.textMedium),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],

            if (appointment.adminNotes != null && appointment.adminNotes!.isNotEmpty) ...[
              const SizedBox(height: 10),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.amber.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.amber.shade200),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.notes_rounded, size: 14, color: Colors.amber.shade800),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        'Counselor Note: ${appointment.adminNotes}',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.amber.shade900,
                          fontStyle: FontStyle.italic,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // Action Buttons
            if (isCounselorView && appointment.isPending) ...[
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: onDecline,
                      icon: const Icon(Icons.close, size: 16, color: AppTheme.statusCancelled),
                      label: const Text('Decline', style: TextStyle(color: AppTheme.statusCancelled)),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: AppTheme.statusCancelled),
                        padding: const EdgeInsets.symmetric(vertical: 8),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: onApprove,
                      icon: const Icon(Icons.check, size: 16),
                      label: const Text('Approve'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primary,
                        padding: const EdgeInsets.symmetric(vertical: 8),
                      ),
                    ),
                  ),
                ],
              ),
            ] else if (isCounselorView && appointment.isApproved) ...[
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: onComplete,
                      icon: const Icon(Icons.done_all, size: 16),
                      label: const Text('Mark Complete'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.statusCompleted,
                        padding: const EdgeInsets.symmetric(vertical: 8),
                      ),
                    ),
                  ),
                ],
              ),
            ] else if (!isCounselorView && appointment.isPending && onCancel != null) ...[
              const SizedBox(height: 12),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton.icon(
                  onPressed: onCancel,
                  icon: const Icon(Icons.cancel_outlined, size: 15, color: AppTheme.statusCancelled),
                  label: const Text('Cancel Booking', style: TextStyle(color: AppTheme.statusCancelled, fontSize: 12.5)),
                  style: TextButton.styleFrom(padding: EdgeInsets.zero),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
