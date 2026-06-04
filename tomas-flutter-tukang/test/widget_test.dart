import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:tomas_flutter_tukang/main.dart';

void main() {
  testWidgets('app shell renders', (WidgetTester tester) async {
    await tester.pumpWidget(const TomasTukangApp());
    await tester.pump();

    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
