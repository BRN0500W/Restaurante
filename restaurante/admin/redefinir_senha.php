<?php
session_start();
include("../includes/conexao.php");

if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit;
}

if ($_POST) {
    $novaSenha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $email = $_SESSION['reset_email'];

    $stmt = $conn->prepare("UPDATE admin SET senha = ? WHERE email = ?");
    $stmt->bind_param("ss", $novaSenha, $email);
    $stmt->execute();

    unset($_SESSION['reset_email']);

    $sucesso = "Senha redefinida com sucesso!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nova Senha</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="login-container">
    <h2>Definir Nova Senha</h2>

    <?php if(isset($sucesso)) echo "<p class='sucesso'>$sucesso</p>"; ?>

    <?php if(!isset($sucesso)) { ?>
    <form method="POST">
        <input type="password" name="senha" placeholder="Nova senha" required>
        <button type="submit">Salvar Nova Senha</button>
    </form>
    <?php } ?>

    <a href="login.php" class="voltar">Ir para login</a>
</div>

</body>
</html>
