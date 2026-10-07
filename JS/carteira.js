/* ============================================================
   carteira.js – Modal de Movimentação + Categorias Dinâmicas
   ============================================================ */

// Categorias disponíveis para cada tipo de movimentação
const CATEGORIAS_POR_TIPO = {
    entrada: [
        { valor: "Salário",       label: "💰 Salário" },
        { valor: "Freelance",     label: "💻 Freelance" },
        { valor: "Investimentos", label: "📈 Investimentos" },
        { valor: "Presente",      label: "🎁 Presente" },
        { valor: "Outros",        label: "📦 Outros" }
    ],
    saida: [
        { valor: "Alimentação", label: "🍔 Alimentação" },
        { valor: "Transporte",  label: "🚗 Transporte" },
        { valor: "Moradia",     label: "🏠 Moradia" },
        { valor: "Saúde",       label: "🏥 Saúde" },
        { valor: "Educação",    label: "📚 Educação" },
        { valor: "Lazer",       label: "🎮 Lazer" },
        { valor: "Outros",      label: "📦 Outros" }
    ]
};

let modal;
let selectTipo;
let selectCategoria;

document.addEventListener("DOMContentLoaded", () => {
    modal = document.getElementById("modalMovimentacao");
    selectTipo = document.getElementById("tipoSelect");
    selectCategoria = document.getElementById("categoriaSelect");

    // Atualiza a lista de categorias sempre que o tipo mudar
    if (selectTipo) {
        selectTipo.addEventListener("change", atualizarCategorias);
    }

    // Fecha o modal clicando fora do conteúdo (no overlay escuro)
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                fecharModal();
            }
        });
    }

    // Fecha o modal com a tecla ESC
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal && modal.classList.contains("active")) {
            fecharModal();
        }
    });
});

function atualizarCategorias() {
    const tipo = selectTipo.value;

    // Limpa as opções atuais, mantendo o placeholder
    selectCategoria.innerHTML = '<option value="" selected disabled>Selecione uma categoria</option>';

    if (!tipo || !CATEGORIAS_POR_TIPO[tipo]) {
        selectCategoria.disabled = true;
        return;
    }

    selectCategoria.disabled = false;

    CATEGORIAS_POR_TIPO[tipo].forEach(cat => {
        const option = document.createElement("option");
        option.value = cat.valor;
        option.textContent = cat.label;
        selectCategoria.appendChild(option);
    });
}

function abrirModal() {
    if (!modal) return;
    modal.classList.add("active");
    document.body.style.overflow = "hidden"; // trava o scroll do fundo
}

function fecharModal() {
    if (!modal) return;
    modal.classList.remove("active");
    document.body.style.overflow = "";

    // Reseta o formulário e a categoria ao fechar
    const form = modal.querySelector("form");
    if (form) form.reset();
    if (selectCategoria) {
        selectCategoria.innerHTML = '<option value="" selected disabled>Selecione uma categoria</option>';
        selectCategoria.disabled = true;
    }
}
