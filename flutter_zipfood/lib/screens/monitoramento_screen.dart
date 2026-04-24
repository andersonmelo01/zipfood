import 'package:flutter/material.dart';
import '../services/api_service.dart';

class MonitoramentoScreen extends StatefulWidget {
  const MonitoramentoScreen({super.key});

  @override
  State<MonitoramentoScreen> createState() => _MonitoramentoScreenState();
}

class _MonitoramentoScreenState extends State<MonitoramentoScreen> {
  late Future<List<dynamic>> _pedidosFuture;

  @override
  void initState() {
    super.initState();
    _pedidosFuture = ApiService.fetchPedidos();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Monitoramento de Pedidos'),
      ),
      body: Column(
        children: [
          Expanded(
            child: FutureBuilder<List<dynamic>>(
              future: _pedidosFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                } else if (snapshot.hasError) {
                  return Center(child: Text('Erro ao carregar pedidos'));
                } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
                  return const Center(child: Text('Nenhum pedido encontrado'));
                }
                final pedidos = snapshot.data!;
                return ListView.builder(
                  itemCount: pedidos.length,
                  itemBuilder: (context, index) {
                    final pedido = pedidos[index];
                    return ListTile(
                      title: Text('Pedido #${pedido['id']}'),
                      subtitle: Text('Cliente: ${pedido['cliente']}'),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('Status: ${pedido['status']}'),
                          Text('R\$ ${pedido['valor']}'),
                        ],
                      ),
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
                  onPressed: () => Navigator.pushNamed(context, '/cardapio'),
                  child: const Text('Cardápio'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
