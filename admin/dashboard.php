<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include("../includes/conexao.php");

// Métricas reais do banco
$total_pratos   = $conn->query("SELECT COUNT(*) as n FROM pratos")->fetch_assoc()['n'];
$total_pedidos  = $conn->query("SELECT COUNT(DISTINCT DATE_FORMAT(data_pedido,'%Y-%m-%d %H:%i'), nome_cliente) as n FROM pedidos")->fetch_assoc()['n'];
$total_usuarios = $conn->query("SELECT COUNT(*) as n FROM usuarios")->fetch_assoc()['n'];
$faturamento    = $conn->query("SELECT COALESCE(SUM(p.quantidade * pr.preco),0) as v FROM pedidos p JOIN pratos pr ON p.prato_id = pr.id")->fetch_assoc()['v'];

// Pedidos de hoje
$hoje_str = date('Y-m-d');
$pedidos_hoje = $conn->query("SELECT COUNT(*) as n FROM pedidos WHERE DATE(data_pedido) = '$hoje_str'")->fetch_assoc()['n'];

// Últimos 6 pedidos distintos
$ultimos = $conn->query("
    SELECT nome_cliente, MAX(data_pedido) as data_pedido,
           SUM(p.quantidade * pr.preco) as total,
           COUNT(p.id) as qtd_itens
    FROM pedidos p
    JOIN pratos pr ON p.prato_id = pr.id
    GROUP BY nome_cliente, DATE_FORMAT(p.data_pedido,'%Y-%m-%d %H:%i')
    ORDER BY data_pedido DESC
    LIMIT 6
");

// Top 5 pratos mais pedidos
$top_pratos = $conn->query("
    SELECT pr.nome, SUM(p.quantidade) as total_qtd
    FROM pedidos p JOIN pratos pr ON p.prato_id = pr.id
    GROUP BY pr.id, pr.nome
    ORDER BY total_qtd DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$max_qtd = !empty($top_pratos) ? $top_pratos[0]['total_qtd'] : 1;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Admin Sabor & Arte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<!-- SIDEBAR -->
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
        <a href="dashboard.php" class="active">
            <span class="nav-icon"><i class="bi bi-speedometer2"></i></span> Dashboard
        </a>
        <a href="pedidos.php">
            <span class="nav-icon"><i class="bi bi-receipt"></i></span> Todos os Pedidos
        </a>
        <a href="pratos.php">
            <span class="nav-icon"><i class="bi bi-egg-fried"></i></span> Gerenciar Pratos
        </a>
        <div class="nav-section-label">Sistema</div>
        <a href="usuarios.php">
            <span class="nav-icon"><i class="bi bi-people"></i></span> Usuários
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a href="../index.php" target="_blank">
            <i class="bi bi-globe2"></i> Ver Site
        </a>
        <a href="logout.php" class="logout">
            <i class="bi bi-box-arrow-right"></i> Sair
        </a>
    </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-title">
            <h1>Dashboard</h1>
            <p>Visão geral do restaurante — <?= date('d/m/Y') ?></p>
        </div>
        <div class="topbar-actions">
            <a href="pratos.php?acao=novo" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Novo Prato
            </a>
        </div>
    </header>

    <div class="page-content">

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon red">🍕</div>
                <div class="stat-body">
                    <div class="stat-label">Pratos no Cardápio</div>
                    <div class="stat-value"><?= $total_pratos ?></div>
                    <div class="stat-sub">Itens cadastrados</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">📦</div>
                <div class="stat-body">
                    <div class="stat-label">Total de Pedidos</div>
                    <div class="stat-value"><?= $total_pedidos ?></div>
                    <div class="stat-sub"><span class="up"><?= $pedidos_hoje ?> hoje</span></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">👥</div>
                <div class="stat-body">
                    <div class="stat-label">Clientes Cadastrados</div>
                    <div class="stat-value"><?= $total_usuarios ?></div>
                    <div class="stat-sub">Contas ativas</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">💰</div>
                <div class="stat-body">
                    <div class="stat-label">Faturamento Total</div>
                    <div class="stat-value" style="font-size:1.4rem;">R$&nbsp;<?= number_format($faturamento, 2, ',', '.') ?></div>
                    <div class="stat-sub">Todos os pedidos</div>
                </div>
            </div>
        </div>

        <!-- GRID -->
        <div class="content-grid">

            <!-- ÚLTIMOS PEDIDOS -->
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title">Últimos Pedidos</div>
                        <div class="panel-sub">Registros mais recentes de clientes</div>
                    </div>
                    <a href="pedidos.php" class="btn btn-outline btn-sm">Ver todos</a>
                </div>
                <?php if ($ultimos->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p>Nenhum pedido ainda.</p>
                </div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Itens</th>
                            <th>Total</th>
                            <th>Quando</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $ultimos->fetch_assoc()):
                            $dt = new DateTime($r['data_pedido']);
                            $isHoje = $dt->format('Y-m-d') === $hoje_str;
                        ?>
                        <tr class="pedido-row-group <?= $isHoje ? 'today' : '' ?>">
                            <td class="fw"><?= htmlspecialchars($r['nome_cliente']) ?></td>
                            <td class="muted"><?= $r['qtd_itens'] ?> <?= $r['qtd_itens'] == 1 ? 'item' : 'itens' ?></td>
                            <td class="fw" style="color:var(--red)">R$ <?= number_format($r['total'], 2, ',', '.') ?></td>
                            <td class="muted"><?= $isHoje ? 'Hoje ' . $dt->format('H:i') : $dt->format('d/m H:i') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- TOP PRATOS -->
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title">Pratos Mais Pedidos</div>
                        <div class="panel-sub">Ranking por quantidade</div>
                    </div>
                </div>
                <?php if (empty($top_pratos)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🍽</div>
                    <p>Sem dados ainda.</p>
                </div>
                <?php else: ?>
                <div class="rank-list">
                    <?php foreach ($top_pratos as $i => $prato):
                        $pct = round(($prato['total_qtd'] / $max_qtd) * 100);
                    ?>
                    <div class="rank-item">
                        <div class="rank-num"><?= $i + 1 ?></div>
                        <div style="flex:1;min-width:0">
                            <div class="rank-name"><?= htmlspecialchars($prato['nome']) ?></div>
                            <div class="rank-bar-wrap" style="margin-top:6px">
                                <div class="rank-bar" style="width:<?= $pct ?>%"></div>
                            </div>
                        </div>
                        <div class="rank-qty"><?= $prato['total_qtd'] ?>×</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>
</html>
