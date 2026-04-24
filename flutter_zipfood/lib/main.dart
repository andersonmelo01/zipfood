import 'package:flutter/material.dart';
import 'screens/home_screen.dart';
import 'screens/cardapio_screen.dart';
import 'screens/monitoramento_screen.dart';

void main() {
  runApp(const ZipfoodApp());
}

class ZipfoodApp extends StatelessWidget {
  const ZipfoodApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Zipfood',
      theme: ThemeData(
        primarySwatch: Colors.green,
        useMaterial3: true,
      ),
      initialRoute: '/',
      routes: {
        '/': (context) => const HomeScreen(),
        '/cardapio': (context) => const CardapioScreen(),
        '/monitoramento': (context) => const MonitoramentoScreen(),
      },
    );
  }
}
