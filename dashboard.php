<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$totalPedidos = (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();
$totalFaturado = (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE status = 'Entregue'")->fetchColumn();
$emAndamento = (int) $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status IN ('Preparando', 'Saiu')")->fetchColumn();
$faturamentoHoje = (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE DATE(data_pedido) = CURDATE() AND status = 'Entregue'")->fetchColumn();

$configRuntime = config_value('delivery', []);
$taxaEntrega = (float) ($configRuntime['taxa_entrega'] ?? 5.00);
$taxaAtiva = (bool) ($configRuntime['taxa_ativa'] ?? true);
$lojaFechada = (bool) ($configRuntime['loja_fechada'] ?? false);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>

<body>
    <div class="container app-shell py-4">
        <div class="app-hero p-4 p-lg-5 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="img/logo-zipfood.svg" alt="ZipFood" style="height:40px;width:auto;">
                        <div>
                            <div class="text-uppercase small opacity-75">Back-office</div>
                            <h1 class="display-6 fw-bold mb-1">ZipFood</h1>
                            <div class="opacity-75">Operação, pedidos e cardápio em uma base única.</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="status-chip <?= $lojaFechada ? 'is-danger' : 'is-open' ?>">
                            <?= $lojaFechada ? 'Loja fechada' : 'Loja aberta' ?>
                        </span>
                        <span class="status-chip is-muted">Taxa de entrega: R$ <?= money($taxaEntrega) ?></span>
                        <span class="status-chip <?= $taxaAtiva ? 'is-open' : 'is-muted' ?>">
                            <?= $taxaAtiva ? 'Taxa ativa' : 'Taxa inativa' ?>
                        </span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="small opacity-75">Sessão</div>
                    <div class="fw-bold"><?= e((string) ($_SESSION['admin']['user'] ?? 'admin')) ?></div>
                    <a href="logout.php" class="btn btn-outline-light btn-sm mt-3">Sair</a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="metric-card is-success p-4 h-100">
                    <div class="metric-label">Faturamento total</div>
                    <div class="metric-value mt-2">R$ <?= money($totalFaturado) ?></div>
                    <i class="bi bi-cash-stack metric-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card is-primary p-4 h-100">
                    <div class="metric-label">Pedidos</div>
                    <div class="metric-value mt-2"><?= $totalPedidos ?></div>
                    <i class="bi bi-box-seam metric-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card is-warning p-4 h-100">
                    <div class="metric-label">Em andamento</div>
                    <div class="metric-value mt-2"><?= $emAndamento ?></div>
                    <i class="bi bi-fire metric-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card is-purple p-4 h-100">
                    <div class="metric-label">Faturamento hoje</div>
                    <div class="metric-value mt-2">R$ <?= money($faturamentoHoje) ?></div>
                    <i class="bi bi-graph-up metric-icon"></i>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card-menu p-4 text-center h-100">
                    <div class="display-6">🍔</div>
                    <h5 class="mt-3 fw-bold">Produtos</h5>
                    <p class="text-muted mb-4">Gerenciar cardápio e disponibilidade.</p>
                    <a href="painel.php" class="btn btn-brand w-100">Acessar</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-menu p-4 text-center h-100">
                    <div class="display-6">📦</div>
                    <h5 class="mt-3 fw-bold">Pedidos</h5>
                    <p class="text-muted mb-4">Acompanhar status e operação em tempo real.</p>
                    <a id="btnPedidos" href="pedidos.php" class="btn btn-primary w-100">Abrir pedidos</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-menu p-4 text-center h-100">
                    <div class="display-6">💰</div>
                    <h5 class="mt-3 fw-bold">Financeiro</h5>
                    <p class="text-muted mb-4">Relatório de vendas e receita.</p>
                    <a href="financeiro.php" class="btn btn-success w-100">Ver relatório</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card-menu p-4 text-center h-100">
                    <div class="display-6">🏢</div>
                    <h5 class="mt-3 fw-bold">Emitente</h5>
                    <p class="text-muted mb-4">Dados do estabelecimento para relatórios e pedidos.</p>
                    <a href="config_emitente.php" class="btn btn-info w-100">Configurar emitente</a>
                </div>
            </div>

            <!-- Feedbacks -->
            <div class="col-md-3">
                <div class="card-menu p-4 text-center h-100">
                    <div class="display-6">⭐</div>
                    <h5 class="mt-3 fw-bold">Feedbacks</h5>
                    <p class="text-muted mb-4">Avaliações e comentários dos clientes.</p>
                    <a href="feedbacks.php" class="btn btn-warning w-100">Ver feedbacks</a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="config-panel p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Configuração de entrega</h5>
                            <div class="text-muted">Ajuste operação sem acessar o banco diretamente.</div>
                        </div>
                        <span class="status-chip is-muted">Configuração local</span>
                    </div>

                    <form action="salvar_config.php" method="POST" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Taxa de entrega (R$)</label>
                            <input type="number" step="0.01" name="taxa_entrega" value="<?= e((string) $taxaEntrega) ?>" class="form-control" required>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="taxa_ativa" <?= $taxaAtiva ? 'checked' : '' ?>>
                                <label class="form-check-label">Ativar taxa de entrega</label>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="loja_fechada" <?= $lojaFechada ? 'checked' : '' ?>>
                                <label class="form-check-label">Loja fechada</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-brand w-100">Salvar configuração</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-soft p-4 h-100">
                    <h5 class="fw-bold mb-3">Atalhos</h5>
                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-outline-primary">Abrir cardápio</a>
                        <a href="painel.php" class="btn btn-outline-dark">Gerenciar produtos</a>
                        <a href="pedidos.php" class="btn btn-outline-success">Gerenciar pedidos</a>
                        <a href="financeiro.php" class="btn btn-outline-warning">Financeiro</a>
                    </div>
                </div>
            </div>
        </div>

        <audio id="somNovoPedido" src="notificacao.mp3" preload="auto"></audio>
        <button id="ativarSom" class="btn btn-warning position-fixed bottom-0 end-0 m-4 shadow">
            🔊 Ativar som
        </button>
        <div id="avisoSom" class="alert alert-info position-fixed bottom-0 start-0 m-4 shadow" style="z-index:9999; display:none;">
            ⚠️ Clique em qualquer lugar da tela para ativar o som de novos pedidos.
        </div>
    </div>

    <script>
        const pedidoAudio = document.getElementById('somNovoPedido');
        const pedidoAudioBtn = document.getElementById('ativarSom');
        const pedidoTrigger = document.getElementById('btnPedidos');
        let pedidoSomLiberado = localStorage.getItem('pedidoSomLiberado') === 'true';
        const avisoSom = document.getElementById('avisoSom');
        let totalAnterior = <?= $totalPedidos ?>;
        let ultimoIdAnterior = <?= (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM pedidos')->fetchColumn() ?>;
        let pedidoAudioContext = null;

        pedidoAudio.preload = 'auto';
        pedidoAudio.load();
        // Exibe aviso se som não estiver liberado
        if (!pedidoSomLiberado) {
            avisoSom.style.display = 'block';
        }

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
            pedidoAudioBtn.textContent = '🔊 Som ativado';
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
                        pedidoAudio.addEventListener('ended', finalizar, {
                            once: true
                        });
                        setTimeout(finalizar, 1000);
                    });

                    if (i < 2) {
                        await new Promise(resolve => setTimeout(resolve, 180));
                    }
                }
            } catch (e) {
                console.log('Erro ao tocar som:', e);
                await tocarBeepAlerta();
            }
        }

        pedidoAudioBtn.addEventListener('click', liberarSomPedidos);
        syncBotaoSom();

        function tentarLiberarSomPorInteracao() {
            if (!pedidoSomLiberado) {
                liberarSomPedidos();
                avisoSom.style.display = 'none';
                document.removeEventListener('click', tentarLiberarSomPorInteracao);
                document.removeEventListener('touchstart', tentarLiberarSomPorInteracao);
            }
        }
        document.addEventListener('click', tentarLiberarSomPorInteracao);
        document.addEventListener('touchstart', tentarLiberarSomPorInteracao);
        if (pedidoSomLiberado) {
            avisoSom.style.display = 'none';
        }

        async function verificarNovosPedidos() {
            try {
                const res = await fetch('verificar_pedidos.php?ts=' + Date.now(), {
                    cache: 'no-store'
                });
                const data = await res.json();

                const totalAtual = Number(data.total ?? 0);
                const ultimoIdAtual = Number(data.ultimo_id ?? 0);
                const houveNovoPedido = ultimoIdAtual > ultimoIdAnterior || totalAtual > totalAnterior;

                if (houveNovoPedido) {
                    pedidoTrigger.classList.remove('btn-primary');
                    pedidoTrigger.classList.add('btn-danger');
                    pedidoTrigger.textContent = '📦 Novo pedido!';
                    await tocarSomPedido();
                    totalAnterior = totalAtual;
                    ultimoIdAnterior = ultimoIdAtual;
                } else {
                    pedidoTrigger.classList.remove('btn-danger');
                    pedidoTrigger.classList.add('btn-primary');
                    pedidoTrigger.textContent = '📦 Abrir pedidos';
                    totalAnterior = totalAtual;
                    ultimoIdAnterior = ultimoIdAtual;
                }
            } catch (error) {
                console.log('Erro ao verificar pedidos:', error);
            }
        }

        verificarNovosPedidos();
        setInterval(verificarNovosPedidos, 1000);
        window.addEventListener('focus', verificarNovosPedidos);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                verificarNovosPedidos();
            }
        });
    </script>
</body>

</html>