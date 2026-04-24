import 'package:flutter/material.dart';
import '../services/api_service.dart';

class CardapioScreen extends StatefulWidget {
  const CardapioScreen({super.key});

  @override
  State<CardapioScreen> createState() => _CardapioScreenState();
}

class _CardapioScreenState extends State<CardapioScreen> {
  late Future<List<dynamic>> _cardapioFuture;

  @override
  void initState() {
    super.initState();
    _cardapioFuture = ApiService.fetchCardapio();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Cardápio'),
      ),
      body: Column(
        children: [
          Expanded(
            child: FutureBuilder<List<dynamic>>(
              future: _cardapioFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                } else if (snapshot.hasError) {
                  return Center(child: Text('Erro ao carregar cardápio'));
                } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
                  return const Center(child: Text('Nenhum item no cardápio'));
                }
                final cardapio = snapshot.data!;
                return ListView.builder(
                  itemCount: cardapio.length,
                  itemBuilder: (context, index) {
                    final item = cardapio[index];
                    return ListTile(
                      title: Text(item['nome'] ?? ''),
                      subtitle: Text(item['descricao'] ?? ''),
                      trailing: Text('R\$ ${item['preco']}'),
                    );
                  },
                );
              },
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Voltar'),
                ),
                ElevatedButton(
                  onPressed: () => Navigator.pushNamed(context, '/monitoramento'),
                  child: const Text('Monitoramento'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
