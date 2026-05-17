<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioLogado = isset($_SESSION['usuario_id']);
$usuarioNome = $usuarioLogado ? $_SESSION['usuario_nome'] : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante Sabor & Arte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/restaurante/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #8B0000;">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="/restaurante/index.php">
            🍽 Sabor & Arte
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/restaurante/index.php"><i class="bi bi-house-door"></i> Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/restaurante/cardapio.php"><i class="bi bi-book"></i> Cardápio</a>
                </li>
                <?php if ($usuarioLogado): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/restaurante/meus_pedidos.php"><i class="bi bi-receipt"></i> Meus Pedidos</a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($usuarioLogado): ?>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm fw-bold dropdown-toggle" style="color:#8b0000;" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars(explode(' ', $usuarioNome)[0]); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text text-muted small">
                                    <?php echo htmlspecialchars($usuarioNome); ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/restaurante/meus_pedidos.php">
                                    <i class="bi bi-receipt"></i> Meus Pedidos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="/restaurante/usuario/logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Sair
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/restaurante/usuario/login.php" class="btn btn-light btn-sm fw-bold" style="color: #8B0000;">
                        <i class="bi bi-person"></i> Entrar
                    </a>
                    <a href="/restaurante/usuario/cadastro.php" class="btn btn-outline-light btn-sm fw-bold">
                        <i class="bi bi-person-plus"></i> Cadastrar
                    </a>
                <?php endif; ?>

                <a href="/restaurante/admin/login.php" class="btn btn-sm fw-bold ms-1"
                   style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);"
                   title="Área Admin">
                    <i class="bi bi-lock"></i>
                </a>
            </div>
        </div>
    </div>
</nav>
