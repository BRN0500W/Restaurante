<?php
// Arquivos base: conexao com o banco e cabecalho compartilhado do site.
include("includes/conexao.php");
include("includes/header.php");
?>

<main class="cardapio-page">
    <div class="container py-5">
        <!-- Cabecalho da pagina do cardapio. -->
        <div class="text-center mb-5">
            <h2 class="fw-bold cardapio-title">
                <i class="bi bi-book"></i> Nosso Cardapio
            </h2>
            <p class="text-muted">Pratos preparados com ingredientes frescos e muito carinho.</p>
            <hr class="cardapio-divider">
        </div>

        <!-- Filtros de categoria usados pelo JavaScript para mostrar/esconder pratos. -->
        <div class="d-flex justify-content-center gap-2 mb-5 flex-wrap" aria-label="Filtros do cardapio">
            <button class="btn btn-sm fw-bold filtro-btn active" type="button" data-categoria="todos">
                Todos
            </button>
            <button class="btn btn-sm fw-bold filtro-btn" type="button" data-categoria="pizza">
                Pizzas
            </button>
            <button class="btn btn-sm fw-bold filtro-btn" type="button" data-categoria="sobremesa">
                Sobremesas
            </button>
            <button class="btn btn-sm fw-bold filtro-btn" type="button" data-categoria="outros">
                Outros
            </button>
        </div>

        <div class="row g-4 align-items-start">
            <section class="col-lg-8" aria-label="Lista de pratos">
                <div class="row row-cols-1 row-cols-md-2 g-4" id="lista-pratos">
                    <?php
                    // Busca os pratos cadastrados no banco para montar os cards do cardapio.
                    $sql = "SELECT * FROM pratos ORDER BY nome";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()):
                        $nomeCategoria = strtolower($row['nome']);

                        // Define a categoria usada no filtro visual.
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

                        // Preco em formato numerico para o JS fazer calculos sem depender da moeda exibida.
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
                                <button class="btn btn-sm fw-bold text-white btn-adicionar"
                                        type="button"
                                        data-id="<?php echo (int) $row['id']; ?>"
                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                                        data-preco="<?php echo $preco; ?>"
                                        data-categoria="<?php echo $categoria; ?>">
                                    <!-- Os data-* acima levam os dados do prato para o carrinho em JS. -->
                                    <i class="bi bi-bag-plus"></i> Adicionar
                                </button>
                            </div>
                        </article>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <aside class="col-lg-4">
                <!-- Painel fixo onde o cliente revisa o pedido antes da confirmacao final. -->
                <section class="pedido-panel sticky-lg-top" id="pedido-lista" aria-labelledby="pedido-titulo">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <p class="pedido-eyebrow mb-1">Autoatendimento</p>
                            <h3 class="h5 fw-bold mb-0" id="pedido-titulo">Lista do pedido</h3>
                        </div>
                        <span class="pedido-contador" id="pedido-contador">0 itens</span>
                    </div>

                    <p class="text-muted small mb-3">
                        Revise todos os itens antes da confirmacao final.
                    </p>

                    <div class="pedido-vazio" id="pedido-vazio">
                        <i class="bi bi-basket"></i>
                        <p class="mb-0">Nenhum prato adicionado ainda.</p>
                    </div>

                    <!-- Lista preenchida dinamicamente pelo JS com os itens escolhidos. -->
                    <ul class="pedido-itens" id="pedido-itens" aria-live="polite"></ul>

                    <!-- Resumo financeiro atualizado sempre que a quantidade muda. -->
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

                    <!-- Formulario final: so confirma quando existe nome e pelo menos um item. -->
                    <form class="pedido-form" id="pedido-form">
                        <label class="form-label small fw-bold" for="cliente-nome">Nome do cliente</label>
                        <input class="form-control" id="cliente-nome" name="nome_cliente" type="text" maxlength="100" placeholder="Digite seu nome" required>

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
        </div>
    </div>
</main>

<script src="/restaurante/js/script.js"></script>

<?php include("includes/footer.php"); ?>
