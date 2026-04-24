<?php

require_once __DIR__ . '/conexao.php';

$stmt = $pdo->query("SELECT * FROM produtos WHERE disponivel = 1 ORDER BY promo_ativa DESC, categoria, nome");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$configRuntime = config_value('delivery', []);
$lojaFechada = (bool) ($configRuntime['loja_fechada'] ?? false);
$taxaEntrega = (float) ($configRuntime['taxa_entrega'] ?? 0);
$brand = (string) config_value('app.brand', 'Delivery Pro');

$categorias = ['Todos'];
foreach ($produtos as $produto) {
    $categoria = trim((string) ($produto['categoria'] ?? 'Outros'));
    if ($categoria === '') {
        $categoria = 'Outros';
    }

    if (!in_array($categoria, $categorias, true)) {
        $categorias[] = $categoria;
    }
}

$produtosPromocao = array_values(array_filter($produtos, static function (array $produto): bool {
    $precoOriginal = (float) ($produto['preco'] ?? 0);
    $precoOferta = (float) ($produto['preco_promocional'] ?? 0);

    return !empty($produto['promo_ativa']) && $precoOferta > 0 && $precoOferta < $precoOriginal;
}));

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($brand) ?> | Cardápio Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">

    <style>
        .public-shell {
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
            min-height: 100vh;
        }

        .public-shell::before,
        .public-shell::after {
            content: "";
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 999px;
            filter: blur(18px);
            opacity: 0.45;
            pointer-events: none;
            z-index: -1;
        }

        .public-shell::before {
            top: -120px;
            right: -80px;
            background: radial-gradient(circle, rgba(255, 107, 0, 0.32), transparent 65%);
        }

        .public-shell::after {
            bottom: -120px;
            left: -90px;
            background: radial-gradient(circle, rgba(29, 78, 216, 0.18), transparent 65%);
        }

        .hero-card {
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.92)),
                radial-gradient(circle at top right, rgba(255, 107, 0, 0.28), transparent 24%),
                radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.2), transparent 28%);
            color: #fff;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: var(--app-shadow);
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            letter-spacing: -0.03em;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
        }

        .menu-toolbar {
            position: sticky;
            top: 16px;
            z-index: 20;
        }

        .menu-search {
            border-radius: 16px;
            min-height: 52px;
        }

        .category-chip {
            border: 1px solid rgba(15, 23, 42, 0.1);
            background: rgba(255, 255, 255, 0.9);
            color: #0f172a;
            border-radius: 999px;
            padding: 0.65rem 1rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .category-chip.active {
            background: linear-gradient(135deg, #ff6b00, #ff3d00);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 12px 24px rgba(255, 107, 0, 0.18);
        }

        .menu-card {
            position: relative;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 20px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.10);
        }

        .menu-image {
            height: 160px;
            object-fit: cover;
            width: 100%;
            background: linear-gradient(135deg, #e2e8f0, #f8fafc);
        }

        .menu-card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .menu-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.4em;
        }

        .menu-promo {
            border: 1px solid rgba(255, 107, 0, 0.18);
            background:
                linear-gradient(135deg, rgba(255, 107, 0, 0.10), rgba(255, 61, 0, 0.06)),
                #fff;
            box-shadow: 0 14px 28px rgba(255, 107, 0, 0.10);
            transform: translateY(-2px);
        }

        .menu-promo::before {
            content: "PROMOÇÃO";
            position: absolute;
            top: 14px;
            right: -34px;
            background: linear-gradient(135deg, #ff6b00, #ff3d00);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            padding: 0.35rem 2.5rem;
            transform: rotate(28deg);
            box-shadow: 0 10px 22px rgba(255, 107, 0, 0.25);
            z-index: 2;
        }

        .menu-promo:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 18px 36px rgba(255, 107, 0, 0.14);
        }

        .menu-promo .price-pill {
            background: rgba(255, 107, 0, 0.14);
        }

        .menu-promo-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
            color: #9a3412;
            background: rgba(255, 237, 213, 0.95);
            animation: pulsePromo 1.8s infinite;
        }

        .promo-showcase {
            background: linear-gradient(135deg, rgba(255, 107, 0, 0.08), rgba(255, 61, 0, 0.04)), #fff;
            border: 1px solid rgba(255, 107, 0, 0.12);
            border-radius: 28px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }

        .promo-showcase .menu-card {
            min-height: 100%;
        }

        .promo-title {
            font-family: 'Playfair Display', serif;
            letter-spacing: -0.02em;
        }

        @keyframes pulsePromo {
            0%, 100% {
                transform: translateY(0);
                box-shadow: 0 0 0 0 rgba(255, 107, 0, 0.18);
            }
            50% {
                transform: translateY(-1px);
                box-shadow: 0 0 0 10px rgba(255, 107, 0, 0);
            }
        }

        .old-price {
            color: #94a3b8;
            text-decoration: line-through;
            font-size: 0.92rem;
        }

        .menu-meta {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            align-items: center;
        }

        .price-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 800;
            color: #0f172a;
            background: rgba(255, 107, 0, 0.10);
            border-radius: 999px;
            padding: 0.45rem 0.75rem;
        }

        .cart-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 64px;
            height: 64px;
            border: 0;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(135deg, #ff6b00, #ff3d00);
            box-shadow: 0 16px 34px rgba(255, 107, 0, 0.3);
            z-index: 1000;
        }

        .cart-badge {
            position: absolute;
            top: -6px;
            right: -4px;
            background: #16a34a;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 800;
            border-radius: 999px;
            padding: 0.2rem 0.5rem;
        }

        .cart-drawer {
            position: fixed;
            inset: auto 0 0 0;
            background: #fff;
            border-top-left-radius: 28px;
            border-top-right-radius: 28px;
            box-shadow: 0 -20px 50px rgba(15, 23, 42, 0.18);
            transform: translateY(100%);
            transition: transform 0.28s ease;
            z-index: 999;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            height: 86dvh;
            max-height: 86dvh;
            overflow: hidden;
        }

        .cart-drawer.ativo {
            transform: translateY(0);
        }

        .cart-items {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            padding-right: 12px;
        }

        @media (min-width: 992px) {
            .cart-drawer {
                top: 0;
                bottom: 0;
                right: -430px;
                left: auto;
                width: 420px;
                height: 100dvh;
                max-height: 100dvh;
                border-radius: 0;
                transform: translateX(100%);
            }

            .cart-drawer.ativo {
                transform: translateX(0);
                right: 0;
            }

            .cart-button {
                width: 72px;
                height: 72px;
            }
        }
    </style>
</head>

<body class="public-shell">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top" style="z-index:1050;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="#">
                <img src="img/logo-zipfood.svg" alt="ZipFood" style="height:32px;width:auto;">
                ZipFood
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                    <li class="nav-item"><a class="nav-link fw-bold" href="#menu">Cardápio</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold" href="acompanhar.php">Acompanhar Pedido</a></li>
                    <li class="nav-item">
                        <button class="btn btn-outline-primary position-relative" onclick="toggleCarrinho()">
                            <i class="bi bi-bag"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="contadorCarrinhoNav" style="font-size:0.8em;">0</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <script>
    // Sincroniza badge do carrinho na navbar
    function syncCarrinhoNav() {
        const badge = document.getElementById('contadorCarrinhoNav');
        const mainBadge = document.getElementById('contadorCarrinho');
        if (badge && mainBadge) badge.innerText = mainBadge.innerText;
    }
    setInterval(syncCarrinhoNav, 500);
    </script>
    <div style="height:64px;"></div>

    <main class="container py-4 py-lg-5">
        <?php if ($lojaFechada): ?>
            <div class="alert alert-warning soft-panel border-0 mb-4">
                <strong>Pedidos temporariamente pausados.</strong> A loja está fechada no momento.
            </div>
        <?php endif; ?>

        <section class="hero-card p-4 p-lg-5 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="hero-badge mb-3">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Pedido rápido e sem complicação
                    </span>
                    <h1 class="hero-title display-5 fw-bold mb-3">
                        Seu delivery rápido e prático.
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Explore o cardápio, monte seu pedido e finalize em poucos toques. Visual limpo, rápido no celular e elegante no desktop.
                    </p>

                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <a href="#menu" class="btn btn-brand btn-lg">Explorar cardápio</a>
                        <button class="btn btn-outline-light btn-lg" onclick="toggleCarrinho()">Abrir carrinho</button>
                    </div>
                    <div class="mt-2">
                        <a href="/delivery/flutter_zipfood/build/app/outputs/flutter-apk/app-release.apk" class="btn btn-success btn-sm" target="_blank">
                            <i class="bi bi-phone"></i> Baixar app para Android 
                        </a>
                        <span class="text-white-50 small ms-2">Instale o app Zipfood no seu celular Android</span>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="soft-panel p-4 bg-white text-dark">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="text-muted small">Taxa de entrega</div>
                                <div class="h3 fw-bold mb-0">R$ <?= money($taxaEntrega) ?></div>
                            </div>
                            <div class="brand-mark">%</div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 rounded-4 bg-light">
                                    <div class="small text-muted">Itens</div>
                                    <div class="fw-bold"><?= count($produtos) ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-4 bg-light">
                                    <div class="small text-muted">Status</div>
                                    <div class="fw-bold"><?= $lojaFechada ? 'Fechado' : 'Aberto' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="menu-toolbar mb-4">
            <div class="soft-panel p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-5">
                        <label class="form-label small text-uppercase fw-bold text-muted mb-1">Buscar no cardápio</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="search" id="searchProduto" class="form-control menu-search border-start-0" placeholder="Digite o nome do produto">
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <label class="form-label small text-uppercase fw-bold text-muted mb-1">Categorias</label>
                        <div class="d-flex flex-wrap gap-2" id="categoriaFilters">
                            <?php foreach ($categorias as $index => $categoria): ?>
                                <button type="button"
                                    class="category-chip <?= $index === 0 ? 'active' : '' ?>"
                                    data-category="<?= e($categoria) ?>">
                                    <?= e($categoria) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($produtosPromocao)): ?>
            <section class="promo-showcase p-4 p-lg-4 mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <div class="small text-uppercase fw-bold text-muted">Destaques</div>
                        <h2 class="promo-title h4 fw-bold mb-0">Produtos em promoção</h2>
                    </div>
                    <span class="badge rounded-pill text-bg-danger">Oferta ativa</span>
                </div>

                <div class="row g-3">
                    <?php foreach (array_slice($produtosPromocao, 0, 3) as $p): ?>
                        <?php
                        $categoria = trim((string) ($p['categoria'] ?? 'Outros'));
                        if ($categoria === '') {
                            $categoria = 'Outros';
                        }
                        $precoOriginal = (float) ($p['preco'] ?? 0);
                        $precoOferta = (float) ($p['preco_promocional'] ?? 0);
                        ?>
                        <div class="col-sm-6 col-xl-4">
                            <article class="menu-card menu-promo">
                                <img src="<?= e($p['imagem'] ?: 'img/sem-foto.png') ?>" class="menu-image" alt="<?= e($p['nome']) ?>">
                                <div class="p-3 menu-card-body">
                                    <div class="menu-meta mb-3">
                                        <span class="badge rounded-pill text-bg-light"><?= e($categoria) ?></span>
                                        <span class="menu-promo-badge"><i class="bi bi-lightning-charge-fill"></i> Promoção</span>
                                    </div>

                                    <h3 class="h6 fw-bold mb-2 menu-title"><?= e($p['nome']) ?></h3>

                                    <div class="small text-muted mb-1">Preço anterior</div>
                                    <div class="old-price mb-2">R$ <?= money($precoOriginal) ?></div>

                                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                                        <div class="price-pill">
                                            <i class="bi bi-currency-dollar"></i>
                                            R$ <?= money($precoOferta) ?>
                                        </div>
                                        <span class="badge text-bg-danger">Oferta</span>
                                    </div>

                                    <button class="btn btn-brand w-100"
                                        <?= $lojaFechada ? 'disabled' : '' ?>
                                        onclick='addCarrinho(<?= (int) $p["id"] ?>, <?= json_encode($p["nome"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, <?= json_encode((float) $precoOferta) ?>)'>
                                        Adicionar ao carrinho
                                    </button>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section id="menu">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h4 fw-bold mb-1">Cardápio</h2>
                    <div class="text-muted">Escolha seus itens e finalize no carrinho.</div>
                </div>
                <div class="text-muted small">
                    <?= count($produtos) ?> item(ns)
                </div>
            </div>

            <div class="row g-3" id="menuGrid">
                <?php foreach ($produtos as $p): ?>
                    <?php
                    $categoria = trim((string) ($p['categoria'] ?? 'Outros'));
                    if ($categoria === '') {
                        $categoria = 'Outros';
                    }
                    $precoOriginal = (float) ($p['preco'] ?? 0);
                    $precoOferta = (float) ($p['preco_promocional'] ?? 0);
                    $ehPromocao = !empty($p['promo_ativa']);
                    if ($ehPromocao && ($precoOferta <= 0 || $precoOferta >= $precoOriginal)) {
                        $precoOferta = round($precoOriginal * 0.90, 2);
                    }
                    $ehPromocao = $ehPromocao && $precoOferta > 0 && $precoOferta < $precoOriginal;
                    ?>
                    <?php
                    $nomeBusca = function_exists('mb_strtolower') ? mb_strtolower((string) $p['nome']) : strtolower((string) $p['nome']);
                    $categoriaBusca = function_exists('mb_strtolower') ? mb_strtolower($categoria) : strtolower($categoria);
                    ?>
                    <div class="col-sm-6 col-xl-4 produto-item" data-name="<?= e($nomeBusca) ?>" data-category="<?= e($categoriaBusca) ?>">
                        <article class="menu-card <?= $ehPromocao ? 'menu-promo' : '' ?>">
                            <img src="<?= e($p['imagem'] ?: 'img/sem-foto.png') ?>" class="menu-image" alt="<?= e($p['nome']) ?>">
                            <div class="p-4 menu-card-body">
                                <div class="menu-meta mb-3">
                                    <span class="badge rounded-pill text-bg-light"><?= e($categoria) ?></span>
                                    <?php if ($ehPromocao): ?>
                                        <span class="menu-promo-badge"><i class="bi bi-lightning-charge-fill"></i> Promoção</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill text-bg-success">Disponível</span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="h6 fw-bold mb-2 menu-title"><?= e($p['nome']) ?></h3>

                                <?php if ($ehPromocao): ?>
                                    <div class="small text-muted mb-1">Preço anterior</div>
                                    <div class="old-price mb-2">R$ <?= money($precoOriginal) ?></div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                                    <div class="price-pill">
                                        <i class="bi bi-currency-dollar"></i>
                                        R$ <?= money($ehPromocao ? $precoOferta : $precoOriginal) ?>
                                    </div>
                                    <?php if ($ehPromocao): ?>
                                        <span class="badge text-bg-danger">Oferta</span>
                                    <?php endif; ?>
                                </div>

                                <button class="btn btn-brand w-100"
                                    <?= $lojaFechada ? 'disabled' : '' ?>
                                    onclick='addCarrinho(<?= (int) $p["id"] ?>, <?= json_encode($p["nome"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, <?= json_encode((float) ($ehPromocao ? $precoOferta : $precoOriginal)) ?>)'>
                                    Adicionar ao carrinho
                                </button>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <button class="cart-button" onclick="toggleCarrinho()" aria-label="Abrir carrinho">
        <i class="bi bi-bag"></i>
        <span class="cart-badge" id="contadorCarrinho">0</span>
    </button>

    <div class="overlay" id="overlay" onclick="toggleCarrinho()"></div>

    <aside class="cart-drawer" id="carrinhoApp">
        <div class="p-4 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-muted text-uppercase fw-bold">Seu pedido</div>
                    <div class="h5 mb-0 fw-bold">Carrinho</div>
                </div>
                <button class="btn btn-outline-secondary btn-sm" onclick="toggleCarrinho()">Fechar</button>
            </div>
        </div>

        <div class="cart-items p-3 p-lg-4" id="listaCarrinho"></div>

        <div class="border-top p-3 p-lg-4 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Total</span>
                <span class="h4 fw-bold mb-0 text-success">R$ <span id="total">0.00</span></span>
            </div>

            <button onclick="limparCarrinho()" class="btn btn-outline-danger w-100 mb-2">
                Limpar carrinho
            </button>

            <button class="btn btn-brand w-100"
                data-bs-toggle="modal"
                data-bs-target="#modalPedido"
                <?= $lojaFechada ? 'disabled' : '' ?>>
                Finalizar pedido
            </button>
        </div>
    </aside>

    <div class="modal fade" id="modalPedido" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden;">
                <form method="POST" action="salvar_pedido.php" id="formPedido">
                    <div class="modal-header border-0 p-4">
                        <div>
                            <div class="small text-uppercase text-muted fw-bold">Checkout</div>
                            <h5 class="modal-title fw-bold mb-0">Finalizar pedido</h5>
                        </div>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body px-4 pb-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" class="form-control" placeholder="Seu nome" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" id="telefonePedido" class="form-control" placeholder="(00) 00000-0000" required oninput="mascaraTelefone(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Endereço</label>
                                <input type="text" name="endereco" class="form-control" placeholder="Rua, número, bairro" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Referência</label>
                                <input type="text" name="referencia" class="form-control" placeholder="Ponto de referência">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea name="observacao" class="form-control" rows="3" placeholder="Ex.: sem cebola, troco para 100"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Forma de pagamento</label>
                                <select name="pagamento" class="form-select" required>
                                    <option value="">Selecione</option>
                                    <option>Dinheiro</option>
                                    <option>Pix</option>
                                    <option>Cartão Crédito</option>
                                    <option>Cartão Débito</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Taxa de entrega</label>
                                <input type="text" class="form-control" value="R$ <?= money($taxaEntrega) ?>" disabled>
                                <input type="hidden" name="taxa_entrega" value="<?= e((string) $taxaEntrega) ?>">
                                <input type="hidden" name="taxa_ativa" value="<?= (isset($configRuntime['taxa_ativa']) && $configRuntime['taxa_ativa']) ? '1' : '0' ?>">
                            </div>
                            <input type="hidden" name="itens" id="itensInput">
                            <input type="hidden" name="total" id="totalInput">
                            <input type="hidden" name="loja_fechada" value="<?= (isset($configRuntime['loja_fechada']) && $configRuntime['loja_fechada']) ? '1' : '0' ?>">
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button class="btn btn-brand w-100">Enviar pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let carrinho = [];
        const cartItemsEl = document.getElementById('listaCarrinho');

        function addCarrinho(id, nome, preco) {
            const item = carrinho.find(p => p.id == id);
            if (item) {
                item.qtd++;
            } else {
                carrinho.push({ id, nome, preco, qtd: 1 });
            }
            renderCarrinho();
            if (window.innerWidth < 992) {
                toggleCarrinho();
            }
        }

        function renderCarrinho() {
            let html = '';
            let subtotal = 0;
            let quantidadeTotal = 0;

            if (carrinho.length === 0) {
                html = `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bag display-5 d-block mb-2"></i>
                        Seu carrinho está vazio
                    </div>`;
            }

            carrinho.forEach((item, i) => {
                subtotal += item.preco * item.qtd;
                quantidadeTotal += item.qtd;

                html += `
                    <div class="soft-panel p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-bold">${item.nome}</div>
                                <div class="text-muted small">${item.qtd} x R$ ${item.preco.toFixed(2)}</div>
                            </div>
                            <button onclick="removerItem(${i})" class="btn btn-outline-danger btn-sm">✕</button>
                        </div>
                    </div>`;
            });

            // Exibe taxa de entrega se houver itens e taxa estiver ativa
            if (carrinho.length > 0) {
                const taxaEntrega = parseFloat(document.querySelector('input[name="taxa_entrega"]').value || '0');
                const taxaAtiva = (function() {
                    const el = document.querySelector('input[name="taxa_ativa"]');
                    if (!el) return true; // padrão: ativa
                    return el.value === '1' || el.value === 'true';
                })();
                if (taxaAtiva && taxaEntrega > 0) {
                    html += `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Taxa de entrega</span>
                            <span class="fw-bold">R$ ${taxaEntrega.toFixed(2)}</span>
                        </div>`;
                    subtotal += taxaEntrega;
                }
            }

            cartItemsEl.innerHTML = html;
            document.getElementById('total').innerText = subtotal.toFixed(2);
            document.getElementById('totalInput').value = subtotal.toFixed(2);
            document.getElementById('contadorCarrinho').innerText = quantidadeTotal;
        }

        function removerItem(index) {
            carrinho.splice(index, 1);
            renderCarrinho();
        }

        function limparCarrinho() {
            if (confirm('Deseja realmente limpar o carrinho?')) {
                carrinho = [];
                renderCarrinho();
            }
        }

        function toggleCarrinho() {
            document.getElementById('carrinhoApp').classList.toggle('ativo');
            document.getElementById('overlay').classList.toggle('ativo');
        }

        document.getElementById('formPedido').addEventListener('submit', function(e) {
            if (document.querySelector('input[name="loja_fechada"]').value === '1') {
                alert('A loja está fechada no momento. Não é possível enviar pedidos.');
                e.preventDefault();
                return;
            }
            if (carrinho.length === 0) {
                alert('Carrinho vazio!');
                e.preventDefault();
                return;
            }

            document.querySelectorAll('.campo-item').forEach(el => el.remove());

            carrinho.forEach(item => {
                const inputNome = document.createElement('input');
                inputNome.type = 'hidden';
                inputNome.name = 'produto_nome[]';
                inputNome.value = item.nome;
                inputNome.classList.add('campo-item');

                const inputQtd = document.createElement('input');
                inputQtd.type = 'hidden';
                inputQtd.name = 'quantidade[]';
                inputQtd.value = item.qtd;
                inputQtd.classList.add('campo-item');

                const inputPreco = document.createElement('input');
                inputPreco.type = 'hidden';
                inputPreco.name = 'preco[]';
                inputPreco.value = item.preco;
                inputPreco.classList.add('campo-item');

                this.appendChild(inputNome);
                this.appendChild(inputQtd);
                this.appendChild(inputPreco);
            });
        });

        const searchInput = document.getElementById('searchProduto');
        const chips = document.querySelectorAll('.category-chip');
        const items = document.querySelectorAll('.produto-item');

        function filtrarProdutos() {
            const termo = (searchInput.value || '').toLowerCase().trim();
            const activeChip = document.querySelector('.category-chip.active');
            const categoria = activeChip ? activeChip.dataset.category.toLowerCase() : 'todos';

            items.forEach(item => {
                const nome = item.dataset.name || '';
                const itemCategoria = item.dataset.category || '';
                const matchText = !termo || nome.includes(termo);
                const matchCategory = categoria === 'todos' || itemCategoria === categoria;
                item.style.display = matchText && matchCategory ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filtrarProdutos);
        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                chips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                filtrarProdutos();
            });
        });

        renderCarrinho();
    </script>

    <script>
    function mascaraTelefone(input) {
        let v = input.value.replace(/\D/g, '');
        if (v.length > 10) {
            v = v.replace(/(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
        } else if (v.length > 5) {
            v = v.replace(/(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (v.length > 2) {
            v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
        }
        input.value = v;
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <footer class="mt-5 py-4 bg-light border-top">
        <div class="container text-center">
            <a href="avaliacoes.php" class="btn btn-outline-warning" style="font-family:'Playfair Display',serif;font-weight:700;letter-spacing:0.5px;">
                <i class="bi bi-star-fill"></i> Veja avaliações de clientes ou envie a sua
            </a>
            <div class="small text-muted mt-2">Avaliações só ficam visíveis após aprovação do administrador.</div>
        </div>
    </footer>
</body>

</html>
