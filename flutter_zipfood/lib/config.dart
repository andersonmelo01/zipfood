/// Configurações globais do app Zipfood
/// Edite este arquivo para apontar para a URL correta da sua API PHP

class AppConfig {
  /// Exemplo: 'http://localhost/delivery' ou 'https://seudominio.com/delivery'
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://192.168.15.4/delivery',
  );
}
