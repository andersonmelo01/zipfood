import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config.dart';

class ApiService {
  static String get baseUrl => AppConfig.apiBaseUrl;

  static Future<List<dynamic>> fetchCardapio() async {
    final response = await http.get(Uri.parse('$baseUrl/listar_cardapio.php'));
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao carregar cardápio');
    }
  }

  static Future<List<dynamic>> fetchPedidos() async {
    final response = await http.get(Uri.parse('$baseUrl/listar_pedidos.php'));
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Erro ao carregar pedidos');
    }
  }
}
