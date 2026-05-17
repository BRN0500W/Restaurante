<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header("Content-Type: application/json; charset=utf-8");

// Apenas usuários logados podem confirmar pedidos
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["sucesso" => false, "mensagem" => "Você precisa estar logado para fazer pedidos."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["sucesso" => false, "mensagem" => "Método não permitido."]);
    exit;
}

include("includes/conexao.php");

$dados = json_decode(file_get_contents("php://input"), true);
$nomeCliente = trim($dados["nome_cliente"] ?? "");
$itens = $dados["itens"] ?? [];
$usuarioId = (int) $_SESSION['usuario_id'];

// Usa o nome da sessão se não vier no payload
if ($nomeCliente === "") {
    $nomeCliente = $_SESSION['usuario_nome'] ?? 'Cliente';
}

if (!is_array($itens) || count($itens) === 0) {
    http_response_code(400);
    echo json_encode(["sucesso" => false, "mensagem" => "Adicione pelo menos um item ao pedido."]);
    exit;
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "INSERT INTO pedidos (nome_cliente, usuario_id, prato_id, quantidade) VALUES (?, ?, ?, ?)"
    );

    foreach ($itens as $item) {
        $pratoId    = filter_var($item["id"]        ?? null, FILTER_VALIDATE_INT);
        $quantidade = filter_var($item["quantidade"] ?? null, FILTER_VALIDATE_INT);

        if (!$pratoId || !$quantidade || $quantidade <= 0) {
            throw new InvalidArgumentException("Item de pedido inválido.");
        }

        $stmt->bind_param("siii", $nomeCliente, $usuarioId, $pratoId, $quantidade);
        $stmt->execute();
    }

    $conn->commit();

    echo json_encode(["sucesso" => true, "mensagem" => "Pedido confirmado com sucesso."]);
} catch (Throwable $erro) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["sucesso" => false, "mensagem" => "Não foi possível salvar o pedido."]);
}
?>
