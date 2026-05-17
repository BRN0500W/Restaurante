<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioLogado = isset($_SESSION['usuario_id']);
$usuarioNome   = $usuarioLogado ? $_SESSION['usuario_nome'] : '';

include("includes/conexao.php");
include("includes/header.php");

// Mensagem de boas-vindas após cadastro
$recem_cadastrado = isset($_GET['cadastro']) && $_GET['cadastro'] == '1';
?>

<main class="cardapio-page">
    <div class="container py-5">

        <?php if ($recem_cadastrado): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle"></i>
            Bem-vindo(a), <strong><?php echo htmlspecialchars($usuarioNome); ?></strong>! Conta criada com sucesso. Agora você pode fazer pedidos!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Cabeçalho -->
        <div class="text-center mb-5">
            <h2 class="fw-bold cardapio-title">
                <i class="bi bi-book"></i> Nosso Cardápio
            </h2>
            <p class="text-muted">Pratos preparados com ingredientes frescos e muito carinho.</p>
            <hr class="cardapio-divider">
        </div>

        <!-- Filtros de categoria -->
        <div class="d-flex justify-content-center gap-2 mb-5 flex-wrap" aria-label="Filtros do cardápio">
            <button class="btn btn-sm fw-bold filtro-btn active" type="button" data-categoria="todos">Todos</button>
            <button class="btn btn-sm fw-bold filtro-btn" type="button" data-categoria="pizza">Pizzas</button>
            <button class="btn btn-sm fw-bold filtro-btn" type="button" data-categoria="sobremesa">Sobremesas</button>
            <button class="btn btn-sm fw-bold filtro-btn" type="button" data-categoria="outros">Outros</button>
        </div>

        <div class="row g-4 align-items-start">
            <!-- Lista de pratos -->
            <section class="col-lg-<?php echo $usuarioLogado ? '8' : '12'; ?>" aria-label="Lista de pratos">
                <div class="row row-cols-1 row-cols-md-<?php echo $usuarioLogado ? '2' : '3'; ?> g-4" id="lista-pratos">
                    <?php
                    $sql = "SELECT * FROM pratos ORDER BY nome";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()):
                        $nomeCategoria = strtolower($row['nome']);
                        if (stripos($nomeCategoria, 'pizza') !== false && stripos($nomeCategoria, 'chocolate') !== false) {
                            $categoria = 'sobremesa';
                        } elseif (stripos($nomeCategoria, 'pizza') !== false) {
                            $categoria = 'pizza';
                        } else {
                            $categoria = 'outros';
                        }
                        $imagem = !empty($row['imagem'])
                            ? $row['imagem']
                            : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80';
                        $preco = number_format((float) $row['preco'], 2, '.', '');
                    ?>
                    <div class="col prato-item" data-categoria="<?php echo $categoria; ?>">
                        <article class="card h-100 shadow-sm border-0 menu-card">
                            <img src="<?php echo htmlspecialchars($imagem); ?>"
                                 class="card-img-top cardapio-img"
                                 alt="<?php echo htmlspecialchars($row['nome']); ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80'">

                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($row['nome']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($row['descricao']); ?></p>
                            </div>

                            <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center pb-3 gap-3">
                                <span class="fw-bold fs-5 preco-cardapio">
                                    R$ <?php echo number_format((float) $row['preco'], 2, ',', '.'); ?>
                                </span>
                                <?php if ($usuarioLogado): ?>
                                    <button class="btn btn-sm fw-bold text-white btn-adicionar"
                                            type="button"
                                            data-id="<?php echo (int) $row['id']; ?>"
                                            data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                                            data-preco="<?php echo $preco; ?>"
                                            data-categoria="<?php echo $categoria; ?>">
                                        <i class="bi bi-bag-plus"></i> Adicionar
                                    </button>
                                <?php else: ?>
                                    <a href="/restaurante/usuario/login.php"
                                       class="btn btn-sm btn-outline-secondary fw-bold"
                                       title="Faça login para pedir">
                                        <i class="bi bi-lock"></i> Login p/ pedir
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                    <?php endwhile; ?>
                </div>

                <?php if (!$usuarioLogado): ?>
                <div class="text-center mt-5 py-4 px-3" style="background:#fff3f3;border-radius:12px;border:1px dashed #8b0000;">
                    <div style="font-size:2rem;">🔒</div>
                    <h5 class="mt-2 fw-bold" style="color:#8b0000;">Quer fazer um pedido?</h5>
                    <p class="text-muted mb-3">Crie uma conta gratuita ou entre para montar seu pedido diretamente pelo cardápio.</p>
                    <a href="/restaurante/usuario/login.php" class="btn fw-bold text-white me-2" style="background:#8b0000;">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </a>
                    <a href="/restaurante/usuario/cadastro.php" class="btn btn-outline-secondary fw-bold">
                        <i class="bi bi-person-plus"></i> Criar conta grátis
                    </a>
                </div>
                <?php endif; ?>
            </section>

            <!-- Painel do pedido — apenas para usuários logados -->
            <?php if ($usuarioLogado): ?>
            <aside class="col-lg-4">
                <section class="pedido-panel sticky-lg-top" id="pedido-lista" aria-labelledby="pedido-titulo">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <p class="pedido-eyebrow mb-1">Autoatendimento</p>
                            <h3 class="h5 fw-bold mb-0" id="pedido-titulo">Lista do pedido</h3>
                        </div>
                        <span class="pedido-contador" id="pedido-contador">0 itens</span>
                    </div>

                    <p class="text-muted small mb-3">
                        Revise todos os itens antes da confirmação final.
                    </p>

                    <div class="pedido-vazio" id="pedido-vazio">
                        <i class="bi bi-basket"></i>
                        <p class="mb-0">Nenhum prato adicionado ainda.</p>
                    </div>

                    <ul class="pedido-itens" id="pedido-itens" aria-live="polite"></ul>

                    <div class="pedido-resumo" id="pedido-resumo" hidden>
                        <div class="pedido-resumo-linha">
                            <span>Subtotal</span>
                            <strong id="pedido-subtotal">R$ 0,00</strong>
                        </div>
                        <div class="pedido-resumo-linha pedido-total">
                            <span>Total</span>
                            <strong id="pedido-total">R$ 0,00</strong>
                        </div>
                    </div>

                    <form class="pedido-form" id="pedido-form">
                        <!-- Nome preenchido automaticamente com o nome do usuário logado -->
                        <input type="hidden" id="cliente-nome" name="nome_cliente"
                               value="<?php echo htmlspecialchars($usuarioNome); ?>">

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-outline-secondary w-100" type="button" id="limpar-pedido">
                                <i class="bi bi-trash3"></i> Limpar
                            </button>
                            <button class="btn btn-confirmar w-100" type="submit" id="confirmar-pedido" disabled>
                                <i class="bi bi-check2-circle"></i> Confirmar
                            </button>
                        </div>
                    </form>

                    <p class="pedido-feedback mt-3 mb-0" id="pedido-feedback" role="status"></p>
                </section>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="/restaurante/js/script.js"></script>

<?php include("includes/footer.php"); ?>
