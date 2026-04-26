<?php

require_once __DIR__ . '/conexao.php';
require_module_access('pedidos');

// Buscar produtos cadastrados
$stmtProd = $pdo->query("SELECT id, nome, preco FROM produtos ORDER BY nome ASC");
$produtos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

// Datas vindas do formulário (sem hora)
$dataInicioForm = $_GET['data_inicio'] ?? date('Y-m-d');
$dataFimForm    = $_GET['data_fim'] ?? date('Y-m-d');

// Datas para consulta (com hora)
$dataInicio = $dataInicioForm . " 00:00:00";
$dataFim    = $dataFimForm . " 23:59:59";

// Buscar pedidos do banco
$sql = "SELECT * FROM pedidos 
        WHERE data_pedido BETWEEN ? AND ?
        ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$dataInicio, $dataFim]);

$pedidosFiltrados = [];



while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    // Buscar itens do pedido (CORRETO usando PDO)
    $sqlItens = "SELECT produto_nome AS nome, preco, quantidade 
                    FROM pedido_itens 
                    WHERE pedido_id = ?";
    $stmtItens = $pdo->prepare($sqlItens);
    $stmtItens->execute([$row['id']]);

    $row['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    $pedidosFiltrados[] = $row;
}

function corStatus($status)
{
    switch ($status) {
        case 'Preparando':
            return 'bg-warning text-dark';
        case 'Saiu':
            return 'bg-info text-dark';
        case 'Entregue':
            return 'bg-success';
        default:
            return 'bg-secondary';
    }
}

function corLinhaStatus($status)
{
    switch ($status) {
        case 'Preparando':
            return 'linha-preparando';
        case 'Saiu':
            return 'linha-saiu';
        case 'Entregue':
            return 'linha-entregue';
        default:
            return '';
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">

    <style>
        /* Modal editar*/

        #modalEditarPedido .modal-body {
            max-height: 65vh;
            overflow-y: auto;
            padding-right: 10px;
        }

        #modalEditarPedido .modal-content {
            border-radius: 12px;
        }

        #modalEditarPedido .modal-header {
            border-bottom: 1px solid #eee;
        }

        #modalEditarPedido .modal-footer {
            border-top: 1px solid #eee;
        }

        body {
            background: #f5f7fb;
        }

        /* MOdal Novo Pedido*/
        #modalNovoPedido .modal-body {
            max-height: 65vh;
            overflow-y: auto;
            padding-right: 10px;
        }

        #modalNovoPedido .modal-content {
            border-radius: 12px;
        }

        #modalNovoPedido .modal-header {
            border-bottom: 1px solid #eee;
        }

        #modalNovoPedido .modal-footer {
            border-top: 1px solid #eee;
        }

        .page-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: #fff;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-resumo {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .05);
        }

        .table tbody tr:hover {
            background: #f1f5ff;
        }

        .badge-status {
            padding: 6px 10px;
            font-size: .75rem;
            border-radius: 8px;
        }

        .acoes-col {
            min-width: 250px;
        }

        .table td {
            vertical-align: middle;
        }

        .table tbody tr.linha-preparando>td {
            background: #fff3cd !important;
            border-left: 4px solid #ffc107;
        }

        .table tbody tr.linha-saiu>td {
            background: #cff4fc !important;
            border-left: 4px solid #0dcaf0;
        }

        .table tbody tr.linha-entregue>td {
            background: #d1e7dd !important;
            border-left: 4px solid #198754;
        }
    </style>
</head>

<body>
    <div class="container py-4">

        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-box-seam"></i> Gerenciador de Pedidos</h3>
                <small class="opacity-75">Controle e acompanhamento em tempo real</small>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-light btn-sm">
                    <i class="bi bi-speedometer2"></i> Painel
                </a>
                <a href="pedidos.php" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                </a>
            </div>
            <button class="btn btn-success btn-sm fw-bold"
                data-bs-toggle="modal"
                data-bs-target="#modalNovoPedido">
                <i class="bi bi-plus-circle"></i> Novo Pedido
            </button>

        </div>

        <!-- RESUMO -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card card-resumo p-3">
                    <small class="text-muted">Pedidos no período</small>
                    <h4 class="fw-bold mb-0"><?= count($pedidosFiltrados) ?></h4>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="bg-white p-3 rounded shadow-sm mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data Inicial</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= $dataInicioForm ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Data Final</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= $dataFimForm ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
                </div>
                <div class="col-md-2 d-grid">
                    <a href="pedidos.php" class="btn btn-outline-secondary">Hoje</a>
                </div>
            </form>
        </div>

        <?php if (empty($pedidosFiltrados)): ?>
            <div class="alert alert-info">Nenhum pedido encontrado.</div>
        <?php else: ?>
            <!--Listar pedidos-->
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>#</th>
                                <th>Data</th>
                                <th>Cliente</th>
                                <th>Endereço</th>
                                <th>Pagamento</th>
                                <th>Itens</th>
                                <th>Total</th>
                                <th>Obs</th>
                                <th>Status</th>
                                <th class="acoes-col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidosFiltrados as $p): ?>
                                <tr class="<?= corLinhaStatus($p['status']) ?>">
                                    <td class="fw-bold text-center text-primary">#<?= $p['id'] ?></td>

                                    <td><?= date('d/m/Y H:i', strtotime($p['data_pedido'])) ?></td>

                                    <td>
                                        <strong><?= e($p['nome']) ?></strong><br>
                                        <small class="text-muted"><?= e($p['telefone']) ?></small>
                                    </td>

                                    <td>
                                        <?= e($p['endereco']) ?><br>
                                        <small class="text-muted"><?= e($p['referencia']) ?></small>
                                    </td>

                                    <td class="fw-semibold"><?= e($p['pagamento']) ?></td>

                                    <td>
                                        <ul class="small mb-0 ps-3">
                                            <?php foreach ($p['itens'] as $item): ?>
                                                <li>
                                                    <?= e($item['nome']) ?>
                                                    (R$ <?= number_format($item['preco'], 2, ',', '.') ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>

                                    <td class="fw-bold text-danger text-center">
                                        R$ <?= number_format($p['total'], 2, ',', '.') ?>
                                    </td>

                                    <td class="fw-semibold"><?= htmlspecialchars($p['observacao']) ?></td>

                                    <td class="text-center">
                                        <span class="badge <?= corStatus($p['status']) ?>">
                                            <?= e($p['status'] ?? 'novo') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-grid gap-1">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-warning"
                                                    onclick="alterarStatus(<?= $p['id'] ?>,'Preparando')">Preparando</button>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="alterarStatus(<?= $p['id'] ?>,'Saiu')">Saiu</button>
                                                <button class="btn btn-sm btn-success"
                                                    onclick="alterarStatus(<?= $p['id'] ?>,'Entregue')">Entregue</button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="alterarStatus(<?= $p['id'] ?>,'Cancelado')">Cancelar</button>
                                            </div>
                                            <button class="btn btn-sm btn-primary"
                                                onclick='abrirEditarPedido(
                                                    <?= $p["id"] ?>,
                                                    <?= json_encode($p["nome"]) ?>,
                                                    <?= json_encode($p["telefone"]) ?>,
                                                    <?= json_encode($p["endereco"]) ?>,
                                                    <?= json_encode($p["referencia"]) ?>,
                                                    <?= json_encode($p["pagamento"]) ?>,
                                                    <?= json_encode($p["observacao"]) ?>,
                                                    <?= json_encode($p["itens"]) ?>
                                                )'>
                                                ✏ Editar
                                            </button>

                                            <a class="btn btn-sm btn-outline-dark" href="imprimir_pedido.php?id=<?= $p['id'] ?>" target="_blank" title="Imprimir Pedido">
                                                <i class="bi bi-printer"></i> Imprimir
                                            </a>
                                            <button class="btn btn-sm btn-danger"
                                                onclick="excluirPedido(<?= $p['id'] ?>)">🗑 Excluir</button>
                                        </div>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <!-- MODAL EDITAR -->
    <div class="modal fade" id="modalEditarPedido">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="editar_pedido.php">

                    <input type="hidden" name="id" id="edit_id">

                    <div class="modal-header">
                        <h5 class="modal-title">Editar Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <h6 class="mb-3 text-primary">Dados do Cliente</h6>
                        <input type="text" name="nome" id="edit_nome" class="form-control mb-2" required>
                        <input type="text" name="telefone" id="edit_telefone" class="form-control mb-2" required>
                        <input type="text" name="endereco" id="edit_endereco" class="form-control mb-2" required>
                        <input type="text" name="referencia" id="edit_referencia" class="form-control mb-2">
                        <hr>
                        <button type="button" class="btn btn-sm btn-success mt-2"
                            onclick="adicionarItemEdit()">
                            ➕ Adicionar Item
                        </button>
                        <hr>
                        <h6 class="mb-3 text-primary">Itens do Pedido</h6>
                        <div id="edit_itens"></div>
                        <hr>
                        <h6 class="mb-3 text-primary">Pagamento</h6>
                        <select name="pagamento" id="edit_pagamento" class="form-control mb-2">
                            <option>Pix</option>
                            <option>Dinheiro</option>
                            <option>Cartão Crédito</option>
                            <option>Cartão Débito</option>
                        </select>

                        <textarea name="observacao" id="edit_observacao" class="form-control"></textarea>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">Salvar Alterações</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- MODAL NOVO PEDIDO -->
    <div class="modal fade" id="modalNovoPedido">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <form method="POST" action="salvar_pedido_manual.php">

                    <div class="modal-header">
                        <h5 class="modal-title">Novo Pedido Manual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <!-- DADOS CLIENTE -->
                        <h6 class="text-primary mb-3">Dados do Cliente</h6>

                        <input type="text" name="nome" class="form-control mb-2" placeholder="Nome" required>
                        <input type="text" name="telefone" class="form-control mb-2" placeholder="Telefone" required>
                        <input type="text" name="endereco" class="form-control mb-2" placeholder="Endereço" required>
                        <input type="text" name="referencia" class="form-control mb-3" placeholder="Referência">

                        <hr>

                        <!-- ITENS -->
                        <h6 class="text-primary mb-2">Itens do Pedido</h6>

                        <button type="button" class="btn btn-sm btn-success mb-3" id="btnAdicionarItem">
                            ➕ Adicionar Item
                        </button>

                        <div id="novo_itens"></div>

                        <hr>

                        <!-- PAGAMENTO -->
                        <h6 class="text-primary mb-3">Pagamento</h6>

                        <select name="pagamento" class="form-control mb-2">
                            <option>Pix</option>
                            <option>Dinheiro</option>
                            <option>Cartão Crédito</option>
                            <option>Cartão Débito</option>
                        </select>

                        <div class="mb-3">
                            <label><strong>Total:</strong></label>
                            <input type="number" step="0.01" name="total" id="novo_total"
                                class="form-control" readonly required>
                        </div>

                        <hr>

                        <h6 class="text-primary mb-2">Observação</h6>
                        <textarea name="observacao" class="form-control"></textarea>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">Salvar Pedido</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <audio id="somNovoPedido" src="notificacao.mp3" preload="auto"></audio>

    <button id="ativarSom" class="btn btn-warning position-fixed bottom-0 end-0 m-4 shadow"
        style="z-index:9999;">
        🔔 Ativar som de novos pedidos
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!--Controlar som-->
    <script>
        let tocandoAgora = false;
        const pedidoAudio = document.getElementById('somNovoPedido');
        const pedidoAudioBtn = document.getElementById('ativarSom');
        let pedidoSomLiberado = true;
        localStorage.setItem('pedidoSomLiberado', 'true');
        let totalAnterior = <?= (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn() ?>;
        let ultimoIdAnterior = <?= (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM pedidos')->fetchColumn() ?>;
        let pedidoAudioContext = null;

        pedidoAudio.preload = 'auto';
        pedidoAudio.load();

        function getAudioContext() {
            if (!pedidoAudioContext) {
                pedidoAudioContext = new(window.AudioContext || window.webkitAudioContext)();
            }
            return pedidoAudioContext;
        }

        async function tocarBeepAlerta() {
            try {
                const ctx = getAudioContext();
                if (ctx.state === 'suspended') {
                    await ctx.resume();
                }

                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.value = 880;
                gain.gain.value = 0.0001;
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();

                gain.gain.exponentialRampToValueAtTime(0.12, ctx.currentTime + 0.01);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.45);
                osc.stop(ctx.currentTime + 0.5);
            } catch (e) {
                console.log('Erro ao tocar beep:', e);
            }
        }

        function syncBotaoSom() {
            pedidoAudioBtn.classList.add('btn-success');
            pedidoAudioBtn.classList.remove('btn-warning');
            pedidoAudioBtn.textContent = '🔔 Som ativado';
        }

        async function liberarSomPedidos() {
            try {
                const ctx = getAudioContext();
                if (ctx.state === 'suspended') {
                    await ctx.resume();
                }
                await pedidoAudio.play();
                pedidoAudio.pause();
                pedidoAudio.currentTime = 0;
                //pedidoAudio.load();
                pedidoSomLiberado = true;
                localStorage.setItem('pedidoSomLiberado', 'true');
                syncBotaoSom();
            } catch (e) {
                console.log('Erro ao desbloquear som:', e);
                pedidoSomLiberado = true;
                localStorage.setItem('pedidoSomLiberado', 'true');
                syncBotaoSom();
            }
        }

        async function tocarSomPedido() {
            if (!pedidoSomLiberado) return;
            try {
                for (let i = 0; i < 3; i++) {
                    pedidoAudio.currentTime = 0;
                    await pedidoAudio.play();
                    await new Promise((resolve) => {
                        const finalizar = () => {
                            pedidoAudio.removeEventListener('ended', finalizar);
                            resolve();
                        };
                        pedidoAudio.addEventListener('ended', finalizar, { once: true });
                        setTimeout(finalizar, 1000);
                    });

                    if (i < 2) {
                        await new Promise(resolve => setTimeout(resolve, 180));
                    }
                }
            } catch (e) {
                console.log("Erro ao tocar som:", e);
                await tocarBeepAlerta();
            }
        }

        pedidoAudioBtn.addEventListener('click', liberarSomPedidos);
        syncBotaoSom();

        document.addEventListener('click', () => {
            if (!pedidoSomLiberado) {
                liberarSomPedidos();
            }
        });

        async function verificarNovosPedidos() {
            try {
                const response = await fetch('verificar_pedidos.php?ts=' + Date.now(), {
                    cache: 'no-store'
                });
                const data = await response.json();

                const totalAtual = Number(data.total ?? 0);
                const ultimoIdAtual = Number(data.ultimo_id ?? 0);
                const houveNovoPedido = ultimoIdAtual > ultimoIdAnterior || totalAtual > totalAnterior;

                if (houveNovoPedido) {
                    await tocarSomPedido();
                    totalAnterior = totalAtual;
                    ultimoIdAnterior = ultimoIdAtual;
                    setTimeout(() => location.reload(), 500);
                } else {
                    totalAnterior = totalAtual;
                    ultimoIdAnterior = ultimoIdAtual;
                }
            } catch (error) {
                console.log("Erro ao verificar pedidos:", error);
            }
        }

        verificarNovosPedidos();
        setInterval(verificarNovosPedidos, 3000);
        window.addEventListener('focus', verificarNovosPedidos);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                verificarNovosPedidos();
            }
        });
    </script>

    <!--Listar produtos-->
    <script>
        window.produtos = <?= json_encode($produtos ?? [], JSON_UNESCAPED_UNICODE) ?>;
        const listaProdutos = window.produtos || [];
    </script>

    <!--status e excluir pedidos-->
    <script>
        async function alterarStatus(id, status) {
            await fetch('atualizar_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id + '&status=' + status
            });
            location.reload();
        }

        async function excluirPedido(id) {
            if (!confirm('Deseja excluir este pedido?')) return;

            try {
                const response = await fetch('excluir_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + id
                });

                const texto = await response.text();

                if (texto.trim() === "OK") {
                    location.reload();
                } else {
                    alert("Erro ao excluir: " + texto);
                }

            } catch (error) {
                alert("Erro na requisição: " + error);
            }
        }
    </script>

    <!--Script Editar Pedido Com Modal-->
    <script>
        function abrirEditarPedido(id, nome, telefone, endereco, referencia, pagamento, observacao, itens) {

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_telefone').value = telefone;
            document.getElementById('edit_endereco').value = endereco;
            document.getElementById('edit_referencia').value = referencia;
            document.getElementById('edit_pagamento').value = pagamento;
            document.getElementById('edit_observacao').value = observacao;

            const container = document.getElementById('edit_itens');
            container.innerHTML = '';
            contadorItensEdit = 0;

            itens.forEach((item) => {

                const index = contadorItensEdit++;

                container.innerHTML += `
                    <div class="border rounded p-2 mb-2 item-edit">

                        <input type="hidden" 
                            name="itens[${index}][nome]" 
                            value="${item.nome}">

                        <strong>${item.nome}</strong>

                        <div class="row mt-2">
                            <div class="col">
                                <input type="number" step="0.01" 
                                    name="itens[${index}][preco]" 
                                    value="${item.preco}" 
                                    class="form-control" required>
                            </div>

                            <div class="col">
                                <input type="number" 
                                    name="itens[${index}][quantidade]" 
                                    value="${item.quantidade}" 
                                    class="form-control" required>
                            </div>

                            <div class="col-2 d-grid">
                                <button type="button" 
                                    class="btn btn-danger"
                                    onclick="this.closest('.item-edit').remove()">
                                    ❌
                                </button>
                            </div>
                        </div>

                    </div>
                `;
            });


            new bootstrap.Modal(document.getElementById('modalEditarPedido')).show();
        }
        let contadorItensEdit = 0;

        function adicionarItemEdit() {

            const container = document.getElementById('edit_itens');
            const index = contadorItensEdit++;

            let options = '<option value="">Selecione o produto</option>';

            listaProdutos.forEach(prod => {
                options += `<option value="${prod.nome}" data-preco="${prod.preco}">
                        ${prod.nome}
                    </option>`;
            });

            container.innerHTML += `
                <div class="border rounded p-2 mb-2 item-edit">

                    <div class="row mb-2">
                        <div class="col">
                            <select name="itens[${index}][nome]" 
                                class="form-control produto-select" 
                                onchange="atualizarPreco(this, ${index})" required>
                                ${options}
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <input type="number" step="0.01" 
                                name="itens[${index}][preco]" 
                                placeholder="Preço"
                                class="form-control preco-input" required>
                        </div>

                        <div class="col">
                            <input type="number" 
                                name="itens[${index}][quantidade]" 
                                placeholder="Qtd"
                                class="form-control" value="1" required>
                        </div>

                        <div class="col-2 d-grid">
                            <button type="button" 
                                class="btn btn-danger"
                                onclick="this.closest('.item-edit').remove()">
                                ❌
                            </button>
                        </div>
                    </div>

                </div>
            `;
        }

        function atualizarPreco(select, index) {
            const selectedOption = select.options[select.selectedIndex];
            const preco = selectedOption.getAttribute('data-preco');

            const container = select.closest('.item-edit');
            const inputPreco = container.querySelector('.preco-input');

            inputPreco.value = preco;
        }
    </script>

    <!--Novo pedido Manual-->
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const produtos = window.produtos || [];
            const btnAdicionar = document.getElementById("btnAdicionarItem");
            const container = document.getElementById("novo_itens");
            const campoTotal = document.getElementById("novo_total");

            let contadorNovo = 0;

            if (btnAdicionar) {
                btnAdicionar.addEventListener("click", adicionarItemNovo);
            }

            function adicionarItemNovo() {

                if (!container) return;

                if (!produtos.length) {
                    alert("Nenhum produto cadastrado.");
                    return;
                }

                let options = `<option value="">Selecione</option>`;

                produtos.forEach(p => {
                    const preco = parseFloat(p.preco) || 0;

                    options += `
                    <option value="${p.nome}" data-preco="${preco}">
                        ${p.nome}
                    </option>
                `;
                });

                container.insertAdjacentHTML('beforeend', `
                    <div class="border rounded p-2 mb-2 item-novo">

                        <div class="row mb-2">
                            <div class="col-6">
                                <select name="itens[${contadorNovo}][nome]" 
                                        class="form-control produto-select" required>
                                    ${options}
                                </select>
                            </div>

                            <div class="col-3">
                                <input type="number" 
                                    name="itens[${contadorNovo}][quantidade]" 
                                    value="1" min="1"
                                    class="form-control quantidade" required>
                            </div>

                            <div class="col-3">
                                <input type="number" step="0.01"
                                    name="itens[${contadorNovo}][preco]" 
                                    class="form-control preco" readonly required>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-danger remover-item">
                            Remover
                        </button>

                    </div>
                `);

                contadorNovo++;
            }

            document.addEventListener('change', function(e) {

                if (e.target.classList.contains('produto-select')) {

                    const selected = e.target.options[e.target.selectedIndex];
                    const preco = parseFloat(selected.getAttribute('data-preco')) || 0;

                    const item = e.target.closest('.item-novo');
                    item.querySelector('.preco').value = preco.toFixed(2);

                    calcularTotalNovo();
                }
            });

            document.addEventListener('input', function(e) {

                if (e.target.classList.contains('quantidade')) {
                    calcularTotalNovo();
                }
            });

            document.addEventListener('click', function(e) {

                if (e.target.classList.contains('remover-item')) {
                    e.target.closest('.item-novo').remove();
                    calcularTotalNovo();
                }
            });

            function calcularTotalNovo() {

                let total = 0;

                document.querySelectorAll('.item-novo').forEach(item => {

                    const preco = parseFloat(item.querySelector('.preco')?.value) || 0;
                    const qtd = parseInt(item.querySelector('.quantidade')?.value) || 0;

                    total += preco * qtd;
                });

                if (campoTotal) {
                    campoTotal.value = total.toFixed(2);
                }
            }

        });
    </script>

</body>

</html>
