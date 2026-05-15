<?php
session_start();
include("../includes/conexao.php");

if ($_POST) {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['reset_email'] = $email;
        header("Location: redefinir_senha.php");
        exit;
    } else {
        $erro = "Email não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="login-container">
    <h2>Recuperar Senha</h2>

    <?php if(isset($erro)) echo "<p class='erro'>$erro</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Digite seu email" required>
        <button type="submit">Continuar</button>
    </form>

    <a href="login.php" class="voltar">← Voltar ao login</a>
</div>

</body>
</html>
