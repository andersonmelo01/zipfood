<?php

require_once __DIR__ . '/conexao.php';
require_admin();

$where = [];
$params = [];

if (!empty($_GET['busca'])) {
    $where[] = "(nome LIKE :busca OR codigo_produto LIKE :busca OR codigo_barras LIKE :busca)";
    $params[':busca'] = '%' . $_GET['busca'] . '%';
}

if (!empty($_GET['categoria'])) {
    $where[] = "categoria LIKE :categoria";
    $params[':categoria'] = '%' . $_GET['categoria'] . '%';
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where[] = "disponivel = :status";
    $params[':status'] = $_GET['status'];
}

$sql = "SELECT * FROM produtos";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-2">
                <img src="img/logo-zipfood.svg" alt="ZipFood" style="height:32px;width:auto;">
                <h2 class="fw-bold mb-0">🍔 Painel de Produtos</h2>
            </div>
            <a href="dashboard.php" class="btn btn-dark">← Voltar ao Dashboard</a>
        </div>

        <!-- NOVO PRODUTO -->
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-success text-white fw-bold">
                ➕ Novo Produto
            </div>
            <div class="card-body">
                <form action="salvar.php" method="POST">
                    <div class="row g-2">

                        <div class="col-md-3">
                            <input type="text" name="codigo_produto" class="form-control" placeholder="Código Produto" required>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="codigo_barras" id="codigo_barras"
                                    class="form-control" placeholder="Código Barras" required>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="gerarCodigoBarras()">Gerar</button>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <input type="number" step="0.01" min="0" name="preco_custo" class="form-control" placeholder="Preço Custo" required>
                        </div>

                        <div class="col-md-3">
                            <input type="text" name="un" class="form-control" placeholder="UN" maxlength="3" required>
                        </div>

                        <hr>

                        <div class="col-md-4">
                            <input type="text" name="nome" class="form-control" placeholder="Nome do Produto" required>
                        </div>

                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0" name="preco" class="form-control" placeholder="Preço Venda" required>
                        </div>

                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0" name="preco_promocional" class="form-control" placeholder="Preço Promoção">
                        </div>

                        <div class="col-md-2 d-flex align-items-center">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="promo_ativa">
                                <label class="form-check-label fw-bold">Em promoção</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="imagem" id="imagemInput" class="form-control" placeholder="URL da imagem" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="abrirGaleria()">Ver</button>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <input list="categorias" name="categoria" class="form-control" placeholder="Categoria" required>
                            <datalist id="categorias">
                                <?php
                                $cats = [];
                                foreach ($produtos as $p) {
                                    if (!in_array($p['categoria'], $cats)) {
                                        $cats[] = $p['categoria'];
                                    }
                                }
                                foreach ($cats as $c) {
                                    echo "<option value=\"$c\">";
                                }
                                ?>
                            </datalist>
                        </div>

                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="disponivel" checked>
                                <label class="form-check-label fw-bold">Disponível</label>
                            </div>
                        </div>

                        <hr>

                        <h5 class="fw-bold mt-3">💰 Impostos</h5>

                        <div class="row g-2">
                            <div class="col-md-2"><input type="text" name="ncm" value="0" class="form-control" placeholder="NCM" required></div>
                            <div class="col-md-2"><input type="text" name="cfop" value="0" class="form-control" placeholder="CFOP" required></div>
                            <div class="col-md-2"><input type="text" name="cst_icms" value="0" class="form-control" placeholder="CST ICMS" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" name="aliquota_icms" value="0.0" class="form-control" placeholder="% ICMS" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" name="aliquota_pis" value="0.0" class="form-control" placeholder="% PIS" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" name="aliquota_cofins" value="0.0" class="form-control" placeholder="% COFINS" required></div>
                        </div>

                    </div>
                    <button class="btn btn-success mt-3">Salvar Produto</button>
                </form>
            </div>
        </div>

        <!-- FILTRO DE PRODUTOS -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="busca" class="form-control"
                    placeholder="Buscar por nome ou código..."
                    value="<?= $_GET['busca'] ?? '' ?>">
            </div>

            <div class="col-md-3">
                <input type="text" name="categoria" class="form-control"
                    placeholder="Filtrar por categoria"
                    value="<?= $_GET['categoria'] ?? '' ?>">
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos Status</option>
                    <option value="1" <?= (($_GET['status'] ?? '') === '1') ? 'selected' : '' ?>>Disponível</option>
                    <option value="0" <?= (($_GET['status'] ?? '') === '0') ? 'selected' : '' ?>>Esgotado</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary w-100">🔍 Filtrar</button>
                <a href="painel.php" class="btn btn-secondary w-100">Limpar</a>
            </div>
        </form>

        <!-- LISTA -->
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white fw-bold">📦 Produtos</div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cód.</th>
                                <th>Barras</th>
                                <th>UN</th>
                                <th>Custo</th>
                                <th>Imagem</th>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Categoria</th>
                                <th>Promoção</th>
                                <th>Status</th>
                                <th width="220">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $p): ?>
                                <tr>
                            <td><?= e($p['codigo_produto']) ?></td>
                            <td><?= e($p['codigo_barras']) ?></td>
                            <td><?= e($p['un']) ?></td>
                            <td>R$ <?= money($p['preco_custo']) ?></td>
                            <td><img src="<?= e($p['imagem']) ?>" style="height:60px;object-fit:cover" alt="<?= e($p['nome']) ?>"></td>
                            <td><?= e($p['nome']) ?></td>
                            <td>R$ <?= money($p['preco']) ?></td>
                            <td><?= e($p['categoria']) ?></td>
                            <td>
                                <?php if (!empty($p['promo_ativa'])): ?>
                                    <span class="badge bg-danger">Promoção</span>
                                    <div class="small text-muted">R$ <?= money($p['preco_promocional'] ?? 0) ?></div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Normal</span>
                                <?php endif; ?>
                            </td>
                                    <td>
                                <?= $p['disponivel'] ? '<span class="badge bg-success">Disponível</span>' : '<span class="badge bg-danger">Esgotado</span>' ?>
                                    </td>
                                    <td class="d-flex gap-2">
                                        <button class="btn btn-warning btn-sm"
                                            onclick='abrirModalEdicao(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                            Editar</button>
                                        <a href="salvar.php?remover=<?= $p['id'] ?>" class="btn btn-danger btn-sm">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    <!-- MODAL EDITAR -->
    <div class="modal fade" id="modalEditarProduto">
        <div class="modal-dialog modal-xl">
            <form class="modal-content" method="POST" action="salvar.php">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">✏️ Editar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="row g-2">
                        <div class="col-md-3"><input type="text" name="codigo_produto" id="edit_codigo_produto" class="form-control" required></div>
                        <div class="col-md-3"><input type="text" name="codigo_barras" id="edit_codigo_barras" class="form-control" required></div>
                        <div class="col-md-3"><input type="number" step="0.01" name="preco_custo" id="edit_preco_custo" class="form-control" required></div>
                        <div class="col-md-3"><input type="text" name="un" id="edit_un" class="form-control" required></div>

                        <div class="col-md-6"><input type="text" name="nome" id="edit_nome" class="form-control" required></div>
                        <div class="col-md-3"><input type="number" step="0.01" name="preco" id="edit_preco" class="form-control" required></div>
                        <div class="col-md-3"><input type="number" step="0.01" min="0" name="preco_promocional" id="edit_preco_promocional" class="form-control" placeholder="Preço Promoção"></div>
                        <div class="col-md-3"><input type="text" name="categoria" id="edit_categoria" class="form-control" required></div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="promo_ativa" id="edit_promo_ativa">
                                <label class="form-check-label">Em promoção</label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <input type="text" name="imagem" id="edit_imagem" class="form-control" readonly>
                                <button type="button" class="btn btn-secondary" onclick="abrirGaleria()">Galeria</button>
                            </div>
                            <img id="previewEditImagem" style="max-height:80px;display:none" class="mt-2">
                        </div>

                        <div class="col-md-3">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="disponivel" id="edit_disponivel">
                                <label class="form-check-label">Disponível</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">💾 Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL GALERIA -->
    <div class="modal fade" id="modalGaleria" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecionar Imagem</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Lista de imagens com scroll -->
                    <div id="listaImagens" class="row g-3 mb-3"
                        style="max-height: 50vh; overflow-y: auto;"></div>

                    <hr>

                    <!-- UPLOAD DE IMAGEM -->
                    <h6 class="fw-bold">📤 Enviar nova imagem</h6>
                    <form id="formUploadImagem" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="file" name="imagem" class="form-control" required>
                            <button class="btn btn-success">Enviar</button>
                        </div>
                    </form>

                    <div id="statusUpload" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function abrirModalEdicao(produto) {
            edit_id.value = produto.id;
            edit_codigo_produto.value = produto.codigo_produto || '';
            edit_codigo_barras.value = produto.codigo_barras || '';
            edit_preco_custo.value = produto.preco_custo || 0;
            edit_un.value = produto.un || '';
            edit_nome.value = produto.nome || '';
            edit_preco.value = produto.preco || 0;
            edit_categoria.value = produto.categoria || '';
            edit_preco_promocional.value = produto.preco_promocional || 0;
            edit_promo_ativa.checked = produto.promo_ativa == 1;
            edit_imagem.value = produto.imagem || '';
            edit_disponivel.checked = produto.disponivel == 1;

            if (produto.imagem) {
                previewEditImagem.src = produto.imagem;
                previewEditImagem.style.display = 'block';
            }

            new bootstrap.Modal(modalEditarProduto).show();
        }

        function abrirGaleria() {
            carregarImagens();
            new bootstrap.Modal(document.getElementById('modalGaleria')).show();
        }

        async function carregarImagens() {
            try {
                let res = await fetch('listar_imagens.php');
                let imagens = await res.json();

                let html = '';
                imagens.forEach(img => {
                    html += `
            <div class="col-md-3 text-center">
                <img src="${img}" class="img-fluid rounded shadow"
                    style="height:120px; object-fit:cover; cursor:pointer"
                    onclick="selecionarImagem('${img}')">
            </div>`;
                });

                document.getElementById('listaImagens').innerHTML = html;
            } catch (e) {
                console.error('Erro ao carregar imagens', e);
            }
        }

        // Selecionar imagem para formulário principal
        function selecionarImagem(url) {
            // novo produto
            let input = document.getElementById('imagemInput');
            if (input) input.value = url;

            // modal edição
            let inputEdit = document.getElementById('edit_imagem');
            if (inputEdit) {
                inputEdit.value = url;
                document.getElementById('previewEditImagem').src = url;
                document.getElementById('previewEditImagem').style.display = 'block';
            }

            bootstrap.Modal.getInstance(document.getElementById('modalGaleria')).hide();
        }

        // Upload de imagem
        document.addEventListener('submit', async function(e) {
            if (e.target && e.target.id === 'formUploadImagem') {
                e.preventDefault();

                let formData = new FormData(e.target);
                let status = document.getElementById('statusUpload');
                status.innerHTML = 'Enviando...';

                try {
                    let res = await fetch('upload_imagem.php', {
                        method: 'POST',
                        body: formData
                    });

                    let data = await res.json();

                    if (data.url) {
                        status.innerHTML = '<span class="text-success">Imagem enviada!</span>';
                        carregarImagens();
                    } else {
                        status.innerHTML = '<span class="text-danger">Erro ao enviar</span>';
                    }
                } catch (err) {
                    status.innerHTML = '<span class="text-danger">Falha no upload</span>';
                    console.error(err);
                }
            }
        });
    </script>
    <!-- gerar código barras-->
    <script>
        async function gerarCodigoBarras() {
            try {
                let res = await fetch('gerar_codigo_barras.php');
                let data = await res.json();

                if (data.codigo) {
                    document.getElementById('codigo_barras').value = data.codigo;
                } else {
                    alert('Erro ao gerar código de barras');
                }
            } catch (e) {
                alert('Erro ao gerar código');
                console.error(e);
            }
        }
    </script>

</body>

</html>
