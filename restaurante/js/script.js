// Representa um prato vindo do cardapio e centraliza seus dados principais.
class ProdutoCardapio {
    constructor(id, nome, preco, categoria) {
        this.id = Number(id);
        this.nome = nome;
        this.preco = Number(preco);
        this.categoria = categoria;
    }

    getCategoriaLegivel() {
        // Switch usado para traduzir a categoria tecnica para o texto exibido.
        switch (this.categoria) {
            case "pizza":
                return "Pizza";
            case "sobremesa":
                return "Sobremesa";
            default:
                return "Outros";
        }
    }
}

// Herda os dados do produto e adiciona comportamento especifico do pedido.
class ItemPedido extends ProdutoCardapio {
    constructor(produto) {
        super(produto.id, produto.nome, produto.preco, produto.categoria);
        this.quantidade = 1;
    }

    alterarQuantidade(valor) {
        this.quantidade += valor;
    }

    getSubtotal() {
        return this.preco * this.quantidade;
    }

    toJSON() {
        // Envia ao PHP somente o que o banco precisa para gravar o pedido.
        return {
            id: this.id,
            quantidade: this.quantidade
        };
    }
}

document.addEventListener("DOMContentLoaded", () => {
    // Elementos principais: sem eles, a pagina atual nao precisa executar o carrinho.
    const listaPratos = document.getElementById("lista-pratos");
    const listaPedido = document.getElementById("pedido-itens");

    if (!listaPratos || !listaPedido) {
        return;
    }

    const refs = {
        botoesFiltro: document.querySelectorAll(".filtro-btn"),
        pratos: document.querySelectorAll(".prato-item"),
        vazio: document.getElementById("pedido-vazio"),
        resumo: document.getElementById("pedido-resumo"),
        subtotal: document.getElementById("pedido-subtotal"),
        total: document.getElementById("pedido-total"),
        contador: document.getElementById("pedido-contador"),
        form: document.getElementById("pedido-form"),
        nomeCliente: document.getElementById("cliente-nome"),
        limpar: document.getElementById("limpar-pedido"),
        confirmar: document.getElementById("confirmar-pedido"),
        feedback: document.getElementById("pedido-feedback")
    };

    // Array principal: guarda os objetos instanciados enquanto o cliente monta o pedido.
    const pedido = [];
    let enviandoPedido = false;

    // Mantem a exibicao de valores sempre no padrao brasileiro.
    const formatarMoeda = (valor) => valor.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });

    function configurarFiltros() {
        // Cada botao de filtro altera a categoria visivel sem recarregar a pagina.
        refs.botoesFiltro.forEach((botao) => {
            botao.addEventListener("click", () => {
                const categoriaSelecionada = botao.dataset.categoria;

                refs.botoesFiltro.forEach((item) => item.classList.remove("active"));
                botao.classList.add("active");

                refs.pratos.forEach((prato) => {
                    const deveMostrar = categoriaSelecionada === "todos" || prato.dataset.categoria === categoriaSelecionada;
                    prato.style.display = deveMostrar ? "" : "none";
                });
            });
        });
    }

    function buscarItemPedido(id) {
        // Evita duplicar o mesmo prato na lista; se ja existe, altera quantidade.
        return pedido.find((item) => item.id === Number(id));
    }

    function adicionarItem(botao) {
        // Instancia um produto a partir dos data-* gravados no HTML pelo PHP.
        const produto = new ProdutoCardapio(
            botao.dataset.id,
            botao.dataset.nome,
            botao.dataset.preco,
            botao.dataset.categoria
        );

        const itemExistente = buscarItemPedido(produto.id);

        if (itemExistente) {
            itemExistente.alterarQuantidade(1);
        } else {
            pedido.push(new ItemPedido(produto));
        }

        definirFeedback("Item adicionado a lista.");
        renderizarPedido();
    }

    function alterarItem(id, valor) {
        const item = buscarItemPedido(id);

        if (!item) {
            return;
        }

        // Valor positivo aumenta, valor negativo diminui.
        item.alterarQuantidade(valor);

        if (item.quantidade <= 0) {
            removerItem(id);
            return;
        }

        renderizarPedido();
    }

    function removerItem(id) {
        // Remove pelo indice para manter o mesmo array usado pelo restante do carrinho.
        const indice = pedido.findIndex((item) => item.id === Number(id));

        if (indice >= 0) {
            pedido.splice(indice, 1);
        }

        renderizarPedido();
    }

    function calcularResumo() {
        let quantidadeTotal = 0;
        let valorTotal = 0;

        // Aritmetica com for: soma quantidade e subtotal de todos os itens do pedido.
        for (let i = 0; i < pedido.length; i += 1) {
            quantidadeTotal += pedido[i].quantidade;
            valorTotal += pedido[i].getSubtotal();
        }

        return { quantidadeTotal, valorTotal };
    }

    function atualizarResumo() {
        const { quantidadeTotal, valorTotal } = calcularResumo();
        const temItens = pedido.length > 0;
        const nomePreenchido = refs.nomeCliente.value.trim() !== "";

        refs.vazio.hidden = temItens;
        refs.resumo.hidden = !temItens;
        refs.subtotal.textContent = formatarMoeda(valorTotal);
        refs.total.textContent = formatarMoeda(valorTotal);
        // Operador ternario para ajustar singular/plural no contador.
        refs.contador.textContent = `${quantidadeTotal} ${quantidadeTotal === 1 ? "item" : "itens"}`;
        refs.confirmar.disabled = !temItens || !nomePreenchido || enviandoPedido;
    }

    function criarBotaoQuantidade(acao, icone, rotulo) {
        // Cria botoes de controle via DOM para evitar HTML repetido em strings.
        const botao = document.createElement("button");
        const elementoIcone = document.createElement("i");

        botao.type = "button";
        botao.className = "btn btn-outline-secondary btn-sm";
        botao.dataset.action = acao;
        botao.setAttribute("aria-label", rotulo);
        elementoIcone.className = icone;

        botao.appendChild(elementoIcone);
        return botao;
    }

    function renderizarPedido() {
        // Recria a lista sempre a partir do array pedido, mantendo a tela sincronizada.
        listaPedido.innerHTML = "";

        pedido.forEach((item) => {
            const li = document.createElement("li");
            const topo = document.createElement("div");
            const dados = document.createElement("div");
            const nome = document.createElement("p");
            const categoria = document.createElement("p");
            const subtotal = document.createElement("span");
            const quantidade = document.createElement("div");
            const numeroQuantidade = document.createElement("span");
            const remover = criarBotaoQuantidade("remover", "bi bi-x-lg", "Remover item");

            li.className = "pedido-item";
            li.dataset.id = item.id;
            topo.className = "pedido-item-topo";
            nome.className = "pedido-item-nome";
            categoria.className = "pedido-item-categoria";
            subtotal.className = "pedido-item-subtotal";
            quantidade.className = "pedido-quantidade";
            remover.classList.add("btn-outline-danger", "pedido-remover");

            nome.textContent = item.nome;
            categoria.textContent = item.getCategoriaLegivel();
            subtotal.textContent = formatarMoeda(item.getSubtotal());
            numeroQuantidade.textContent = item.quantidade;

            dados.append(nome, categoria);
            topo.append(dados, subtotal);
            quantidade.append(
                criarBotaoQuantidade("decrementar", "bi bi-dash", "Diminuir quantidade"),
                numeroQuantidade,
                criarBotaoQuantidade("incrementar", "bi bi-plus", "Aumentar quantidade"),
                remover
            );
            li.append(topo, quantidade);
            listaPedido.appendChild(li);
        });

        atualizarResumo();
    }

    function definirFeedback(mensagem, tipo = "sucesso") {
        // Mensagens curtas para orientar o cliente durante o autoatendimento.
        refs.feedback.textContent = mensagem;
        refs.feedback.classList.toggle("erro", tipo === "erro");
    }

    async function confirmarPedido(event) {
        event.preventDefault();

        const nomeCliente = refs.nomeCliente.value.trim();

        if (!nomeCliente) {
            definirFeedback("Digite o nome do cliente antes de confirmar.", "erro");
            atualizarResumo();
            return;
        }

        if (pedido.length === 0) {
            definirFeedback("Adicione pelo menos um item ao pedido.", "erro");
            return;
        }

        const totalAntesDeEnviar = calcularResumo().valorTotal;
        enviandoPedido = true;
        atualizarResumo();
        definirFeedback("Enviando pedido...");

        try {
            // Envia o pedido em JSON para o PHP salvar os itens no banco.
            const resposta = await fetch("confirmar_pedido.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    nome_cliente: nomeCliente,
                    itens: pedido.map((item) => item.toJSON())
                })
            });

            const dados = await resposta.json();

            if (!resposta.ok || !dados.sucesso) {
                throw new Error(dados.mensagem || "Nao foi possivel confirmar o pedido.");
            }

            pedido.splice(0, pedido.length);
            refs.form.reset();
            renderizarPedido();
            definirFeedback(`${dados.mensagem} Total: ${formatarMoeda(totalAntesDeEnviar)}.`);
        } catch (erro) {
            definirFeedback(erro.message, "erro");
        } finally {
            enviandoPedido = false;
            atualizarResumo();
        }
    }

    listaPratos.addEventListener("click", (event) => {
        // Delegacao de evento: um unico listener atende todos os botoes Adicionar.
        const botao = event.target.closest(".btn-adicionar");

        if (botao) {
            adicionarItem(botao);
        }
    });

    listaPedido.addEventListener("click", (event) => {
        // As acoes dos botoes da lista sao decididas pelo data-action.
        const botao = event.target.closest("button[data-action]");
        const itemPedido = event.target.closest(".pedido-item");

        if (!botao || !itemPedido) {
            return;
        }

        switch (botao.dataset.action) {
            case "incrementar":
                alterarItem(itemPedido.dataset.id, 1);
                break;
            case "decrementar":
                alterarItem(itemPedido.dataset.id, -1);
                break;
            case "remover":
                removerItem(itemPedido.dataset.id);
                break;
            default:
                break;
        }
    });

    refs.limpar.addEventListener("click", () => {
        pedido.splice(0, pedido.length);
        definirFeedback("");
        renderizarPedido();
    });

    refs.nomeCliente.addEventListener("input", atualizarResumo);
    refs.form.addEventListener("submit", confirmarPedido);

    configurarFiltros();
    atualizarResumo();
});

function validarPedido() {
    // Compatibilidade com formularios antigos que ainda chamem validarPedido().
    const nome = document.getElementById("nome");

    if (!nome) {
        return true;
    }

    if (nome.value.trim() === "") {
        alert("Digite seu nome!");
        return false;
    }

    return true;
}
