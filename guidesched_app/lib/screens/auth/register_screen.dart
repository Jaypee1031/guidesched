import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../theme/app_theme.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _studentNumberController = TextEditingController();
  final _emailController = TextEditingController();
  final _contactController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  String _selectedGradeStrand = 'Grade 11 - STEM';
  int _selectedYearLevel = 11;
  bool _obscurePassword = true;

  final List<Map<String, dynamic>> _strands = [
    {'label': 'Grade 7 - Junior High', 'year': 7},
    {'label': 'Grade 8 - Junior High', 'year': 8},
    {'label': 'Grade 9 - Junior High', 'year': 9},
    {'label': 'Grade 10 - Junior High', 'year': 10},
    {'label': 'Grade 11 - STEM', 'year': 11},
    {'label': 'Grade 11 - ABM', 'year': 11},
    {'label': 'Grade 11 - HUMSS', 'year': 11},
    {'label': 'Grade 11 - TVL', 'year': 11},
    {'label': 'Grade 12 - STEM', 'year': 12},
    {'label': 'Grade 12 - ABM', 'year': 12},
    {'label': 'Grade 12 - HUMSS', 'year': 12},
    {'label': 'Grade 12 - TVL', 'year': 12},
  ];

  @override
  void dispose() {
    _nameController.dispose();
    _studentNumberController.dispose();
    _emailController.dispose();
    _contactController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    final authProvider = context.read<AuthProvider>();
    final success = await authProvider.register(
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      password: _passwordController.text,
      studentNumber: _studentNumberController.text.trim(),
      course: _selectedGradeStrand,
      yearLevel: _selectedYearLevel,
      contactNumber: _contactController.text.trim(),
    );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Account registered successfully! Welcome to GuideSched.'),
          backgroundColor: AppTheme.primary,
        ),
      );
      Navigator.pop(context);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage ?? 'Registration failed'),
          backgroundColor: AppTheme.statusCancelled,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Student Registration'),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 500),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Text(
                      'Create Your Student Account',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Connect with Cagasat High School Guidance Office anytime.',
                      style: TextStyle(fontSize: 13.5, color: AppTheme.textMuted),
                    ),
                    const SizedBox(height: 24),

                    // Full Name
                    TextFormField(
                      controller: _nameController,
                      decoration: const InputDecoration(
                        labelText: 'Full Name',
                        hintText: 'e.g. Juan C. Santos',
                        prefixIcon: Icon(Icons.person_outline, color: AppTheme.primary),
                      ),
                      validator: (val) => (val == null || val.trim().isEmpty) ? 'Please enter your name' : null,
                    ),
                    const SizedBox(height: 16),

                    // Student Number & Contact Row
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: _studentNumberController,
                            decoration: const InputDecoration(
                              labelText: 'LRN / Student No.',
                              hintText: 'e.g. 123456789012',
                              prefixIcon: Icon(Icons.badge_outlined, color: AppTheme.primary),
                            ),
                            validator: (val) => (val == null || val.trim().isEmpty) ? 'Required' : null,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: TextFormField(
                            controller: _contactController,
                            keyboardType: TextInputType.phone,
                            decoration: const InputDecoration(
                              labelText: 'Contact Number',
                              hintText: '09123456789',
                              prefixIcon: Icon(Icons.phone_outlined, color: AppTheme.primary),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Grade & Strand Dropdown
                    DropdownButtonFormField<String>(
                      initialValue: _selectedGradeStrand,
                      decoration: const InputDecoration(
                        labelText: 'Grade Level & Strand',
                        prefixIcon: Icon(Icons.school_outlined, color: AppTheme.primary),
                      ),
                      items: _strands.map((s) {
                        return DropdownMenuItem<String>(
                          value: s['label'] as String,
                          child: Text(s['label'] as String, style: const TextStyle(fontSize: 13.5)),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) {
                          setState(() {
                            _selectedGradeStrand = val;
                            final match = _strands.firstWhere((s) => s['label'] == val);
                            _selectedYearLevel = match['year'] as int;
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 16),

                    // Email Address
                    TextFormField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      decoration: const InputDecoration(
                        labelText: 'Email Address',
                        hintText: 'juan@cagasaths.edu.ph',
                        prefixIcon: Icon(Icons.email_outlined, color: AppTheme.primary),
                      ),
                      validator: (val) {
                        if (val == null || val.trim().isEmpty) return 'Please enter your email';
                        if (!val.contains('@')) return 'Please enter a valid email';
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    // Password
                    TextFormField(
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      decoration: InputDecoration(
                        labelText: 'Password',
                        prefixIcon: const Icon(Icons.lock_outline, color: AppTheme.primary),
                        suffixIcon: IconButton(
                          icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility),
                          onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                        ),
                      ),
                      validator: (val) {
                        if (val == null || val.length < 6) {
                          return 'Password must be at least 6 characters';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    // Confirm Password
                    TextFormField(
                      controller: _confirmPasswordController,
                      obscureText: _obscurePassword,
                      decoration: const InputDecoration(
                        labelText: 'Confirm Password',
                        prefixIcon: Icon(Icons.lock_reset_outlined, color: AppTheme.primary),
                      ),
                      validator: (val) {
                        if (val != _passwordController.text) {
                          return 'Passwords do not match';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 24),

                    // Submit
                    ElevatedButton(
                      onPressed: auth.isLoading ? null : _handleRegister,
                      child: auth.isLoading
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                            )
                          : const Text('Create Account', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(height: 16),

                    Center(
                      child: TextButton(
                        onPressed: () => Navigator.pop(context),
                        child: const Text('Already registered? Back to Login'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
