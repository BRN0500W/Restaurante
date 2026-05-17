<?php
session_start();

// Se já logado, redireciona para o cardápio
if (isset($_SESSION['usuario_id'])) {
    header("Location: /restaurante/cardapio.php");
    exit;
}

include("../includes/conexao.php");

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        if ($usuario && password_verify($senha, $usuario["senha"])) {
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["usuario_nome"] = $usuario["nome"];
            header("Location: /restaurante/cardapio.php");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sabor & Arte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/restaurante/css/style.css">
    <style>
        body { background: #f4f0eb; }
        .auth-card {
            max-width: 420px;
            margin: 80px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.10);
            padding: 40px 36px;
        }
        .auth-logo {
            font-size: 2rem;
            margin-bottom: 4px;
        }
        .auth-title {
            color: #8b0000;
            font-weight: 700;
        }
        .btn-auth {
            background-color: #8b0000;
            border-color: #8b0000;
            color: #fff;
            font-weight: 700;
        }
        .btn-auth:hover {
            background-color: #a71414;
            border-color: #a71414;
            color: #fff;
        }
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
        <h2 class="auth-title">Entrar na sua conta</h2>
        <p class="text-muted small">Faça login para fazer pedidos</p>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label fw-bold small" for="email">E-mail</label>
            <input class="form-control" type="email" id="email" name="email"
                   placeholder="seu@email.com" required
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold small" for="senha">Senha</label>
            <input class="form-control" type="password" id="senha" name="senha"
                   placeholder="••••••••" required>
        </div>
        <button class="btn btn-auth w-100 py-2" type="submit">
            <i class="bi bi-box-arrow-in-right"></i> Entrar
        </button>
    </form>

    <hr class="my-4">
    <p class="text-center text-muted small mb-0">
        Não tem conta?
        <a href="/restaurante/usuario/cadastro.php" class="link-auth">Cadastre-se grátis</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
