import 'package:flutter/material.dart';
import '../../models/user_model.dart';
import '../../services/mock_data_service.dart';
import '../../theme/app_theme.dart';

class CounselorStudentsScreen extends StatefulWidget {
  const CounselorStudentsScreen({super.key});

  @override
  State<CounselorStudentsScreen> createState() => _CounselorStudentsScreenState();
}

class _CounselorStudentsScreenState extends State<CounselorStudentsScreen> {
  final MockDataService _mock = MockDataService();
  List<UserModel> _students = [];
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _students = _mock.getStudentsDirectory();
  }

  void _showStudentDetails(UserModel student) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 28,
                  backgroundColor: AppTheme.primary,
                  child: Text(
                    student.initials,
                    style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        student.name,
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.textDark),
                      ),
                      Text(
                        'LRN: ${student.studentNumber ?? "N/A"}',
                        style: const TextStyle(fontSize: 13, color: AppTheme.primary, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            const Divider(),
            const SizedBox(height: 12),
            _infoRow('Academic Strand', student.course ?? 'High School'),
            _infoRow('Year Level', student.yearLevel != null ? 'Grade ${student.yearLevel}' : 'Grade 11'),
            _infoRow('Email Address', student.email),
            _infoRow('Contact Number', student.contactNumber ?? 'Not provided'),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Close'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: AppTheme.textMuted, fontSize: 13)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13.5, color: AppTheme.textDark)),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _students.where((s) {
      if (_searchQuery.isEmpty) return true;
      final t = '${s.name} ${s.studentNumber} ${s.course} ${s.email}'.toLowerCase();
      return t.contains(_searchQuery);
    }).toList();

    return Scaffold(
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Students Directory',
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.primaryDark),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Browse and view student profiles and counseling participation.',
                  style: TextStyle(fontSize: 13, color: AppTheme.textMuted),
                ),
                const SizedBox(height: 12),
                TextField(
                  onChanged: (v) => setState(() => _searchQuery = v.toLowerCase()),
                  decoration: const InputDecoration(
                    hintText: 'Search by student name, LRN, or strand...',
                    prefixIcon: Icon(Icons.search, color: AppTheme.primary),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView.separated(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
              itemCount: filtered.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (context, index) {
                final student = filtered[index];
                return Card(
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                    side: const BorderSide(color: AppTheme.borderSubtle),
                  ),
                  child: ListTile(
                    onTap: () => _showStudentDetails(student),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    leading: CircleAvatar(
                      backgroundColor: AppTheme.primaryContainer,
                      child: Text(
                        student.initials,
                        style: const TextStyle(color: AppTheme.primaryDark, fontWeight: FontWeight.bold),
                      ),
                    ),
                    title: Text(
                      student.name,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5, color: AppTheme.textDark),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 2),
                        Text(
                          student.course ?? 'Student',
                          style: const TextStyle(fontSize: 12, color: AppTheme.textMuted),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'LRN: ${student.studentNumber ?? student.userId}',
                          style: const TextStyle(fontSize: 11.5, color: AppTheme.primary, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                    trailing: const Icon(Icons.chevron_right, color: AppTheme.textMuted),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
