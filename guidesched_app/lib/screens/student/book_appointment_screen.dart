import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../models/availability_slot_model.dart';
import '../../models/user_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/appointment_provider.dart';
import '../../theme/app_theme.dart';
import '../../widgets/time_slot_chip.dart';

class BookAppointmentScreen extends StatefulWidget {
  final VoidCallback onBookingSuccess;

  const BookAppointmentScreen({super.key, required this.onBookingSuccess});

  @override
  State<BookAppointmentScreen> createState() => _BookAppointmentScreenState();
}

class _BookAppointmentScreenState extends State<BookAppointmentScreen> {
  UserModel? _selectedCounselor;
  DateTime _selectedDate = DateTime.now().add(const Duration(days: 1));
  AvailabilitySlotModel? _selectedSlot;
  String _selectedMode = 'Face-to-face'; // 'Face-to-face' or 'Online'
  String _selectedCategory = 'Academic stress';
  final _detailsController = TextEditingController();
  bool _isSubmitting = false;

  final List<String> _categories = [
    'Academic stress',
    'Anxiety',
    'Family concerns',
    'Peer relationships',
    'Career & Strand guidance',
    'Personal growth',
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadCounselorsAndSlots();
    });
  }

  @override
  void dispose() {
    _detailsController.dispose();
    super.dispose();
  }

  void _loadCounselorsAndSlots() async {
    final aptProvider = context.read<AppointmentProvider>();
    if (aptProvider.counselors.isEmpty) {
      await aptProvider.fetchCounselors();
    }
    if (aptProvider.counselors.isNotEmpty && _selectedCounselor == null) {
      setState(() {
        _selectedCounselor = aptProvider.counselors.first;
      });
    }
    _fetchSlots();
  }

  void _fetchSlots() {
    if (_selectedCounselor == null) return;
    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
    context.read<AppointmentProvider>().fetchAvailability(
      counselorId: _selectedCounselor!.id,
      date: dateStr,
    );
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate.isBefore(now) ? now.add(const Duration(days: 1)) : _selectedDate,
      firstDate: now,
      lastDate: now.add(const Duration(days: 60)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppTheme.primary,
              onPrimary: Colors.white,
              surface: Colors.white,
              onSurface: AppTheme.textDark,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _selectedDate = picked;
        _selectedSlot = null; // Reset selected slot
      });
      _fetchSlots();
    }
  }

  void _confirmAndSubmit() {
    if (_selectedCounselor == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a guidance counselor')),
      );
      return;
    }
    if (_selectedSlot == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select an available time slot')),
      );
      return;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 24,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Confirm Booking Details',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
              ),
              const SizedBox(height: 16),
              _buildReviewRow('Counselor', _selectedCounselor!.name),
              _buildReviewRow('Date', DateFormat('EEEE, MMMM d, yyyy').format(_selectedDate)),
              _buildReviewRow('Time Slot', _selectedSlot!.timeRangeLabel),
              _buildReviewRow('Consultation Mode', _selectedMode),
              _buildReviewRow('Primary Concern', _selectedCategory),
              if (_detailsController.text.isNotEmpty)
                _buildReviewRow('Notes', _detailsController.text.trim()),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isSubmitting
                    ? null
                    : () async {
                        Navigator.pop(ctx);
                        _submitBooking();
                      },
                child: const Text('Confirm & Submit Request', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildReviewRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(label, style: const TextStyle(fontSize: 13, color: AppTheme.textMuted)),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: AppTheme.textDark),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _submitBooking() async {
    final user = context.read<AuthProvider>().currentUser;
    if (user == null || _selectedCounselor == null || _selectedSlot == null) return;

    setState(() => _isSubmitting = true);
    final aptProvider = context.read<AppointmentProvider>();

    final success = await aptProvider.bookAppointment(
      studentId: user.id,
      counselorId: _selectedCounselor!.id,
      date: DateFormat('yyyy-MM-dd').format(_selectedDate),
      startTime: _selectedSlot!.startTime,
      endTime: _selectedSlot!.endTime,
      mode: _selectedMode,
      concernCategory: _selectedCategory,
      details: _detailsController.text.trim(),
    );

    setState(() => _isSubmitting = false);

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Appointment booked successfully! Pending counselor confirmation.'),
          backgroundColor: AppTheme.primary,
        ),
      );
      widget.onBookingSuccess();
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(aptProvider.errorMessage ?? 'Failed to book appointment'),
          backgroundColor: AppTheme.statusCancelled,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final aptProvider = context.watch<AppointmentProvider>();
    final counselors = aptProvider.counselors;
    final slots = aptProvider.availabilitySlots;

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Book an Appointment',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
          ),
          const SizedBox(height: 4),
          const Text(
            'Schedule a confidential session with your high school guidance counselor.',
            style: TextStyle(fontSize: 13, color: AppTheme.textMuted),
          ),
          const SizedBox(height: 20),

          // 1. Counselor Selection
          const Text(
            '1. Select Guidance Counselor',
            style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: AppTheme.textDark),
          ),
          const SizedBox(height: 10),
          if (counselors.isEmpty)
            const Padding(
              padding: EdgeInsets.all(12),
              child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
            )
          else
            ...counselors.map((c) {
              final isSelected = _selectedCounselor?.id == c.id;
              return GestureDetector(
                onTap: () {
                  setState(() {
                    _selectedCounselor = c;
                    _selectedSlot = null;
                  });
                  _fetchSlots();
                },
                child: Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isSelected ? AppTheme.surfaceMint : Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: isSelected ? AppTheme.primary : AppTheme.borderSubtle,
                      width: isSelected ? 1.8 : 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      CircleAvatar(
                        backgroundColor: isSelected ? AppTheme.primary : AppTheme.primaryContainer,
                        child: Text(
                          c.name[0],
                          style: TextStyle(
                            color: isSelected ? Colors.white : AppTheme.primaryDark,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              c.name,
                              style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700, color: AppTheme.textDark),
                            ),
                            Text(
                              c.specialization ?? 'Guidance Counselor',
                              style: const TextStyle(fontSize: 12, color: AppTheme.textMuted),
                            ),
                          ],
                        ),
                      ),
                      if (isSelected)
                        const Icon(Icons.check_circle, color: AppTheme.primary, size: 22),
                    ],
                  ),
                ),
              );
            }),
          const SizedBox(height: 20),

          // 2. Date Selection
          const Text(
            '2. Select Appointment Date',
            style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: AppTheme.textDark),
          ),
          const SizedBox(height: 10),
          InkWell(
            onTap: _pickDate,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppTheme.borderSubtle),
              ),
              child: Row(
                children: [
                  const Icon(Icons.calendar_month_outlined, color: AppTheme.primary),
                  const SizedBox(width: 12),
                  Text(
                    DateFormat('EEEE, MMMM d, yyyy').format(_selectedDate),
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppTheme.textDark),
                  ),
                  const Spacer(),
                  const Text('Change', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppTheme.primary)),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),

          // 3. Time Slot Grid
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                '3. Choose Time Slot',
                style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: AppTheme.textDark),
              ),
              if (aptProvider.isSlotsLoading)
                const SizedBox(
                  width: 16,
                  height: 16,
                  child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primary),
                ),
            ],
          ),
          const SizedBox(height: 10),
          if (aptProvider.isSlotsLoading)
            const Padding(
              padding: EdgeInsets.all(20),
              child: Center(child: Text('Loading availability...', style: TextStyle(color: AppTheme.textMuted))),
            )
          else if (slots.isEmpty)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.amber.shade50,
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Text(
                'No availability slots configured for this date. Please pick another date.',
                style: TextStyle(fontSize: 13, color: Colors.brown),
              ),
            )
          else
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: slots.map((slot) {
                return TimeSlotChip(
                  slot: slot,
                  isSelected: _selectedSlot?.id == slot.id,
                  onTap: () {
                    setState(() => _selectedSlot = slot);
                  },
                );
              }).toList(),
            ),
          const SizedBox(height: 24),

          // 4. Consultation Mode (Face-to-face or Online)
          const Text(
            '4. Consultation Mode',
            style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: AppTheme.textDark),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: ChoiceChip(
                  label: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.meeting_room_outlined, size: 16),
                      SizedBox(width: 6),
                      Text('Face-to-face'),
                    ],
                  ),
                  selected: _selectedMode == 'Face-to-face',
                  selectedColor: AppTheme.primaryContainer,
                  onSelected: (val) {
                    if (val) setState(() => _selectedMode = 'Face-to-face');
                  },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ChoiceChip(
                  label: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.videocam_outlined, size: 16),
                      SizedBox(width: 6),
                      Text('Online (Google Meet)'),
                    ],
                  ),
                  selected: _selectedMode == 'Online',
                  selectedColor: AppTheme.primaryContainer,
                  onSelected: (val) {
                    if (val) setState(() => _selectedMode = 'Online');
                  },
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // 5. Concern Category
          const Text(
            '5. Primary Concern Category',
            style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: AppTheme.textDark),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _categories.map((cat) {
              final isCatSelected = _selectedCategory == cat;
              return FilterChip(
                label: Text(cat),
                selected: isCatSelected,
                selectedColor: AppTheme.primaryContainer,
                checkmarkColor: AppTheme.primaryDark,
                labelStyle: TextStyle(
                  color: isCatSelected ? AppTheme.primaryDark : AppTheme.textDark,
                  fontWeight: isCatSelected ? FontWeight.bold : FontWeight.normal,
                  fontSize: 12.5,
                ),
                onSelected: (selected) {
                  if (selected) setState(() => _selectedCategory = cat);
                },
              );
            }).toList(),
          ),
          const SizedBox(height: 20),

          // 6. Additional Details
          const Text(
            '6. Additional Notes or Details (Optional)',
            style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: AppTheme.textDark),
          ),
          const SizedBox(height: 8),
          TextFormField(
            controller: _detailsController,
            maxLines: 3,
            decoration: const InputDecoration(
              hintText: 'Describe what you would like to discuss with the counselor...',
            ),
          ),
          const SizedBox(height: 28),

          // Submit Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _confirmAndSubmit,
              child: const Text('Review & Book Appointment', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            ),
          ),
          const SizedBox(height: 30),
        ],
      ),
    );
  }
}
