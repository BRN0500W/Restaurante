<?php
session_start();
include("../includes/conexao.php");
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$acao = $_GET['acao'] ?? 'listar';
$msg_sucesso = '';
$msg_erro = '';

/* EXCLUIR */
if ($acao === 'excluir' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    // Verifica se há pedidos com esse prato
    $check = $conn->prepare("SELECT COUNT(*) as n FROM pedidos WHERE prato_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $has_pedidos = $check->get_result()->fetch_assoc()['n'];

    if ($has_pedidos > 0) {
        $msg_erro = "Não é possível excluir: este prato possui pedidos vinculados.";
        $acao = 'listar';
    } else {
        $stmt = $conn->prepare("DELETE FROM pratos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: pratos.php?ok=excluido"); exit;
    }
}

/* INSERIR */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao === 'novo') {
    $nome     = trim($_POST['nome']     ?? '');
    $desc     = trim($_POST['descricao'] ?? '');
    $preco    = (float) str_replace(',', '.', $_POST['preco'] ?? '0');
    $imagem   = trim($_POST['imagem']   ?? '');
    $categoria = trim($_POST['categoria'] ?? '');

    if ($nome === '' || $preco <= 0) {
        $msg_erro = "Nome e preço são obrigatórios.";
    } else {
        $stmt = $conn->prepare("INSERT INTO pratos (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $nome, $desc, $preco, $imagem);
        $stmt->execute();
        header("Location: pratos.php?ok=criado"); exit;
    }
}

/* ATUALIZAR */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao === 'editar') {
    $id    = (int) $_GET['id'];
    $nome  = trim($_POST['nome']      ?? '');
    $desc  = trim($_POST['descricao'] ?? '');
    $preco = (float) str_replace(',', '.', $_POST['preco'] ?? '0');
    $img   = trim($_POST['imagem']    ?? '');

    if ($nome === '' || $preco <= 0) {
        $msg_erro = "Nome e preço são obrigatórios.";
    } else {
        $stmt = $conn->prepare("UPDATE pratos SET nome=?, descricao=?, preco=?, imagem=? WHERE id=?");
        $stmt->bind_param("ssdsi", $nome, $desc, $preco, $img, $id);
        $stmt->execute();
        header("Location: pratos.php?ok=atualizado"); exit;
    }
}

// Mensagens de retorno
$ok = $_GET['ok'] ?? '';
if ($ok === 'criado')     $msg_sucesso = "Prato criado com sucesso!";
if ($ok === 'atualizado') $msg_sucesso = "Prato atualizado com sucesso!";
if ($ok === 'excluido')   $msg_sucesso = "Prato excluído.";

// Busca prato para edição
$prato = null;
if ($acao === 'editar' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM pratos WHERE id = ?");
    $stmt->bind_param("i", (int)$_GET['id']);
    $stmt->execute();
    $prato = $stmt->get_result()->fetch_assoc();
    if (!$prato) { header("Location: pratos.php"); exit; }
}

// Lista de pratos com stats de pedidos
$lista = $conn->query("
    SELECT pr.*,
           COALESCE(SUM(p.quantidade), 0) as total_pedidos
    FROM pratos pr
    LEFT JOIN pedidos p ON p.prato_id = pr.id
    GROUP BY pr.id
    ORDER BY pr.nome
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratos — Admin Sabor & Arte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">🍽</span>
        <span class="brand-name">Sabor & Arte</span>
        <span class="brand-sub">Painel Administrativo</span>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar">👤</div>
        <div class="user-info">
            <div class="user-label">Administrador</div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['admin']) ?></div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a href="dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span> Dashboard</a>
        <a href="pedidos.php"><span class="nav-icon"><i class="bi bi-receipt"></i></span> Todos os Pedidos</a>
        <a href="pratos.php" class="active"><span class="nav-icon"><i class="bi bi-egg-fried"></i></span> Gerenciar Pratos</a>
        <div class="nav-section-label">Sistema</div>
        <a href="usuarios.php"><span class="nav-icon"><i class="bi bi-people"></i></span> Usuários</a>
    </nav>
    <div class="sidebar-bottom">
        <a href="../index.php" target="_blank"><i class="bi bi-globe2"></i> Ver Site</a>
        <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Sair</a>
    </div>
</aside>

<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-title">
            <h1>Gerenciar Pratos</h1>
            <p><?= count($lista) ?> pratos cadastrados no cardápio</p>
        </div>
        <div class="topbar-actions">
            <?php if ($acao !== 'listar'): ?>
                <a href="pratos.php" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Voltar</a>
            <?php else: ?>
                <button class="btn btn-primary" onclick="abrirModal()">
                    <i class="bi bi-plus-lg"></i> Novo Prato
                </button>
            <?php endif; ?>
        </div>
    </header>

    <div class="page-content">

        <?php if ($msg_sucesso): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= $msg_sucesso ?></div>
        <?php endif; ?>
        <?php if ($msg_erro): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= $msg_erro ?></div>
        <?php endif; ?>

        <!-- TABELA DE PRATOS -->
        <div class="panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title">Cardápio Completo</div>
                    <div class="panel-sub">Clique em editar para modificar um prato</div>
                </div>
            </div>

            <?php if (empty($lista)): ?>
            <div class="empty-state">
                <div class="empty-icon">🍽</div>
                <p>Nenhum prato cadastrado ainda.</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Prato</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Pedidos</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lista as $row):
                    $nome_l = strtolower($row['nome']);
                    if (stripos($nome_l,'pizza') !== false && stripos($nome_l,'chocolate') !== false)
                        $cat = ['label'=>'Sobremesa','class'=>'badge-amber'];
                    elseif (stripos($nome_l,'pizza') !== false)
                        $cat = ['label'=>'Pizza','class'=>'badge-red'];
                    else
                        $cat = ['label'=>'Outros','class'=>'badge-blue'];

                    $img = !empty($row['imagem']) ? $row['imagem']
                        : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=100&q=60';
                ?>
                <tr>
                    <td>
                        <div class="prato-info">
                            <img src="<?= htmlspecialchars($img) ?>"
                                 class="prato-thumb"
                                 onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=100&q=60'"
                                 alt="<?= htmlspecialchars($row['nome']) ?>">
                            <div>
                                <div class="prato-nome"><?= htmlspecialchars($row['nome']) ?></div>
                                <div class="prato-desc"><?= htmlspecialchars($row['descricao'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge <?= $cat['class'] ?>"><?= $cat['label'] ?></span></td>
                    <td class="fw" style="color:var(--red)">R$ <?= number_format((float)$row['preco'],2,',','.') ?></td>
                    <td>
                        <?php if ($row['total_pedidos'] > 0): ?>
                            <span class="badge badge-success"><?= $row['total_pedidos'] ?>×</span>
                        <?php else: ?>
                            <span class="muted" style="font-size:.8rem">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="pratos.php?acao=editar&id=<?= $row['id'] ?>"
                               class="btn btn-outline btn-sm btn-icon" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="pratos.php?acao=excluir&id=<?= $row['id'] ?>"
                               class="btn btn-danger btn-sm btn-icon"
                               title="Excluir"
                               onclick="return confirm('Excluir o prato \'<?= htmlspecialchars(addslashes($row['nome'])) ?>\'?\nEsta ação não pode ser desfeita.')">
                                <i class="bi bi-trash3"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- FORM EDITAR (inline se ação=editar) -->
        <?php if ($acao === 'editar' && $prato): ?>
        <div class="panel" style="margin-top:20px;">
            <div class="panel-header">
                <div>
                    <div class="panel-title">Editar Prato</div>
                    <div class="panel-sub">ID #<?= $prato['id'] ?></div>
                </div>
            </div>
            <div style="padding:28px;">
                <form method="POST" action="pratos.php?acao=editar&id=<?= $prato['id'] ?>">
                    <div class="form-grid">
                        <div class="field span2">
                            <label>Nome do Prato</label>
                            <input type="text" name="nome" value="<?= htmlspecialchars($prato['nome']) ?>" required>
                        </div>
                        <div class="field span2">
                            <label>Descrição</label>
                            <textarea name="descricao"><?= htmlspecialchars($prato['descricao'] ?? '') ?></textarea>
                        </div>
                        <div class="field">
                            <label>Preço (R$)</label>
                            <input type="number" step="0.01" min="0.01" name="preco"
                                   value="<?= number_format((float)$prato['preco'],2,'.','')?>" required>
                        </div>
                        <div class="field">
                            <label>URL da Imagem</label>
                            <input type="url" name="imagem" value="<?= htmlspecialchars($prato['imagem'] ?? '') ?>"
                                   placeholder="https://...">
                        </div>
                        <!-- Prévia da imagem -->
                        <div class="field span2" id="img-preview-wrap" style="<?= empty($prato['imagem']) ? 'display:none' : '' ?>">
                            <label>Prévia</label>
                            <img id="img-preview" src="<?= htmlspecialchars($prato['imagem'] ?? '') ?>"
                                 style="height:120px;border-radius:10px;object-fit:cover;border:1.5px solid var(--border);"
                                 onerror="this.parentElement.style.display='none'">
                        </div>
                    </div>
                    <div style="margin-top:20px;display:flex;gap:10px;">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> Salvar Alterações
                        </button>
                        <a href="pratos.php" class="btn btn-outline btn-lg">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- MODAL NOVO PRATO -->
<div class="modal-overlay" id="modal-novo" onclick="fecharModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-head">
            <h2><i class="bi bi-plus-circle" style="color:var(--red)"></i> Novo Prato</h2>
            <button class="btn btn-ghost btn-icon" onclick="fecharModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form method="POST" action="pratos.php?acao=novo">
            <div class="form-grid">
                <div class="field span2">
                    <label>Nome do Prato *</label>
                    <input type="text" name="nome" placeholder="Ex: Pizza Margherita" required>
                </div>
                <div class="field span2">
                    <label>Descrição</label>
                    <textarea name="descricao" placeholder="Ingredientes e detalhes..."></textarea>
                </div>
                <div class="field">
                    <label>Preço (R$) *</label>
                    <input type="number" step="0.01" min="0.01" name="preco" placeholder="0,00" required>
                </div>
                <div class="field">
                    <label>URL da Imagem</label>
                    <input type="url" name="imagem" placeholder="https://..." id="novo-imagem"
                           oninput="previewNovaImg(this.value)">
                </div>
                <div class="field span2" id="novo-preview-wrap" style="display:none;">
                    <label>Prévia</label>
                    <img id="novo-preview" style="height:100px;border-radius:10px;object-fit:cover;border:1.5px solid var(--border);">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Cadastrar Prato</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('modal-novo').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function fecharModal(e) {
    if (!e || e.target === document.getElementById('modal-novo')) {
        document.getElementById('modal-novo').classList.remove('open');
        document.body.style.overflow = '';
    }
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModal(); });

function previewNovaImg(url) {
    const wrap = document.getElementById('novo-preview-wrap');
    const img  = document.getElementById('novo-preview');
    if (url) {
        img.src = url;
        wrap.style.display = '';
        img.onerror = () => { wrap.style.display = 'none'; };
    } else {
        wrap.style.display = 'none';
    }
}

// Preview edição ao vivo
const editImg = document.querySelector('input[name="imagem"]');
if (editImg && editImg.closest('form[action*="editar"]')) {
    editImg.addEventListener('input', function() {
        const wrap = document.getElementById('img-preview-wrap');
        const prev = document.getElementById('img-preview');
        if (this.value) {
            prev.src = this.value;
            wrap.style.display = '';
            prev.onerror = () => wrap.style.display = 'none';
        } else {
            wrap.style.display = 'none';
        }
    });
}

// Abre modal se veio de erro de POST no novo
<?php if ($acao === 'novo' && $msg_erro): ?>
window.addEventListener('DOMContentLoaded', abrirModal);
<?php endif; ?>
</script>

</body>
</html>
