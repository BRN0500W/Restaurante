<?php
// Faz o MySQLi lancar excecoes, facilitando tratar erros no bloco try/catch.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// O JavaScript espera uma resposta JSON deste arquivo.
header("Content-Type: application/json; charset=utf-8");

include("includes/conexao.php");

// Este endpoint aceita apenas pedidos enviados por POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Metodo nao permitido."
    ]);
    exit;
}

// Recebe os dados enviados pelo fetch() no script.js.
$dados = json_decode(file_get_contents("php://input"), true);
$nomeCliente = trim($dados["nome_cliente"] ?? "");
$itens = $dados["itens"] ?? [];

// Validacao inicial para impedir pedidos vazios ou sem cliente.
if ($nomeCliente === "" || !is_array($itens) || count($itens) === 0) {
    http_response_code(400);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Informe o nome do cliente e pelo menos um item."
    ]);
    exit;
}

try {
    // Transacao: se um item falhar, nenhum item do pedido fica salvo pela metade.
    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "INSERT INTO pedidos (nome_cliente, prato_id, quantidade) VALUES (?, ?, ?)"
    );

    // Cada item do carrinho vira um registro na tabela pedidos.
    foreach ($itens as $item) {
        $pratoId = filter_var($item["id"] ?? null, FILTER_VALIDATE_INT);
        $quantidade = filter_var($item["quantidade"] ?? null, FILTER_VALIDATE_INT);

        // Garante que id e quantidade sejam inteiros validos antes de gravar.
        if (!$pratoId || !$quantidade || $quantidade <= 0) {
            throw new InvalidArgumentException("Item de pedido invalido.");
        }

        $stmt->bind_param("sii", $nomeCliente, $pratoId, $quantidade);
        $stmt->execute();
    }

    $conn->commit();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Pedido confirmado com sucesso."
    ]);
} catch (Throwable $erro) {
    // Desfaz qualquer insert feito antes do erro.
    $conn->rollback();

    http_response_code(500);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Nao foi possivel salvar o pedido."
    ]);
}
?>
