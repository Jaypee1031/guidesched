import 'package:flutter/services.dart';

class SoundService {
  static final SoundService _instance = SoundService._internal();
  factory SoundService() => _instance;
  SoundService._internal();

  /// Play a pleasant notification / success chime
  static Future<void> playSuccessChime() async {
    try {
      await HapticFeedback.mediumImpact();
      await SystemSound.play(SystemSoundType.click);
    } catch (_) {
      // Gracefully ignore if audio subsystem is unavailable
    }
  }

  /// Play an action / click feedback sound
  static Future<void> playActionFeedback() async {
    try {
      await HapticFeedback.lightImpact();
      await SystemSound.play(SystemSoundType.click);
    } catch (_) {
      // Gracefully ignore
    }
  }

  /// Play a gentle alert / decline sound
  static Future<void> playAlertSound() async {
    try {
      await HapticFeedback.heavyImpact();
      await SystemSound.play(SystemSoundType.alert);
    } catch (_) {
      // Gracefully ignore
    }
  }
}
