<?php
// Página pública para visualizar avaliações e permitir envio de nova avaliação
// As avaliações só ficam visíveis após aprovação do administrador

require_once __DIR__ . '/conexao.php';

// Carrega avaliações aprovadas do banco
$avaliacoes = [];
$stmt = $pdo->query("SELECT * FROM feedbacks WHERE aprovado = 1 ORDER BY data DESC");
while ($fb = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $avaliacoes[] = $fb;
}

// Processa novo envio
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');
    $estrelas = (int) ($_POST['estrelas'] ?? 5);
    if ($nome && $comentario && $estrelas >= 1 && $estrelas <= 5) {
        $id = uniqid('fb_', true);
        $sql = "INSERT INTO feedbacks (id, nome, comentario, estrelas, aprovado, data) VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, htmlspecialchars($nome), htmlspecialchars($comentario), $estrelas]);
        echo '<script>alert("Avaliação enviada! Ela será exibida após aprovação.");window.location.href="index.php";</script>';
        exit;
    } else {
        $mensagem = 'Preencha todos os campos corretamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Avaliações dos Clientes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <style>
        body { background: #fff8f2; }
        .feedback-card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(255,107,0,0.10);
            margin-bottom: 2rem;
            padding: 1.5rem 1.2rem;
            transition: box-shadow 0.2s;
        }
        .feedback-card:active, .feedback-card:focus-within {
            box-shadow: 0 12px 36px rgba(255,107,0,0.18);
        }
        .feedback-stars {
            color: #ffb300;
            font-size: 1.6em;
            letter-spacing: 0.05em;
            line-height: 1;
        }
        .feedback-nome {
            font-family: 'Playfair Display', serif;
            font-weight: bold;
            color: #ff6b00;
            font-size: 1.1em;
            margin-left: 0.5em;
        }
        .feedback-comentario {
            font-style: italic;
            color: #7c4700;
            font-size: 1.15em;
            margin-top: 0.5em;
            margin-bottom: 0.2em;
            word-break: break-word;
        }
        .feedback-form label { font-weight: 600; }
        @media (max-width: 600px) {
            .feedback-card {
                padding: 1.1rem 0.7rem;
                border-radius: 16px;
            }
            .feedback-stars { font-size: 1.3em; }
            .feedback-nome { font-size: 1em; }
            .feedback-comentario { font-size: 1em; }
        }
        #feedbacks-carousel {
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }
        #feedbacks-list {
            min-height: 320px;
        }
        #prevFeedbacks, #nextFeedbacks {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            opacity: 0.92;
            transition: background 0.15s, box-shadow 0.15s;
        }
        #prevFeedbacks:active, #nextFeedbacks:active {
            background: #ffe3d1;
            box-shadow: 0 4px 16px rgba(255,107,0,0.10);
        }
    </style>
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4 text-center" style="font-family:'Playfair Display',serif;color:#ff6b00;">Avaliações dos Clientes</h1>
    <?php if ($mensagem): ?>
        <div class="alert alert-info text-center"> <?= $mensagem ?> </div>
    <?php endif; ?>
    <div class="row justify-content-center mb-5">
        <div class="col-lg-7">
            <form method="post" class="feedback-form p-4 bg-light rounded-4 shadow-sm">
                <h4 class="mb-3">Deixe sua avaliação</h4>
                <div class="mb-3">
                    <label for="nome" class="form-label">Seu nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" maxlength="32" required>
                </div>
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentário</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3" maxlength="200" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nota</label>
                    <select class="form-select w-auto d-inline-block" name="estrelas" required>
                        <option value="5">5 estrelas</option>
                        <option value="4">4 estrelas</option>
                        <option value="3">3 estrelas</option>
                        <option value="2">2 estrelas</option>
                        <option value="1">1 estrela</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-warning px-4">Enviar avaliação</button>
                <div class="form-text mt-2">Sua avaliação será exibida após aprovação do administrador.</div>
            </form>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h4 class="mb-4">Avaliações aprovadas</h4>
            <?php if (empty($avaliacoes)): ?>
                <div class="alert alert-secondary text-center">Nenhuma avaliação aprovada ainda.</div>
            <?php else: ?>
                <div id="feedbacks-carousel" class="position-relative">
                    <?php 
                    $total = count($avaliacoes);
                    $perPage = 5;
                    $pages = ceil($total / $perPage);
                    ?>
                    <div id="feedbacks-list">
                        <!-- Avaliações serão renderizadas via JS -->
                    </div>
                    <?php if ($pages > 1): ?>
                        <div class="d-flex justify-content-center gap-3 mt-3">
                            <button id="prevFeedbacks" class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i></button>
                            <button id="nextFeedbacks" class="btn btn-outline-secondary"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    <?php endif; ?>
                </div>
                <script>
                const avaliacoes = <?= json_encode($avaliacoes, JSON_UNESCAPED_UNICODE) ?>;
                let feedbackPage = 0;
                const perPage = 5;
                function renderFeedbacks() {
                    const start = feedbackPage * perPage;
                    const end = start + perPage;
                    const list = document.getElementById('feedbacks-list');
                    list.innerHTML = '';
                    for (let i = start; i < end && i < avaliacoes.length; i++) {
                        const fb = avaliacoes[i];
                        let estrelas = '';
                        for (let j = 0; j < (parseInt(fb.estrelas || fb.nota || 0)); j++) estrelas += '<i class="bi bi-star-fill"></i>';
                        list.innerHTML += `
                            <div class="feedback-card mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="feedback-stars">${estrelas}</span>
                                    <span class="ms-2 feedback-nome">${fb.nome ? fb.nome.replace(/</g,'&lt;') : 'Cliente'}</span>
                                </div>
                                <div class="feedback-comentario">“${(fb.comentario || fb.mensagem || '').replace(/</g,'&lt;')}”</div>
                            </div>
                        `;
                    }
                }
                function updateButtons() {
                    const prev = document.getElementById('prevFeedbacks');
                    const next = document.getElementById('nextFeedbacks');
                    if (prev) prev.disabled = feedbackPage === 0;
                    if (next) next.disabled = (feedbackPage + 1) * perPage >= avaliacoes.length;
                }
                document.addEventListener('DOMContentLoaded', () => {
                    renderFeedbacks();
                    updateButtons();
                    const prev = document.getElementById('prevFeedbacks');
                    const next = document.getElementById('nextFeedbacks');
                    if (prev) prev.addEventListener('click', () => {
                        if (feedbackPage > 0) feedbackPage--;
                        renderFeedbacks();
                        updateButtons();
                    });
                    if (next) next.addEventListener('click', () => {
                        if ((feedbackPage + 1) * perPage < avaliacoes.length) feedbackPage++;
                        renderFeedbacks();
                        updateButtons();
                    });
                });
                </script>
            <?php endif; ?>
        </div>
    </div>
    <div class="text-center mt-5">
        <a href="index.php" class="btn btn-outline-secondary">Voltar ao cardápio</a>
    </div>
</div>
</body>
</html>
