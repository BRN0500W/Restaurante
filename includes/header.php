<?php
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
                <li class="nav-item">
                    <a class="nav-link" href="/restaurante/pedido.php"><i class="bi bi-bag"></i> Fazer Pedido</a>
                </li>
            </ul>
            <a href="/restaurante/admin/login.php" class="btn btn-light btn-sm fw-bold" style="color: #8B0000;">
                <i class="bi bi-lock"></i> Admin
            </a>
        </div>
    </div>
</nav>