import 'package:flutter/material.dart';
import '../models/appointment_model.dart';
import '../screens/common/appointment_slip_screen.dart';
import '../services/sound_service.dart';
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
  final VoidCallback? onEditNotes;

  const AppointmentCard({
    super.key,
    required this.appointment,
    this.isCounselorView = false,
    this.onApprove,
    this.onDecline,
    this.onComplete,
    this.onCancel,
    this.onDetails,
    this.onEditNotes,
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

            // Counselor Notes Section
            if (appointment.adminNotes != null && appointment.adminNotes!.isNotEmpty) ...[
              const SizedBox(height: 10),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.amber.shade50,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.amber.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.notes_rounded, size: 15, color: Colors.amber.shade900),
                        const SizedBox(width: 6),
                        Text(
                          'Counselor Remarks:',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.bold,
                            color: Colors.amber.shade900,
                          ),
                        ),
                        const Spacer(),
                        if (isCounselorView && onEditNotes != null)
                          InkWell(
                            onTap: () {
                              SoundService.playActionFeedback();
                              onEditNotes!();
                            },
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                              child: Row(
                                children: [
                                  Icon(Icons.edit_outlined, size: 13, color: Colors.amber.shade900),
                                  const SizedBox(width: 3),
                                  Text(
                                    'Edit',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.amber.shade900,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      appointment.adminNotes!,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.amber.shade900,
                      ),
                    ),
                  ],
                ),
              ),
            ] else if (isCounselorView && onEditNotes != null) ...[
              const SizedBox(height: 8),
              Align(
                alignment: Alignment.centerLeft,
                child: TextButton.icon(
                  onPressed: () {
                    SoundService.playActionFeedback();
                    onEditNotes!();
                  },
                  icon: const Icon(Icons.add_comment_outlined, size: 14, color: AppTheme.primary),
                  label: const Text('Add Counselor Note', style: TextStyle(fontSize: 11.5, color: AppTheme.primary)),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                ),
              ),
            ],

            const SizedBox(height: 12),

            // Secondary Quick Actions (View Slip)
            Row(
              children: [
                OutlinedButton.icon(
                  onPressed: () {
                    SoundService.playActionFeedback();
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => AppointmentSlipScreen(appointment: appointment),
                      ),
                    );
                  },
                  icon: const Icon(Icons.description_outlined, size: 14, color: AppTheme.primary),
                  label: const Text(
                    'Official Pass / Slip',
                    style: TextStyle(fontSize: 11.5, color: AppTheme.primary, fontWeight: FontWeight.bold),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: BorderSide(color: AppTheme.primary.withValues(alpha: 0.5)),
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                ),
                const Spacer(),
                if (!isCounselorView && appointment.isPending && onCancel != null)
                  TextButton.icon(
                    onPressed: () {
                      SoundService.playAlertSound();
                      onCancel!();
                    },
                    icon: const Icon(Icons.cancel_outlined, size: 14, color: AppTheme.statusCancelled),
                    label: const Text('Cancel Booking', style: TextStyle(color: AppTheme.statusCancelled, fontSize: 11.5)),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      minimumSize: Size.zero,
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                  ),
              ],
            ),

            // Main Primary Action Buttons
            if (isCounselorView && appointment.isPending) ...[
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () {
                        SoundService.playAlertSound();
                        onDecline?.call();
                      },
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
                      onPressed: () {
                        SoundService.playSuccessChime();
                        onApprove?.call();
                      },
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
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () {
                        SoundService.playSuccessChime();
                        onComplete?.call();
                      },
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
            ],
          ],
        ),
      ),
    );
  }
}
