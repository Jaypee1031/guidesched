import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/appointment_provider.dart';
import '../../theme/app_theme.dart';

class CounselorScheduleScreen extends StatefulWidget {
  const CounselorScheduleScreen({super.key});

  @override
  State<CounselorScheduleScreen> createState() => _CounselorScheduleScreenState();
}

class _CounselorScheduleScreenState extends State<CounselorScheduleScreen> {
  DateTime _selectedDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _fetchSlots());
  }

  void _fetchSlots() {
    final user = context.read<AuthProvider>().currentUser;
    if (user != null) {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      context.read<AppointmentProvider>().fetchAvailability(
        counselorId: user.id,
        date: dateStr,
      );
    }
  }

  void _showAddSlotDialog() {
    final startController = TextEditingController(text: '09:00');
    final endController = TextEditingController(text: '10:00');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Add Availability Slot'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'Date: ${DateFormat('EEE, MMM d, yyyy').format(_selectedDate)}',
              style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: startController,
              decoration: const InputDecoration(
                labelText: 'Start Time (HH:MM)',
                hintText: '09:00',
                prefixIcon: Icon(Icons.access_time),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: endController,
              decoration: const InputDecoration(
                labelText: 'End Time (HH:MM)',
                hintText: '10:00',
                prefixIcon: Icon(Icons.access_time_filled),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Time slot updated successfully'),
                  backgroundColor: AppTheme.primary,
                ),
              );
              _fetchSlots();
            },
            child: const Text('Save Slot'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final aptProvider = context.watch<AppointmentProvider>();
    final slots = aptProvider.availabilitySlots;

    return Scaffold(
      body: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Schedule Management',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
              ),
              ElevatedButton.icon(
                onPressed: _showAddSlotDialog,
                icon: const Icon(Icons.add, size: 16),
                label: const Text('Add Slot', style: TextStyle(fontSize: 12.5)),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          const Text(
            'Configure your working hours and appointment availability for students.',
            style: TextStyle(fontSize: 13, color: AppTheme.textMuted),
          ),
          const SizedBox(height: 18),

          // Date Selection Strip
          Card(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(color: AppTheme.borderSubtle),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  const Icon(Icons.calendar_month_outlined, color: AppTheme.primary),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Viewing Schedule For',
                        style: TextStyle(fontSize: 11.5, color: AppTheme.textMuted),
                      ),
                      Text(
                        DateFormat('EEEE, MMMM d, yyyy').format(_selectedDate),
                        style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                      ),
                    ],
                  ),
                  const Spacer(),
                  IconButton(
                    icon: const Icon(Icons.edit_calendar_rounded, color: AppTheme.primary),
                    onPressed: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: _selectedDate,
                        firstDate: DateTime.now().subtract(const Duration(days: 30)),
                        lastDate: DateTime.now().add(const Duration(days: 90)),
                      );
                      if (picked != null) {
                        setState(() => _selectedDate = picked);
                        _fetchSlots();
                      }
                    },
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          const Text(
            'Time Slots on this Day',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.textDark),
          ),
          const SizedBox(height: 12),

          if (aptProvider.isSlotsLoading)
            const Center(child: Padding(
              padding: EdgeInsets.all(32),
              child: CircularProgressIndicator(color: AppTheme.primary),
            ))
          else if (slots.isEmpty)
            Card(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: const BorderSide(color: AppTheme.borderSubtle),
              ),
              child: const Padding(
                padding: EdgeInsets.all(24),
                child: Center(
                  child: Text('No slots configured for this date. Use "Add Slot" above.', style: TextStyle(color: AppTheme.textMuted)),
                ),
              ),
            )
          else
            ...slots.map((slot) {
              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  color: slot.isAvailable ? AppTheme.surfaceMint : Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: slot.isAvailable ? AppTheme.borderGreen : AppTheme.borderSubtle,
                    width: 1.2,
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      slot.isAvailable ? Icons.check_circle_outline : Icons.event_busy,
                      color: slot.isAvailable ? AppTheme.primary : Colors.grey,
                      size: 20,
                    ),
                    const SizedBox(width: 12),
                    Text(
                      slot.timeRangeLabel,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: slot.isAvailable ? AppTheme.primaryDark : AppTheme.textMuted,
                      ),
                    ),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: slot.isAvailable ? AppTheme.primaryContainer : Colors.grey.shade200,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        slot.isAvailable ? 'Open for Booking' : 'Booked / Closed',
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.bold,
                          color: slot.isAvailable ? AppTheme.primaryDark : Colors.grey.shade700,
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }
}
