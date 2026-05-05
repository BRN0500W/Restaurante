<?php
session_start();
include("../includes/conexao.php");

if ($_POST) {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($senha, $admin['senha'])) {
        $_SESSION['admin'] = $usuario;
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="login-body">

<div class="login-container">
    <h2>Área Administrativa</h2>

    <?php if(isset($erro)) echo "<p class='erro'>$erro</p>"; ?>

    <form method="POST">
        <input type="text" name="usuario" placeholder="Usuário" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
    <a href="esqueci_senha.php" class="esqueci">Esqueci minha senha</a>
    <a href="../index.php" class="voltar-site">← Voltar ao Site</a>


</div>

</body>
</html>
