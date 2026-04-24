import 'package:flutter/material.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Zipfood - Cardápio Digital'),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            ElevatedButton(
              child: const Text('Ver Cardápio'),
              onPressed: () {
                Navigator.pushNamed(context, '/cardapio');
              },
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              child: const Text('Monitoramento de Pedidos'),
              onPressed: () {
                Navigator.pushNamed(context, '/monitoramento');
              },
            ),
          ],
        ),
      ),
    );
  }
}
