import 'package:flutter_test/flutter_test.dart';
import 'package:guidesched_app/main.dart';

void main() {
  testWidgets('GuideSched app smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const GuideSchedApp());
    expect(find.byType(GuideSchedApp), findsOneWidget);
  });
}
