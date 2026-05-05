<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="container">

    <aside class="sidebar">
        <h2>Painel Admin</h2>
        <p>👤 <?php echo $_SESSION['admin']; ?></p>

        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="pratos.php">🍽 Gerenciar Pratos</a>
            <a href="#">📦 Pedidos</a>
            <a href="logout.php" class="logout">🚪 Sair</a>
            <a href="../index.php">🌐 Ver Site</a>

        </nav>
    </aside>

    <main class="main-content">
        <h1>Bem-vindo ao Sistema 👋</h1>

        <div class="cards">

            <div class="card">
                <h3>🍽 Pratos</h3>
                <p>Gerencie os pratos cadastrados</p>
            </div>

            <div class="card">
                <h3>📦 Pedidos</h3>
                <p>Visualize os pedidos realizados</p>
            </div>

            <div class="card">
                <h3>⚙ Configurações</h3>
                <p>Gerencie informações do sistema</p>
            </div>

        </div>
    </main>

</div>

</body>
</html>
