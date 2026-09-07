import 'package:intl/intl.dart';

class AvailabilitySlotModel {
  final int id;
  final String startTime; // '09:00:00'
  final String endTime;   // '10:00:00'
  final String status;    // 'available', 'booked', 'blocked'
  final bool isAvailable;

  AvailabilitySlotModel({
    required this.id,
    required this.startTime,
    required this.endTime,
    required this.status,
    required this.isAvailable,
  });

  String get timeRangeLabel {
    try {
      final s = startTime.length >= 5 ? startTime.substring(0, 5) : startTime;
      final e = endTime.length >= 5 ? endTime.substring(0, 5) : endTime;
      
      final sDt = DateFormat('HH:mm').parse(s);
      final eDt = DateFormat('HH:mm').parse(e);
      
      return '${DateFormat('h:mm a').format(sDt)} - ${DateFormat('h:mm a').format(eDt)}';
    } catch (_) {
      return '$startTime - $endTime';
    }
  }

  factory AvailabilitySlotModel.fromJson(Map<String, dynamic> json) {
    final status = json['status']?.toString() ?? 'available';
    return AvailabilitySlotModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      startTime: json['start_time']?.toString() ?? '',
      endTime: json['end_time']?.toString() ?? '',
      status: status,
      isAvailable: json['is_available'] == true || status == 'available',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'start_time': startTime,
      'end_time': endTime,
      'status': status,
      'is_available': isAvailable,
    };
  }
}
