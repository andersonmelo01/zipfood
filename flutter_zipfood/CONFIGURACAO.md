# Configuração do App Zipfood Flutter

Este guia explica como configurar o app Flutter para funcionar em qualquer ambiente (local, VPS, hospedagem compartilhada, etc).

## 1. Edite o arquivo de configuração

Abra o arquivo:

    lib/config.dart

E altere o valor de `apiBaseUrl` para o endereço onde sua API PHP está hospedada. Exemplos:

- Para rodar localmente:
  ```dart
  static const String apiBaseUrl = 'http://localhost/delivery';
  ```
- Para rodar em um servidor remoto:
  ```dart
  static const String apiBaseUrl = 'https://seudominio.com/delivery';
  ```

## 2. Gere o APK ou execute o app

- Para testar no emulador ou dispositivo:
  ```sh
  flutter run
  ```
- Para gerar o APK de produção:
  ```sh
  flutter build apk --release
  ```

O app irá consumir a API PHP no endereço configurado.

## 3. Dicas
- Certifique-se que a API PHP está acessível pelo endereço configurado.
- Se mudar o servidor, basta ajustar o valor em `config.dart`.
- Não exponha informações sensíveis neste arquivo.

---

Dúvidas? Consulte o README ou entre em contato com o desenvolvedor responsável.
