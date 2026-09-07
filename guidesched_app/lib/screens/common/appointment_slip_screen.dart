import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../models/appointment_model.dart';
import '../../services/sound_service.dart';
import '../../theme/app_theme.dart';
import '../../utils/print_helper.dart';

class AppointmentSlipScreen extends StatelessWidget {
  final AppointmentModel appointment;

  const AppointmentSlipScreen({super.key, required this.appointment});

  String get refCode => 'GS-CNHS-${appointment.id.toString().padLeft(5, '0')}';

  void _handlePrint(BuildContext context) {
    SoundService.playActionFeedback();
    if (kIsWeb) {
      printDocument();
    } else {
      _copySlipSummary(context);
    }
  }

  void _copySlipSummary(BuildContext context) {
    final text = '''
=============================================
CAGASAT NATIONAL HIGH SCHOOL
Guidance & Counseling Office
Official Appointment & Consultation Pass
=============================================
Reference Code : $refCode
Date & Time    : ${appointment.formattedDate} | ${appointment.formattedTime}
Mode           : ${appointment.consultationMode}
Status         : ${appointment.displayStatus.toUpperCase()}

STUDENT INFORMATION:
Name           : ${appointment.studentName ?? 'Student'}
Student ID/LRN : ${appointment.studentNumber ?? 'N/A'}
Grade & Section: ${appointment.course ?? 'N/A'}

SESSION DETAILS:
Counselor      : ${appointment.counselorName ?? 'Guidance Counselor'} (${appointment.counselorSpecialization ?? 'Guidance Office'})
Concern Category: ${appointment.concernTopic}
Details        : ${appointment.concernDetails.isNotEmpty ? appointment.concernDetails : 'None provided'}
Counselor Remarks: ${appointment.adminNotes ?? 'No notes recorded yet'}

* Please present this pass at the Guidance Office upon arrival.
=============================================
''';

    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Appointment Pass details copied to clipboard!'),
        backgroundColor: AppTheme.primary,
        duration: Duration(seconds: 3),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final studentName = appointment.studentName ?? 'Student';
    final studentNo = appointment.studentNumber ?? 'N/A';
    final course = appointment.course ?? 'Junior / Senior High';
    final counselorName = appointment.counselorName ?? 'Guidance Counselor';
    final counselorSpec = appointment.counselorSpecialization ?? 'Guidance Specialist';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Official Appointment Pass'),
        actions: [
          IconButton(
            icon: const Icon(Icons.copy_rounded),
            tooltip: 'Copy Pass Details',
            onPressed: () => _copySlipSummary(context),
          ),
          IconButton(
            icon: const Icon(Icons.print_rounded),
            tooltip: 'Print / Save as PDF',
            onPressed: () => _handlePrint(context),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 680),
            child: Column(
              children: [
                // The Official Printable Slip Card
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 16,
                        offset: const Offset(0, 4),
                      ),
                    ],
                    border: Border.all(color: AppTheme.primary.withValues(alpha: 0.3), width: 1.5),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Header Ribbon
                      Container(
                        padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 24),
                        decoration: const BoxDecoration(
                          color: AppTheme.primaryDark,
                          borderRadius: BorderRadius.vertical(top: Radius.circular(14)),
                        ),
                        child: Column(
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.15),
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(Icons.school_rounded, color: Colors.white, size: 26),
                                ),
                                const SizedBox(width: 12),
                                const Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'CAGASAT NATIONAL HIGH SCHOOL',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                        letterSpacing: 0.8,
                                      ),
                                    ),
                                    Text(
                                      'Guidance & Counseling Department',
                                      style: TextStyle(
                                        color: AppTheme.primaryLight,
                                        fontSize: 12,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            const SizedBox(height: 14),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                              decoration: BoxDecoration(
                                color: AppTheme.primary,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: const Text(
                                'OFFICIAL APPOINTMENT PASS',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.2,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),

                      Padding(
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Reference and Status Row
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('PASS REFERENCE NO.', style: TextStyle(fontSize: 10, color: AppTheme.textMuted, fontWeight: FontWeight.bold)),
                                    Text(refCode, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.primaryDark)),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: appointment.statusBgColor,
                                    borderRadius: BorderRadius.circular(20),
                                    border: Border.all(color: appointment.statusColor.withValues(alpha: 0.4)),
                                  ),
                                  child: Text(
                                    appointment.displayStatus.toUpperCase(),
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                      color: appointment.statusColor,
                                    ),
                                  ),
                                ),
                              ],
                            ),

                            const Divider(height: 32, thickness: 1),

                            // Student Information Section
                            const Text(
                              'STUDENT INFORMATION',
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 0.8,
                                color: AppTheme.primary,
                              ),
                            ),
                            const SizedBox(height: 12),
                            _buildInfoGrid([
                              _InfoItem('Student Name', studentName),
                              _InfoItem('Student LRN / ID', studentNo),
                              _InfoItem('Grade & Section', course),
                              _InfoItem('Contact / Email', appointment.studentEmail ?? 'On File'),
                            ]),

                            const Divider(height: 32, thickness: 1),

                            // Consultation Details Section
                            const Text(
                              'CONSULTATION SCHEDULE & DETAILS',
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 0.8,
                                color: AppTheme.primary,
                              ),
                            ),
                            const SizedBox(height: 12),
                            _buildInfoGrid([
                              _InfoItem('Appointment Date', appointment.formattedDate),
                              _InfoItem('Scheduled Time', appointment.formattedTime),
                              _InfoItem('Assigned Counselor', counselorName),
                              _InfoItem('Designation', counselorSpec),
                              _InfoItem('Consultation Mode', appointment.consultationMode),
                              _InfoItem('Concern Category', appointment.concernTopic),
                            ]),

                            if (appointment.concernDetails.isNotEmpty) ...[
                              const SizedBox(height: 12),
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: AppTheme.surfaceMint,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: AppTheme.borderGreen),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Student Note / Background Context:',
                                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      appointment.concernDetails,
                                      style: const TextStyle(fontSize: 12.5, color: AppTheme.textDark),
                                    ),
                                  ],
                                ),
                              ),
                            ],

                            // Counselor Remarks
                            if (appointment.adminNotes != null && appointment.adminNotes!.isNotEmpty) ...[
                              const SizedBox(height: 16),
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Colors.amber.shade50,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: Colors.amber.shade300),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Icon(Icons.assignment_turned_in_outlined, size: 15, color: Colors.amber.shade900),
                                        const SizedBox(width: 6),
                                        Text(
                                          'Official Counselor Remarks / Instructions:',
                                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.amber.shade900),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      appointment.adminNotes!,
                                      style: TextStyle(fontSize: 12.5, color: Colors.amber.shade900),
                                    ),
                                  ],
                                ),
                              ),
                            ],

                            const SizedBox(height: 32),

                            // Signatures
                            Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    children: [
                                      const Divider(thickness: 1, color: Colors.black45),
                                      Text(
                                        studentName,
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                      ),
                                      const Text('Student Signature', style: TextStyle(fontSize: 10, color: AppTheme.textMuted)),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 40),
                                Expanded(
                                  child: Column(
                                    children: [
                                      const Divider(thickness: 1, color: Colors.black45),
                                      Text(
                                        counselorName,
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                      ),
                                      const Text('Guidance Counselor Signature', style: TextStyle(fontSize: 10, color: AppTheme.textMuted)),
                                    ],
                                  ),
                                ),
                              ],
                            ),

                            const SizedBox(height: 24),
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: Colors.grey.shade100,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Row(
                                children: [
                                  Icon(Icons.info_outline, size: 15, color: AppTheme.textMuted),
                                  SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'Notice: This pass is recognized for excused room attendance during the scheduled guidance session. Confidentiality is protected under the Guidance and Counseling Act of 2004 (RA 9258).',
                                      style: TextStyle(fontSize: 9.5, color: AppTheme.textMuted),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Action Buttons below card
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _copySlipSummary(context),
                        icon: const Icon(Icons.copy_rounded, size: 18),
                        label: const Text('Copy Details'),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          side: const BorderSide(color: AppTheme.primary),
                          foregroundColor: AppTheme.primary,
                        ),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _handlePrint(context),
                        icon: const Icon(Icons.print_rounded, size: 18),
                        label: const Text('Print / Save PDF'),
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          backgroundColor: AppTheme.primary,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoGrid(List<_InfoItem> items) {
    return Wrap(
      spacing: 20,
      runSpacing: 14,
      children: items.map((item) {
        return SizedBox(
          width: 280,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                item.label.toUpperCase(),
                style: const TextStyle(fontSize: 10, color: AppTheme.textMuted, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 3),
              Text(
                item.value,
                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, color: AppTheme.textDark),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}

class _InfoItem {
  final String label;
  final String value;
  _InfoItem(this.label, this.value);
}
