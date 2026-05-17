<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include("../includes/conexao.php");

// Filtros
$busca    = trim($_GET['q']    ?? '');
$data_ini = $_GET['data_ini'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Monta query com filtros opcionais
$where = ["1=1"];
$params = [];
$types  = '';

if ($busca !== '') {
    $where[] = "(p.nome_cliente LIKE ? OR u.nome LIKE ? OR u.email LIKE ?)";
    $like = "%$busca%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}
if ($data_ini !== '') {
    $where[] = "DATE(p.data_pedido) >= ?";
    $params[] = $data_ini;
    $types .= 's';
}
if ($data_fim !== '') {
    $where[] = "DATE(p.data_pedido) <= ?";
    $params[] = $data_fim;
    $types .= 's';
}

$whereSQL = implode(' AND ', $where);

$sql = "
    SELECT
        DATE_FORMAT(p.data_pedido,'%Y-%m-%d %H:%i') AS grupo,
        MIN(p.data_pedido) AS data_pedido,
        p.nome_cliente,
        p.usuario_id,
        u.nome AS usuario_nome,
        u.email AS usuario_email,
        SUM(p.quantidade * pr.preco) AS total,
        SUM(p.quantidade) AS total_qtd,
        GROUP_CONCAT(CONCAT(p.quantidade,'× ',pr.nome) ORDER BY pr.nome SEPARATOR ' | ') AS itens
    FROM pedidos p
    JOIN pratos pr ON p.prato_id = pr.id
    LEFT JOIN usuarios u ON p.usuario_id = u.id
    WHERE $whereSQL
    GROUP BY grupo, p.nome_cliente, p.usuario_id, u.nome, u.email
    ORDER BY data_pedido DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$pedidos = $result->fetch_all(MYSQLI_ASSOC);

// Totalizadores filtrados
$total_pedidos = count($pedidos);
$faturamento   = array_sum(array_column($pedidos, 'total'));

$hoje_str = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos — Admin Sabor & Arte</title>
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
        <a href="pedidos.php" class="active"><span class="nav-icon"><i class="bi bi-receipt"></i></span> Todos os Pedidos</a>
        <a href="pratos.php"><span class="nav-icon"><i class="bi bi-egg-fried"></i></span> Gerenciar Pratos</a>
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
            <h1>Todos os Pedidos</h1>
            <p><?= $total_pedidos ?> pedido<?= $total_pedidos != 1 ? 's' : '' ?> encontrado<?= $total_pedidos != 1 ? 's' : '' ?>
               — Faturamento: <strong style="color:var(--red)">R$ <?= number_format($faturamento,2,',','.') ?></strong></p>
        </div>
    </header>

    <div class="page-content">

        <!-- FILTROS -->
        <form method="GET" style="margin-bottom:20px;">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div class="search-bar" style="flex:1;min-width:220px;">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" placeholder="Buscar por nome ou e-mail..."
                           value="<?= htmlspecialchars($busca) ?>">
                </div>
                <div class="field" style="margin:0;">
                    <label>De</label>
                    <input type="date" name="data_ini" value="<?= htmlspecialchars($data_ini) ?>"
                           style="border:1.5px solid var(--border);border-radius:9px;padding:9px 12px;font-family:inherit;font-size:.88rem;color:var(--text);">
                </div>
                <div class="field" style="margin:0;">
                    <label>Até</label>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>"
                           style="border:1.5px solid var(--border);border-radius:9px;padding:9px 12px;font-family:inherit;font-size:.88rem;color:var(--text);">
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
                <?php if ($busca || $data_ini || $data_fim): ?>
                <a href="pedidos.php" class="btn btn-outline"><i class="bi bi-x"></i> Limpar</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="panel">
            <?php if (empty($pedidos)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <p>Nenhum pedido encontrado com os filtros aplicados.</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Conta</th>
                        <th>Itens do Pedido</th>
                        <th>Qtd</th>
                        <th>Total</th>
                        <th>Data / Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $i => $ped):
                        $dt = new DateTime($ped['data_pedido']);
                        $isHoje = $dt->format('Y-m-d') === $hoje_str;
                    ?>
                    <tr class="pedido-row-group <?= $isHoje ? 'today' : '' ?>">
                        <td class="muted"><?= $total_pedidos - $i ?></td>
                        <td>
                            <div class="fw"><?= htmlspecialchars($ped['nome_cliente']) ?></div>
                            <?php if ($ped['usuario_id']): ?>
                                <div class="muted"><?= htmlspecialchars($ped['usuario_email'] ?? '') ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ped['usuario_id']): ?>
                                <span class="badge badge-success"><i class="bi bi-person-check"></i> Registrado</span>
                            <?php else: ?>
                                <span class="badge badge-amber">Visitante</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.82rem;color:var(--muted)">
                            <?= htmlspecialchars($ped['itens']) ?>
                        </td>
                        <td class="muted"><?= $ped['total_qtd'] ?>×</td>
                        <td class="fw" style="color:var(--red);white-space:nowrap">
                            R$ <?= number_format($ped['total'], 2, ',', '.') ?>
                        </td>
                        <td class="muted" style="white-space:nowrap">
                            <?php if ($isHoje): ?>
                                <span class="badge badge-red" style="margin-bottom:3px">Hoje</span><br>
                            <?php endif; ?>
                            <?= $dt->format('d/m/Y') ?> <?= $dt->format('H:i') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
