<?php
// Página simplificada apenas com aviso de novo link
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Verdadeiro X-Tudo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            height: 100vh;
            background: url('img/verdadeirox.jpeg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            position: relative;
        }

        /* Camada escura sobre a imagem */
        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
        }

        .aviso-box {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .aviso-box h1 {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .link-app {
            font-size: 22px;
            font-weight: bold;
            color: #ff6b00;
            text-decoration: none;
        }

        .link-app:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="aviso-box">
        <h1>🚀 Novo Link do App!</h1>
        <p class="lead">Agora estamos em um novo endereço:</p>

        <a href="https://lanchup.ct.ws/verdadeirox" target="_blank" class="link-app">
            https://lanchup.ct.ws/verdadeirox
        </a>

        <p class="mt-3">Clique no link acima para acessar o cardápio atualizado.</p>
    </div>

</body>

</html>