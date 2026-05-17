<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /restaurante/usuario/login.php");
    exit;
}

include("includes/conexao.php");
include("includes/header.php");

$usuario_id = (int) $_SESSION['usuario_id'];

// Busca os pedidos agrupados por data/pedido usando um identificador de "grupo por minuto"
$sql = "
    SELECT 
        DATE_FORMAT(p.data_pedido, '%Y-%m-%d %H:%i') AS grupo,
        MIN(p.data_pedido) AS data_pedido,
        SUM(p.quantidade * pr.preco) AS total,
        GROUP_CONCAT(
            CONCAT(p.quantidade, 'x ', pr.nome)
            ORDER BY pr.nome
            SEPARATOR ', '
        ) AS itens
    FROM pedidos p
    JOIN pratos pr ON p.prato_id = pr.id
    WHERE p.usuario_id = ?
    GROUP BY grupo
    ORDER BY data_pedido DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pedidos = $result->fetch_all(MYSQLI_ASSOC);
?>

<main class="cardapio-page">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold cardapio-title">
                <i class="bi bi-receipt"></i> Meus Pedidos
            </h2>
            <p class="text-muted">Histórico de pedidos de <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong></p>
            <hr class="cardapio-divider">
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (empty($pedidos)): ?>
                    <div class="text-center py-5">
                        <div style="font-size:3rem;">🛒</div>
                        <h5 class="mt-3 text-muted">Você ainda não fez nenhum pedido.</h5>
                        <a href="/restaurante/cardapio.php" class="btn btn-auth mt-3">
                            <i class="bi bi-book"></i> Ver Cardápio
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($pedidos as $index => $pedido):
                            $data = new DateTime($pedido['data_pedido']);
                        ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge rounded-pill mb-1" style="background:#8b0000;">
                                            Pedido #<?php echo count($pedidos) - $index; ?>
                                        </span>
                                        <div class="text-muted small">
                                            <i class="bi bi-calendar3"></i>
                                            <?php echo $data->format('d/m/Y'); ?> às
                                            <?php echo $data->format('H:i'); ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold" style="color:#8b0000;font-size:1.1rem;">
                                            R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?>
                                        </div>
                                        <span class="badge bg-success">Confirmado</span>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <p class="mb-0 small text-muted">
                                    <i class="bi bi-bag"></i>
                                    <?php echo htmlspecialchars($pedido['itens']); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="text-center mt-4">
                        <a href="/restaurante/cardapio.php" class="btn fw-bold text-white" style="background:#8b0000;">
                            <i class="bi bi-plus-circle"></i> Fazer novo pedido
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include("includes/footer.php"); ?>
