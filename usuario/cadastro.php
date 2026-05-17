<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: /restaurante/cardapio.php");
    exit;
}

include("../includes/conexao.php");

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome  = trim($_POST["nome"]  ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirma = $_POST["confirma"] ?? "";

    if ($nome === "" || $email === "" || $senha === "") {
        $erro = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($senha !== $confirma) {
        $erro = "As senhas não coincidem.";
    } else {
        // Verifica se e-mail já existe
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $hash);
            $stmt->execute();

            // Faz login automaticamente após cadastro
            $_SESSION["usuario_id"] = $conn->insert_id;
            $_SESSION["usuario_nome"] = $nome;
            header("Location: /restaurante/cardapio.php?cadastro=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro — Sabor & Arte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/restaurante/css/style.css">
    <style>
        body { background: #f4f0eb; }
        .auth-card {
            max-width: 440px;
            margin: 60px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.10);
            padding: 40px 36px;
        }
        .auth-logo { font-size: 2rem; margin-bottom: 4px; }
        .auth-title { color: #8b0000; font-weight: 700; }
        .btn-auth {
            background-color: #8b0000;
            border-color: #8b0000;
            color: #fff;
            font-weight: 700;
        }
        .btn-auth:hover { background-color: #a71414; border-color: #a71414; color: #fff; }
        .form-control:focus {
            border-color: #8b0000;
            box-shadow: 0 0 0 0.2rem rgba(139,0,0,.15);
        }
        .link-auth { color: #8b0000; font-weight: 600; }
        .link-auth:hover { color: #a71414; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark" style="background-color: #8B0000;">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="/restaurante/index.php">🍽 Sabor & Arte</a>
        <a href="/restaurante/cardapio.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-book"></i> Ver Cardápio
        </a>
    </div>
</nav>

<div class="auth-card">
    <div class="text-center mb-4">
        <div class="auth-logo">🍕</div>
        <h2 class="auth-title">Criar conta</h2>
        <p class="text-muted small">Cadastre-se para fazer pedidos online</p>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label fw-bold small" for="nome">Nome completo</label>
            <input class="form-control" type="text" id="nome" name="nome"
                   placeholder="Seu nome" required
                   value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold small" for="email">E-mail</label>
            <input class="form-control" type="email" id="email" name="email"
                   placeholder="seu@email.com" required
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold small" for="senha">Senha</label>
            <input class="form-control" type="password" id="senha" name="senha"
                   placeholder="Mínimo 6 caracteres" required>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold small" for="confirma">Confirmar senha</label>
            <input class="form-control" type="password" id="confirma" name="confirma"
                   placeholder="Repita a senha" required>
        </div>
        <button class="btn btn-auth w-100 py-2" type="submit">
            <i class="bi bi-person-plus"></i> Criar conta
        </button>
    </form>

    <hr class="my-4">
    <p class="text-center text-muted small mb-0">
        Já tem conta?
        <a href="/restaurante/usuario/login.php" class="link-auth">Entrar</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
