class UserModel {
  final int id;
  final String userId;
  final String role; // 'student', 'counselor', 'admin'
  final String name;
  final String email;
  final String status;
  // Student Profile Fields
  final String? studentNumber;
  final String? course;
  final int? yearLevel;
  // Counselor Profile Fields
  final String? specialization;
  final String? contactNumber;
  final String? profilePicture;

  UserModel({
    required this.id,
    required this.userId,
    required this.role,
    required this.name,
    required this.email,
    required this.status,
    this.studentNumber,
    this.course,
    this.yearLevel,
    this.specialization,
    this.contactNumber,
    this.profilePicture,
  });

  bool get isStudent => role == 'student';
  bool get isCounselor => role == 'counselor' || role == 'admin';

  String get initials {
    final parts = name.trim().split(' ');
    if (parts.length > 1 && parts.first.isNotEmpty && parts.last.isNotEmpty) {
      return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
    } else if (parts.isNotEmpty && parts.first.isNotEmpty) {
      return parts.first[0].toUpperCase();
    }
    return 'U';
  }

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      userId: json['user_id']?.toString() ?? '',
      role: json['role']?.toString() ?? 'student',
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      status: json['status']?.toString() ?? 'active',
      studentNumber: json['student_number']?.toString(),
      course: json['course']?.toString(),
      yearLevel: json['year_level'] is int ? json['year_level'] : int.tryParse(json['year_level']?.toString() ?? ''),
      specialization: json['specialization']?.toString(),
      contactNumber: json['contact_number']?.toString(),
      profilePicture: json['profile_picture']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'role': role,
      'name': name,
      'email': email,
      'status': status,
      'student_number': studentNumber,
      'course': course,
      'year_level': yearLevel,
      'specialization': specialization,
      'contact_number': contactNumber,
      'profile_picture': profilePicture,
    };
  }

  UserModel copyWith({
    String? name,
    String? email,
    String? contactNumber,
    String? studentNumber,
    String? course,
    int? yearLevel,
    String? specialization,
  }) {
    return UserModel(
      id: id,
      userId: userId,
      role: role,
      name: name ?? this.name,
      email: email ?? this.email,
      status: status,
      studentNumber: studentNumber ?? this.studentNumber,
      course: course ?? this.course,
      yearLevel: yearLevel ?? this.yearLevel,
      specialization: specialization ?? this.specialization,
      contactNumber: contactNumber ?? this.contactNumber,
      profilePicture: profilePicture,
    );
  }
}
