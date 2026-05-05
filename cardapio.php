<?php 
include("includes/conexao.php");
include("includes/header.php");
?>

<main>
<div class="container py-5">

    <div class="text-center mb-5">
        <h2 class="fw-bold" style="color: #8B0000;">
            <i class="bi bi-book"></i> Nosso Cardápio
        </h2>
        <p class="text-muted">Pratos preparados com ingredientes frescos e muito carinho</p>
        <hr style="border-color: #8B0000; width: 60px; border-width: 2px; margin: 0 auto;">
    </div>

    <div class="d-flex justify-content-center gap-2 mb-5 flex-wrap">
        <button class="btn btn-sm fw-bold filtro-btn" data-categoria="todos"
                style="background-color:#8B0000; color:white; border-radius:20px; border:2px solid #8B0000;">
            🍽 Todos
        </button>
        <button class="btn btn-sm fw-bold filtro-btn" data-categoria="pizza"
                style="background:transparent; border:2px solid #8B0000; color:#8B0000; border-radius:20px;">
            🍕 Pizzas
        </button>
        <button class="btn btn-sm fw-bold filtro-btn" data-categoria="sobremesa"
                style="background:transparent; border:2px solid #8B0000; color:#8B0000; border-radius:20px;">
            🍫 Sobremesas
        </button>
        <button class="btn btn-sm fw-bold filtro-btn" data-categoria="outros"
                style="background:transparent; border:2px solid #8B0000; color:#8B0000; border-radius:20px;">
            🥗 Outros
        </button>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="lista-pratos">
        <?php
        $sql = "SELECT * FROM pratos ORDER BY nome";
        $result = $conn->query($sql);

        while($row = $result->fetch_assoc()):
            $nome = strtolower($row['nome']);
            if (stripos($nome, 'pizza') !== false && stripos($nome, 'chocolate') !== false) {
                $categoria = 'sobremesa';
            } elseif (stripos($nome, 'pizza') !== false) {
                $categoria = 'pizza';
            } else {
                $categoria = 'outros';
            }
            $imagem = !empty($row['imagem'])
                ? $row['imagem']
                : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80';
        ?>
        <div class="col prato-item" data-categoria="<?php echo $categoria; ?>">
            <div class="card h-100 shadow-sm border-0">
                <img src="<?php echo htmlspecialchars($imagem); ?>"
                     class="card-img-top"
                     alt="<?php echo htmlspecialchars($row['nome']); ?>"
                     style="height: 200px; object-fit: cover;"
                     onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80'">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($row['nome']); ?></h5>
                    <p class="card-text text-muted small"><?php echo htmlspecialchars($row['descricao']); ?></p>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center pb-3">
                    <span class="fw-bold fs-5" style="color: #8B0000;">
                        R$ <?php echo number_format($row['preco'], 2, ',', '.'); ?>
                    </span>
                    <a href="pedido.php" class="btn btn-sm fw-bold text-white" style="background-color:#8B0000;">
                        <i class="bi bi-bag-plus"></i> Pedir
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

</div>
</main>

<script>
document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filtro-btn').forEach(b => {
            b.style.backgroundColor = 'transparent';
            b.style.color = '#8B0000';
        });
        this.style.backgroundColor = '#8B0000';
        this.style.color = 'white';

        const cat = this.dataset.categoria;
        document.querySelectorAll('.prato-item').forEach(item => {
            item.style.display =
                (cat === 'todos' || item.dataset.categoria === cat) ? '' : 'none';
        });
    });
});
</script>

<?php include("includes/footer.php"); ?>
</body>
</html>