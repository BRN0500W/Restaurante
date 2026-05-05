function validarPedido() {
    let nome = document.getElementById("nome").value;
    
    if (nome === "") {
        alert("Digite seu nome!");
        return false;
    }
    return true;
}
