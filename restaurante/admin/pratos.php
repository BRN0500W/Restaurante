<?php
session_start();
include("../includes/conexao.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$acao = $_GET['acao'] ?? 'listar';

/* EXCLUIR */
if ($acao == "excluir" && isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM pratos WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    header("Location: pratos.php");
    exit;
}

/* INSERIR */
if ($_POST && $acao == "novo") {
    $stmt = $conn->prepare("INSERT INTO pratos (nome, descricao, preco) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $_POST['nome'], $_POST['descricao'], $_POST['preco']);
    $stmt->execute();
    header("Location: pratos.php");
    exit;
}

/* ATUALIZAR */
if ($_POST && $acao == "editar") {
    $stmt = $conn->prepare("UPDATE pratos SET nome=?, descricao=?, preco=? WHERE id=?");
    $stmt->bind_param("ssdi", $_POST['nome'], $_POST['descricao'], $_POST['preco'], $_GET['id']);
    $stmt->execute();
    header("Location: pratos.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Pratos</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="container">


<aside class="sidebar">
    <h2>Painel Admin</h2>
    <p>👤 <?= $_SESSION['admin']; ?></p>

    <nav>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="pratos.php">🍽 Gerenciar Pratos</a>
        <a href="../index.php">🌐 Ver Site</a>
        <a href="logout.php" class="logout">🚪 Sair</a>
    </nav>
</aside>

<main class="main-content">

<h1>Gerenciar Pratos</h1>

<?php if ($acao == "novo") { ?>

    <a href="pratos.php" class="btn-novo">← Voltar</a>

    <form method="POST" class="form-admin">
        <input type="text" name="nome" placeholder="Nome do prato" required>
        <textarea name="descricao" placeholder="Descrição" required></textarea>
        <input type="number" step="0.01" name="preco" placeholder="Preço" required>
        <button type="submit">Cadastrar</button>
    </form>

<?php } elseif ($acao == "editar") {

    $stmt = $conn->prepare("SELECT * FROM pratos WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $prato = $stmt->get_result()->fetch_assoc();
?>

    <a href="pratos.php" class="btn-novo">← Voltar</a>

    <form method="POST" class="form-admin">
        <input type="text" name="nome" value="<?= $prato['nome'] ?>" required>
        <textarea name="descricao" required><?= $prato['descricao'] ?></textarea>
        <input type="number" step="0.01" name="preco" value="<?= $prato['preco'] ?>" required>
        <button type="submit">Atualizar</button>
    </form>

<?php } else {

    $result = $conn->query("SELECT * FROM pratos");
?>

<a href="pratos.php?acao=novo" class="btn-novo">➕ Novo Prato</a>

<table class="tabela-admin">
<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Preço</th>
    <th>Ações</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['nome'] ?></td>
    <td>R$ <?= number_format($row['preco'], 2, ',', '.') ?></td>
    <td>
        <a href="pratos.php?acao=editar&id=<?= $row['id'] ?>">✏</a>
        <a href="pratos.php?acao=excluir&id=<?= $row['id'] ?>" onclick="return confirm('Excluir este prato?')">🗑</a>
    </td>
</tr>
<?php } ?>

</table>

<?php } ?>

</main>
</div>

</body>
</html>
