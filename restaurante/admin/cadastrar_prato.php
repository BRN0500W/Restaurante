<?php
session_start();
include("../includes/conexao.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if ($_POST) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];

    $stmt = $conn->prepare("INSERT INTO pratos (nome, descricao, preco) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $nome, $descricao, $preco);
    $stmt->execute();

    $sucesso = "Prato cadastrado com sucesso!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Prato</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="admin-container">
    <h2>Cadastrar Novo Prato</h2>

    <?php if(isset($sucesso)) echo "<p class='sucesso'>$sucesso</p>"; ?>

    <form method="POST">
        <input type="text" name="nome" placeholder="Nome do prato" required>
        <textarea name="descricao" placeholder="Descrição" required></textarea>
        <input type="number" step="0.01" name="preco" placeholder="Preço" required>
        <button type="submit">Cadastrar</button>
    </form>

    <a href="dashboard.php" class="voltar">← Voltar</a>
</div>

</body>
</html>
