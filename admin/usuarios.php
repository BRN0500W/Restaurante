<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include("../includes/conexao.php");

$usuarios = $conn->query("
    SELECT u.*,
           COUNT(DISTINCT DATE_FORMAT(p.data_pedido,'%Y-%m-%d %H:%i')) as total_pedidos,
           COALESCE(SUM(p.quantidade * pr.preco), 0) as total_gasto
    FROM usuarios u
    LEFT JOIN pedidos p ON p.usuario_id = u.id
    LEFT JOIN pratos pr ON pr.id = p.prato_id
    GROUP BY u.id
    ORDER BY u.criado_em DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários — Admin Sabor & Arte</title>
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
        <a href="pratos.php"><span class="nav-icon"><i class="bi bi-egg-fried"></i></span> Gerenciar Pratos</a>
        <div class="nav-section-label">Sistema</div>
        <a href="usuarios.php" class="active"><span class="nav-icon"><i class="bi bi-people"></i></span> Usuários</a>
    </nav>
    <div class="sidebar-bottom">
        <a href="../index.php" target="_blank"><i class="bi bi-globe2"></i> Ver Site</a>
        <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Sair</a>
    </div>
</aside>

<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-title">
            <h1>Usuários Cadastrados</h1>
            <p><?= count($usuarios) ?> cliente<?= count($usuarios) != 1 ? 's' : '' ?> registrado<?= count($usuarios) != 1 ? 's' : '' ?></p>
        </div>
    </header>

    <div class="page-content">
        <div class="panel">
            <?php if (empty($usuarios)): ?>
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <p>Nenhum usuário cadastrado ainda.</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Pedidos</th>
                        <th>Total Gasto</th>
                        <th>Cadastro</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u):
                    $dt = new DateTime($u['criado_em']);
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:var(--red-dim);color:var(--red);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;flex-shrink:0;">
                                <?= mb_strtoupper(mb_substr($u['nome'],0,1)) ?>
                            </div>
                            <span class="fw"><?= htmlspecialchars($u['nome']) ?></span>
                        </div>
                    </td>
                    <td class="muted"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?php if ($u['total_pedidos'] > 0): ?>
                            <span class="badge badge-success"><?= $u['total_pedidos'] ?> pedido<?= $u['total_pedidos'] != 1 ? 's' : '' ?></span>
                        <?php else: ?>
                            <span class="badge badge-amber">Sem pedidos</span>
                        <?php endif; ?>
                    </td>
                    <td class="fw" style="color:var(--red)">
                        <?= $u['total_gasto'] > 0 ? 'R$ ' . number_format($u['total_gasto'],2,',','.') : '—' ?>
                    </td>
                    <td class="muted"><?= $dt->format('d/m/Y') ?></td>
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
