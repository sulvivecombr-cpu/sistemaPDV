<?php
$page_title = 'Ponto de Venda (PDV)';
include 'includes/header.php'; // Inclui o header que já criamos
?>

<div class="container-fluid mt-3">
    <div id="app-messages" class="alert alert-dismissible fade show d-none" role="alert">
        <span id="message-content"></span>
        <button type="button" class="btn-close" aria-label="Close"></button>
    </div>
</div>

<div class="row g-4 mt-3">
    <div class="col-md-7">
        <div class="card p-4 h-100">
            <h4><i class="bi bi-search"></i> Buscar Produto</h4>
            <div class="input-group mb-3">
                <input type="text" class="form-control form-control-lg" id="busca-produto" placeholder="Digite o nome ou código de barras...">
            </div>
            <div id="resultados-busca" class="list-group">
                </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card p-4 h-100">
            <h4><i class="bi bi-cart-check"></i> Venda Atual</h4>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th style="width: 80px;">Qtd.</th>
                            <th>Preço</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="carrinho-itens">
                        </tbody>
                </table>
            </div>
            <hr>
            <div class="text-end">
                <h3>Total: R$ <span id="valor-total">0,00</span></h3>
            </div>
            <div class="d-grid mt-3">
                <button class="btn btn-custom btn-lg" id="btn-finalizar-venda"><i class="bi bi-check-circle-fill"></i> Finalizar Venda (F9)</button>
                <button class="btn btn-outline-secondary btn-sm mt-2" id="btn-limpar-carrinho"><i class="bi bi-x-circle"></i> Limpar Carrinho (F7)</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPagamentoLabel">Finalizar Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row"> 
                <div class="mb-3 col">
                    <label for="totalBruto" class="form-label fw-bold">Subtotal:</label>
                    <input type="text" class="form-control form-control-lg text-end price-color" id="totalBruto" readonly>
                </div>
                <div class="mb-3 col">
                    <label for="descontoVenda" class="form-label fw-bold">Desconto:</label>
                    <input type="number" class="form-control form-control-lg price-returned" id="descontoVenda" placeholder="0.00" step="0.01">
                </div>
                </div>
                <hr>
                <div class="mb-3">
                    <label for="totalComDesconto" class="form-label fw-bold">Total a Pagar:</label>
                    <input type="text" class="form-control form-control-lg text-end price-color" id="totalComDesconto" readonly>
                </div>
                <hr>
                <div class="row"> 
                    <div class="mb-3 col-md-5">
                         <label class="form-label fw-bold">Escolha a Forma:</label>
                         <div id="payment-options-list" class="list-group" style="max-height: 250px; overflow-y: auto;">
                             <!-- Options loaded via JS -->
                             <button type="button" class="list-group-item list-group-item-action active" data-type="money" data-provider="manual">
                                 <span class="badge bg-light text-dark me-2">1</span> Dinheiro
                             </button>
                         </div>
                    </div>
                    <div class="mb-3 col-md-7">
                        <label for="valorPagamento" class="form-label fw-bold">Valor:</label>
                        <input type="number" class="form-control form-control-lg price-color mb-3" id="valorPagamento" placeholder="0.00" step="0.01">
                        
                        <div class="d-grid gap-2">
                            <button id="btnAdicionarPagamento" class="btn btn-custom-orange"><i class="bi bi-plus-circle"></i> Adicionar (Enter)</button>
                        </div>
                    </div>
                </div>
                <!-- Removed old btnAdicionarPagamento block from here -->
                
                <h5 class="mt-4">Pagamentos Adicionados:</h5>
                <ul id="listaPagamentos" class="list-group">
                    </ul>
                    <br/>
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <label for="valorRestante" class="form-label h4 fw-bold mb-0">Restante:</label>
                        <span id="valorRestante" class="h4 fw-bold text-danger">0,00</span>
                    </div>
                <div class="mt-3">
                    <label for="trocoFinal" class="form-label fw-bold">Troco:</label>
                    <input type="text" class="form-control form-control-lg text-end price-returned" id="trocoFinal" readonly>
                </div>

                <div class="d-grid mt-3">
                    <button id="btnConfirmarPagamento" class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i> Confirmar Pagamento</button>
                </div>
                </div>
</div>

<!-- MODAL SUCESSO VENDA -->
<div class="modal fade" id="modalSucesso" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h3 class="mb-3">Venda Realizada!</h3>
                <p class="lead">ID: <span id="sucessoIdVenda"></span></p>
                <div class="d-grid gap-2 col-8 mx-auto">
                    <button class="btn btn-primary btn-lg" id="btnEmitirNFCe"><i class="bi bi-receipt"></i> Emitir NFC-e</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Nova Venda</button>
                </div>
                <div id="resultadoNFCe" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PIX Q.R. CODE -->
<div class="modal fade" id="modalPix" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <h4 class="mb-3">Pagamento via PIX</h4>
                <p>Escaneie o QR Code abaixo:</p>
                <div id="pixQrContainer" class="my-3">
                    <img id="pixQrImage" src="" alt="QR Code PIX" style="max-width: 100%; height: auto;" />
                </div>
                <div class="input-group mb-3">
                    <textarea class="form-control" id="pixCopiaCola" rows="2" readonly></textarea>
                    <button class="btn btn-outline-secondary" type="button" id="btnCopiarPix"><i class="bi bi-clipboard"></i></button>
                </div>
                <div id="statusPix" class="text-primary fw-bold mb-3">
                    <span class="spinner-border spinner-border-sm"></span> Aguardando pagamento...
                </div>
                <button type="button" class="btn btn-success me-2" id="btnConfirmarPixManual" style="display: none;">
                    <i class="bi bi-check-circle"></i> Confirmar Manualmente
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelarPix">Cancelar</button>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
<style>
    /* Estilo para a linha selecionada no carrinho */
    .carrinho-item-selecionado {
        background-color: #e0f7fa; /* Um azul claro para destaque */
        border-left: 4px solid #00bcd4; /* Uma borda para maior visibilidade */
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<script src="js/offline-manager.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function() {
    
    // Inicializa Offline Manager
    OfflineManager.init().then(() => {
        console.log("Offline System Ready");
    });
    
    // Listener para online/offline UI
    window.addEventListener('online', () => {
        showMessage('Conexão restabelecida. Sincronizando...', 'info');
        OfflineManager.syncSales();
        OfflineManager.cacheProducts(); // Atualiza cache
    });
    window.addEventListener('offline', () => {
        showMessage('Você está OFFLINE. Modo de contingência ativado.', 'warning');
    });

    const buscaInput = document.getElementById('busca-produto');
    const resultadosDiv = document.getElementById('resultados-busca');
    const carrinhoBody = document.getElementById('carrinho-itens');
    const totalSpan = document.getElementById('valor-total');
    const btnFinalizar = document.getElementById('btn-finalizar-venda');
    const btnLimparCarrinho = document.getElementById('btn-limpar-carrinho');
    const appMessages = document.getElementById('app-messages');
    const messageContent = document.getElementById('message-content');
    const messageCloseBtn = appMessages.querySelector('.btn-close');

    // Novas variáveis para o modal de pagamento parcial
    const modalPagamento = new bootstrap.Modal(document.getElementById('modalPagamento'));
    const totalBrutoInput = document.getElementById('totalBruto');
    const descontoVendaInput = document.getElementById('descontoVenda');
    const totalComDescontoInput = document.getElementById('totalComDesconto');
    const valorRestanteSpan = document.getElementById('valorRestante');
    // const formaPagamentoSelect = document.getElementById('formaPagamento'); // REMOVIDO
    const valorPagamentoInput = document.getElementById('valorPagamento');
    const btnAdicionarPagamento = document.getElementById('btnAdicionarPagamento');
    
    // STARTUP
    loadPaymentMethods();
    const listaPagamentosUl = document.getElementById('listaPagamentos');
    const trocoFinalInput = document.getElementById('trocoFinal');

    const btnConfirmarPagamento = document.getElementById('btnConfirmarPagamento');

    // Modal Sucesso e NFC-e
    const modalSucesso = new bootstrap.Modal(document.getElementById('modalSucesso'));
    const btnEmitirNFCe = document.getElementById('btnEmitirNFCe');
    const sucessoIdVendaSpan = document.getElementById('sucessoIdVenda');
    const resultadoNFCeDiv = document.getElementById('resultadoNFCe');

    let lastVendaId = null;

    // Modal PIX
    const modalPix = new bootstrap.Modal(document.getElementById('modalPix'));
    const pixQrImage = document.getElementById('pixQrImage');
    const pixCopiaCola = document.getElementById('pixCopiaCola');
    const statusPixDiv = document.getElementById('statusPix');
    const btnCancelarPix = document.getElementById('btnCancelarPix');
    const btnConfirmarPixManual = document.getElementById('btnConfirmarPixManual');
    const btnCopiarPix = document.getElementById('btnCopiarPix');
    let pixInterval = null;
    let pixPaymentId = null;
    let pixValorAtual = 0; // Store value for manual confirm
    let pixFormaAtual = '';

    let searchTimeout;
    let selectedCartItem = null;
    let lastSearchResults = [];
    let pagamentos = []; // Array para armazenar os pagamentos parciais

    // Função para exibir mensagens na interface
    function showMessage(message, type = 'success') {
        messageContent.textContent = message;
        appMessages.className = `alert alert-dismissible fade show mt-3 alert-${type}`;
        messageCloseBtn.onclick = () => appMessages.classList.add('d-none');
        setTimeout(() => {
            appMessages.classList.add('d-none');
        }, 5000); // Esconde após 5 segundos
    }

    // Função para remover a seleção de todos os itens do carrinho
    function clearCartSelection() {
        carrinhoBody.querySelectorAll('tr').forEach(row => {
            row.classList.remove('carrinho-item-selecionado');
        });
        selectedCartItem = null;
    }

    // Função para atualizar o total e o valor restante do modal
    function atualizarValoresModal() {
        const totalComDesconto = parseFloat(totalComDescontoInput.value);
        const totalPago = pagamentos.reduce((sum, p) => sum + p.valor_pago, 0);
        let restante = totalComDesconto - totalPago;
        let troco = 0;

        if (restante < 0) {
            troco = Math.abs(restante);
            restante = 0;
        }

        valorRestanteSpan.textContent = restante.toFixed(2).replace('.', ',');
        trocoFinalInput.value = troco.toFixed(2).replace('.', ',');
        btnConfirmarPagamento.disabled = restante > 0;
        
        valorPagamentoInput.value = restante > 0 ? restante.toFixed(2) : '';
        valorPagamentoInput.focus();
    }

    // FUNÇÕES JÁ EXISTENTES DO PDV
    // 1. BUSCAR PRODUTOS EM TEMPO REAL
    buscaInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const term = buscaInput.value.trim();

        if (term.length < 2) {
            resultadosDiv.innerHTML = '';
            lastSearchResults = [];
            return;
        }

        searchTimeout = setTimeout(() => {
            // TENTA ONLINE PRIMEIRO
            fetch(`actions/buscar_produtos.php?term=${term}`)
                .then(response => {
                    if (!response.ok) throw new Error("Network response was not ok");
                    return response.json();
                })
                .then(data => {
                    processarResultadosBusca(data, term);
                })
                .catch(error => {
                    console.log('Online search failed, trying offline:', error);
                    // FALLBACK OFFLINE
                    OfflineManager.searchProducts(term).then(offlineData => {
                         processarResultadosBusca(offlineData, term, true); // true = da fonte offline
                    });
                });
        }, 300);
    });
    
    // Event Delegation for search results (More robust)
    resultadosDiv.addEventListener('click', function(e) {
        const item = e.target.closest('.list-group-item');
        if (item) {
            e.preventDefault();
            try {
                const produto = JSON.parse(item.dataset.produto);
                adicionarAoCarrinho(produto);
                buscaInput.value = '';
                resultadosDiv.innerHTML = '';
                buscaInput.focus();
            } catch (err) {
                console.error("Erro ao processar produto clicado:", err);
                showMessage("Erro ao adicionar produto. Tente novamente.", "danger");
            }
        }
    });

    // Função auxiliar para renderizar busca (simplified)
    function processarResultadosBusca(data, term, isOffline = false) {
        lastSearchResults = data;
        resultadosDiv.innerHTML = '';
        if (data.length === 0) {
            resultadosDiv.innerHTML = '<div class="list-group-item">Nenhum produto encontrado' + (isOffline ? ' (Offline)' : '') + '.</div>';
        } else {
            // Auto-select if unique match
            if (data.length === 1 && data[0].codigo_barras === term) {
                 adicionarAoCarrinho(data[0]);
                 buscaInput.value = '';
                 resultadosDiv.innerHTML = '';
                 buscaInput.focus();
                 return;
            }
            
            data.forEach(produto => {
                 const item = document.createElement('a');
                 item.href = '#';
                 item.className = 'list-group-item list-group-item-action';
                 // Adiciona indicador visual se for resultado offline
                 const offlineBadge = isOffline ? ' <span class="badge bg-secondary">Offline</span>' : '';
                 
                 item.innerHTML = `${produto.nome} (R$ ${parseFloat(produto.preco_venda).toFixed(2)}) - Estoque: ${produto.quantidade_estoque}${offlineBadge}`;
                 // Store simple JSON in dataset
                 item.dataset.produto = JSON.stringify(produto);
                 resultadosDiv.appendChild(item);
            });
        }
    }

    // 2. ADICIONAR PRODUTO AO CARRINHO
    function adicionarAoCarrinho(produto) {
        const existingRow = document.querySelector(`tr[data-id="${produto.id}"]`);
        const quantidadeAtualNoCarrinho = existingRow ? parseInt(existingRow.querySelector('.qtd-item').value) : 0;

        if (quantidadeAtualNoCarrinho >= produto.quantidade_estoque) {
            showMessage(`Estoque insuficiente para ${produto.nome}. Disponível: ${produto.quantidade_estoque}`, 'warning');
            return;
        }

        if (existingRow) {
            const qtdInput = existingRow.querySelector('.qtd-item');
            qtdInput.value = parseInt(qtdInput.value) + 1;
        } else {
            const tr = document.createElement('tr');
            tr.dataset.id = produto.id;
            tr.tabIndex = 0;
            tr.innerHTML = `
                <td>${produto.nome}</td>
                <td><input type="number" class="form-control qtd-item" value="1" min="1" max="${produto.quantidade_estoque}" data-price="${produto.preco_venda}" data-max-stock="${produto.quantidade_estoque}"></td>
                <td class="preco-item">R$ ${parseFloat(produto.preco_venda).toFixed(2)}</td>
                <td><button class="btn btn-sm btn-outline-danger btn-remover"><i class="bi bi-trash"></i></button></td>
            `;
            carrinhoBody.appendChild(tr);
        }
        atualizarTotal();
        clearCartSelection();
    }

    // 3. ATUALIZAR TOTAL E REMOVER ITENS
    carrinhoBody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remover')) {
            e.target.closest('tr').remove();
            atualizarTotal();
            clearCartSelection();
        }
        else if (e.target.closest('tr')) {
            clearCartSelection();
            const clickedRow = e.target.closest('tr');
            clickedRow.classList.add('carrinho-item-selecionado');
            selectedCartItem = clickedRow;
            clickedRow.focus();
        }
    });

    carrinhoBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qtd-item')) {
            const input = e.target;
            const requestedQty = parseInt(input.value);
            const maxStock = parseInt(input.dataset.maxStock);

            if (requestedQty > maxStock) {
                input.value = maxStock;
                showMessage(`Quantidade máxima para ${input.closest('tr').querySelector('td').textContent.split('(')[0].trim()} é ${maxStock}.`, 'warning');
            } else if (requestedQty < 1) {
                input.value = 1;
            }
            atualizarTotal();
        }
    });

    // 4. FUNÇÃO PARA CALCULAR O TOTAL
    function atualizarTotal() {
        let total = 0;
        carrinhoBody.querySelectorAll('tr').forEach(row => {
            const qtd = parseInt(row.querySelector('.qtd-item').value);
            const preco = parseFloat(row.querySelector('.qtd-item').dataset.price);
            total += qtd * preco;
        });
        totalSpan.textContent = total.toFixed(2).replace('.', ',');
    }

    // 5. NOVA LÓGICA DE FINALIZAÇÃO - ABRE O MODAL
    btnFinalizar.addEventListener('click', function() {
        // CORREÇÃO AQUI: Remove "R$ " e espaços antes de fazer a conversão
        const valorTotalBruto = parseFloat(totalSpan.textContent.replace('R$', '').trim().replace(',', '.'));
        if (valorTotalBruto === 0) {
            showMessage('Adicione pelo menos um item à venda.', 'warning');
            return;
        }
        pagamentos = [];
        listaPagamentosUl.innerHTML = '';
        totalBrutoInput.value = valorTotalBruto.toFixed(2);
        descontoVendaInput.value = '';
        totalComDescontoInput.value = valorTotalBruto.toFixed(2);
        valorPagamentoInput.value = valorTotalBruto.toFixed(2);
        atualizarValoresModal();
        modalPagamento.show();
        descontoVendaInput.focus();
    });

    // 6. LÓGICA DO MODAL - CALCULA O DESCONTO
    descontoVendaInput.addEventListener('input', function() {
        const valorBruto = parseFloat(totalBrutoInput.value);
        const valorDesconto = parseFloat(descontoVendaInput.value) || 0;
        const valorFinal = valorBruto - valorDesconto;

        if (valorFinal < 0) {
            totalComDescontoInput.value = '0.00';
            showMessage('O desconto não pode ser maior que o subtotal.', 'warning');
            descontoVendaInput.value = valorBruto.toFixed(2);
        } else {
            totalComDescontoInput.value = valorFinal.toFixed(2);
        }
        
        pagamentos = [];
        listaPagamentosUl.innerHTML = '';
        valorPagamentoInput.value = valorFinal.toFixed(2);
        atualizarValoresModal();
    });

    // --- CONFIGURAÇÃO DE PAGAMENTOS DINÂMICAS ---
    let paymentTerminals = [];
    let selectedPaymentOption = null;

    function loadPaymentMethods() {
        fetch('actions/get_active_terminals.php')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    paymentTerminals = data.terminals;
                }
                renderPaymentOptions(); // Renderiza mesmo se vazio (só Manual)
            })
            .catch(err => {
                console.error('Erro ao carregar terminais:', err);
                renderPaymentOptions(); // Renderiza fallback
            });
    }

    function renderPaymentOptions() {
        const list = document.getElementById('payment-options-list');
        list.innerHTML = '';
        
        let index = 1;

        // 1. Dinheiro (Sempre Fixo)
        addPaymentOption(list, index++, 'Dinheiro', 'money', 'manual', 'manual');

        // 2. Terminais Configurados
        paymentTerminals.forEach(term => {
            if (['mercadopago', 'getnet', 'rede', 'pagseguro', 'stone'].includes(term.provider)) {
                addPaymentOption(list, index++, `${term.name} - Crédito`, 'credit_card', term.provider, term.id);
                addPaymentOption(list, index++, `${term.name} - Débito`, 'debit_card', term.provider, term.id);
                // Rede/Getnet usually handle Voucher too
                if (term.provider !== 'mercadopago') { // PIX is specific mainly to MP Point for now in this logic, but banks have it too.
                    addPaymentOption(list, index++, `${term.name} - Voucher`, 'voucher', term.provider, term.id);
                } else {
                    addPaymentOption(list, index++, `${term.name} - PIX`, 'pix', term.provider, term.id);
                }
            } else if (term.provider === 'manual') {
                // Se o user criou configurações "Manuais" customizadas (ex: "Fiado")
                addPaymentOption(list, index++, term.name, 'custom', 'manual', term.id);
            }
        });

        // 3. Fallbacks genéricos Manuais (se não tiverem cadastro específico, ou se user quiser)
        if (paymentTerminals.length === 0) {
             addPaymentOption(list, index++, 'Cartão Crédito (Manual)', 'credit_card', 'manual', 'manual');
             addPaymentOption(list, index++, 'Cartão Débito (Manual)', 'debit_card', 'manual', 'manual');
             addPaymentOption(list, index++, 'PIX (Chave/Manual)', 'pix', 'manual', 'manual');
        }

        // Seleciona o primeiro por padrão
        const first = list.querySelector('button');
        if (first) selectPaymentOption(first);
    }

    function addPaymentOption(container, idx, label, type, provider, terminalId) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'list-group-item list-group-item-action d-flex align-items-center';
        btn.dataset.index = idx; // Para atalho de teclado
        btn.dataset.type = type;
        btn.dataset.provider = provider;
        btn.dataset.terminalId = terminalId;
        btn.dataset.label = label;
        
        // Atalho visual (1..9)
        const badge = idx <= 9 ? `<span class="badge bg-secondary me-2">${idx}</span>` : '';
        
        btn.innerHTML = `${badge} ${label}`;
        
        btn.addEventListener('click', () => selectPaymentOption(btn));
        container.appendChild(btn);
    }

    function selectPaymentOption(btn) {
        document.querySelectorAll('#payment-options-list button').forEach(b => b.classList.remove('active', 'bg-primary', 'text-white'));
        btn.classList.add('active', 'bg-primary', 'text-white');
        selectedPaymentOption = {
            type: btn.dataset.type,
            provider: btn.dataset.provider,
            terminalId: btn.dataset.terminalId,
            label: btn.dataset.label
        };
        valorPagamentoInput.focus();
    }

    // LISTENER DE TECLADO MUDADO PARA O MODAL (QUANDO ABERTO)
    document.getElementById('modalPagamento').addEventListener('keydown', function(e) {
        // Se estiver num input, não intercepta numeros (exceto se for comando)
        // Mas queremos atalhos 1, 2, 3...
        
        // Se o foco estiver no input de valor, ENTER adiciona
        if (e.target === valorPagamentoInput && e.key === 'Enter') {
             e.preventDefault();
             btnAdicionarPagamento.click();
             return;
        }

        // Atalhos Numéricos (com Alt ou se foco não estiver digitando texto)
        // Vamos usar Alt+Numero para garantir ou apenas Numero se não estiver no input
        
        if (e.key >= '1' && e.key <= '9') {
             // Se estiver no input de desconto ou outro, deixa digitar
             if (e.target.tagName === 'INPUT') return;

             const idx = e.key;
             const btn = document.querySelector(`#payment-options-list button[data-index="${idx}"]`);
             if (btn) {
                 e.preventDefault();
                 selectPaymentOption(btn);
             }
        }
    });

    // 7. LÓGICA DE ADICIONAR PAGAMENTOS REFEITA
    btnAdicionarPagamento.addEventListener('click', function() {
        if (!selectedPaymentOption) {
            showMessage('Selecione uma forma de pagamento.', 'warning');
            return;
        }

        const valor = parseFloat(valorPagamentoInput.value) || 0;
        const totalComDesconto = parseFloat(totalComDescontoInput.value);
        const totalPago = pagamentos.reduce((sum, p) => sum + p.valor_pago, 0);

        if (valor <= 0) {
            showMessage('Por favor, insira um valor válido.', 'danger');
            return;
        }

        const restante = totalComDesconto - totalPago;
        // Permite troco apenas para 'money'
        if (selectedPaymentOption.type !== 'money' && valor > (restante + 0.05)) { // margem de erro float
             showMessage('O valor excede o restante a pagar.', 'warning');
             return;
        }

        // SE FOR INTEGRAÇÃO (Mercado Pago, Rede, etc)
        if (selectedPaymentOption.provider !== 'manual') {
             
             // Desabilita botão para evitar duplo clique
             const btnOriginalText = btnAdicionarPagamento.innerHTML;
             btnAdicionarPagamento.disabled = true;
             btnAdicionarPagamento.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';

             fetch('actions/process_payment_gateway.php', {
                 method: 'POST',
                 headers: {'Content-Type': 'application/json'},
                 body: JSON.stringify({
                     terminal_id: selectedPaymentOption.terminalId,
                     amount: valor,
                     type: selectedPaymentOption.type
                 })
             })
             .then(r => r.json())
             .then(data => {
                 if (data.success) {
                     // Adiciona na lista com o ID do pagamento retornado
                     // MP retorna payment_intent_id. Se for PIX, pode retornar QR code.
                     
                     // Se for PIX do MP e retornar QR Code (ex: display off), tratamos aqui?
                     // O gateway atual retorna payment_intent_id e espera status PROCESSED.
                     // Vamos implementar lógica de polling se necessário, mas o MP Point Smart processa na maquininha.
                     
                     if (data.payment_intent_id) {
                         monitorarPagamentoPoint(data.payment_intent_id, valor, selectedPaymentOption.label);
                     } else {
                         // Sucesso imediato (raro em maquininha smart, comum em TEF DLL)
                         adicionarPagamentoNaLista(selectedPaymentOption.label, valor, 'GATEWAY_' + Date.now());
                         btnAdicionarPagamento.disabled = false;
                         btnAdicionarPagamento.innerHTML = btnOriginalText;
                     }
                 } else {
                     showMessage('Erro no pagamento: ' + data.message, 'danger');
                     btnAdicionarPagamento.disabled = false;
                     btnAdicionarPagamento.innerHTML = btnOriginalText;
                 }
             })
             .catch(err => {
                 showMessage('Erro de comunicação com o servidor.', 'danger');
                 btnAdicionarPagamento.disabled = false;
                 btnAdicionarPagamento.innerHTML = btnOriginalText;
             });

             return;
        }

        // MANUAL (Dinheiro, ou cartão manual)
        adicionarPagamentoNaLista(selectedPaymentOption.label, valor);
    });

    // Função separada para adicionar pagamento na lista (reutilizada)
    function adicionarPagamentoNaLista(forma, valor, paymentId = null) {
        pagamentos.push({ 
            forma_pagamento: forma, 
            valor_pago: valor,
            payment_id: paymentId 
        });
        
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `
            ${forma}: R$ ${valor.toFixed(2).replace('.', ',')}
            <button class="btn btn-sm btn-danger btn-remover-pagamento" data-valor="${valor}" data-forma="${forma}">
                <i class="bi bi-x-lg"></i>
            </button>
        `;
        listaPagamentosUl.appendChild(li);

        valorPagamentoInput.value = '';
        atualizarValoresModal();
    }

    // 8. LÓGICA DE REMOVER PAGAMENTOS ADICIONADOS
    listaPagamentosUl.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remover-pagamento')) {
            const btn = e.target.closest('.btn-remover-pagamento');
            const valorRemover = parseFloat(btn.dataset.valor);
            const formaRemover = btn.dataset.forma;

            const index = pagamentos.findIndex(p => p.valor_pago === valorRemover && p.forma_pagamento === formaRemover);
            if (index !== -1) {
                pagamentos.splice(index, 1);
                btn.closest('li').remove();
                atualizarValoresModal();
            }
        }
    });

    // 9. FUNÇÃO QUE ENVIA A VENDA PARA O BACKEND
    btnConfirmarPagamento.addEventListener('click', function() {
        const itens = [];
        carrinhoBody.querySelectorAll('tr').forEach(row => {
            itens.push({
                id: row.dataset.id,
                quantidade: parseInt(row.querySelector('.qtd-item').value),
                preco_unitario: parseFloat(row.querySelector('.qtd-item').dataset.price)
            });
        });

        if (itens.length === 0) {
            showMessage('Adicione pelo menos um item à venda.', 'warning');
            modalPagamento.hide();
            return;
        }

        if (pagamentos.length === 0) {
             showMessage('Adicione pelo menos uma forma de pagamento.', 'warning');
             return;
        }

        const valorTotalFinal = parseFloat(totalComDescontoInput.value);
        const valorDesconto = parseFloat(descontoVendaInput.value) || 0;
        const troco = parseFloat(trocoFinalInput.value.replace(',', '.'));
        const totalPago = pagamentos.reduce((sum, p) => sum + p.valor_pago, 0);

        if (totalPago < valorTotalFinal) {
            showMessage('O total pago é menor que o valor final da venda. Adicione mais pagamentos.', 'danger');
            return;
        }

        btnConfirmarPagamento.disabled = true;
        btnConfirmarPagamento.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';

        const payload = {
            itens: itens,
            desconto: valorDesconto,
            pagamentos: pagamentos,
            valor_total: valorTotalFinal,
            troco: troco
        };

        fetch('actions/venda_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                finalizarVendaSucesso(data.id_venda);
            } else {
                showMessage('Erro ao realizar a venda: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro na requisição ou OFFLINE:', error);
            // SÓ SALVA OFFLINE SE FOR ERRO DE REDE/FETCH
            // Para simplificar, assumimos que catch aqui é rede, mas bom validar
            
            OfflineManager.saveOfflineSale(payload).then(() => {
                modalPagamento.hide();
                carrinhoBody.innerHTML = '';
                atualizarTotal();
                showMessage('Sem conexão. Venda salva OFFLINE e será enviada quando a internet voltar.', 'warning');
            });
        })
        .finally(() => {
            btnConfirmarPagamento.disabled = false;
            // btnConfirmarPagamento.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar Pagamento';
            // modalPagamento.hide(); // Movido para dentro do sucesso/catch
        });

    });

    function finalizarVendaSucesso(idVenda) {
        carrinhoBody.innerHTML = '';
        atualizarTotal();
        buscaInput.dispatchEvent(new Event('input'));
        clearCartSelection();
        
        lastVendaId = idVenda;
        sucessoIdVendaSpan.textContent = lastVendaId;
        resultadoNFCeDiv.innerHTML = '';
        btnEmitirNFCe.disabled = false;
        btnEmitirNFCe.innerHTML = '<i class="bi bi-receipt"></i> Emitir NFC-e';
        modalSucesso.show();
        
        btnConfirmarPagamento.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar Pagamento';
        modalPagamento.hide();
    }
    
    // Listener customizado para quando uma venda offline é sincronizada
    window.addEventListener('offline-synced', (e) => {
         showMessage(`Venda Offline sincronizada (ID: ${e.detail.id})`, 'success');
    });

    // 10. LIMPAR CARRINHO
    btnLimparCarrinho.addEventListener('click', function() {
        if (carrinhoBody.children.length > 0) {
            if (confirm('Tem certeza que deseja limpar o carrinho?')) {
                carrinhoBody.innerHTML = '';
                atualizarTotal();
                showMessage('Carrinho limpo.', 'info');
                clearCartSelection();
            }
        } else {
            showMessage('O carrinho já está vazio.', 'info');
        }
    });

    // 11. ATALHOS DE TECLADO GLOBAIS
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F9') {
            e.preventDefault();
            btnFinalizar.click();
        }
        else if (e.key === 'F7') {
            e.preventDefault();
            btnLimparCarrinho.click();
        }
        else if ((e.key === 'Delete' || e.key === 'Backspace') && selectedCartItem) {
            e.preventDefault();
            const removerBtn = selectedCartItem.querySelector('.btn-remover');
            if (removerBtn) {
                removerBtn.click();
                showMessage('Item removido do carrinho.', 'info');
            }
        }
    });

    // 12. ATALHO DE TECLADO PARA O CAMPO DE BUSCA (ENTER)
    // 12. ATALHO DE TECLADO PARA O CAMPO DE BUSCA (ENTER)
    buscaInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const term = buscaInput.value.trim();
            if (!term) return;

            // CANCELA busca do evento 'input' para evitar duplicação ou condições de corrida
            clearTimeout(searchTimeout);

            // Força busca imediata (Scanner rápido)
            fetch(`actions/buscar_produtos.php?term=${term}`)
                .then(response => response.json())
                .then(data => {
                    // Tenta achar match exato de código de barras
                    const matchExato = data.find(p => p.codigo_barras === term);
                    
                    if (matchExato) {
                        adicionarAoCarrinho(matchExato);
                        buscaInput.value = '';
                        resultadosDiv.innerHTML = '';
                        buscaInput.focus();
                    } 
                    else if (data.length === 1) {
                         // Se só tem 1 resultado (mesmo que nome parcial), adiciona
                         adicionarAoCarrinho(data[0]);
                         buscaInput.value = '';
                         resultadosDiv.innerHTML = '';
                         buscaInput.focus();
                    }
                    else if (data.length > 1) {
                        // Vários resultados: mostra lista
                        processarResultadosBusca(data, term);
                        buscaInput.focus(); // Mantém foco para continuar digitando se quiser
                    }
                    else {
                        // TENTA BUSCAR OFFLINE SE NÃO ACHOU ONLINE
                         OfflineManager.searchProducts(term).then(offlineData => {
                            const matchOffline = offlineData.find(p => p.codigo_barras === term);
                            if (matchOffline) {
                                adicionarAoCarrinho(matchOffline);
                                buscaInput.value = '';
                                resultadosDiv.innerHTML = '';
                                buscaInput.focus();
                            } else if (offlineData.length === 1) {
                                adicionarAoCarrinho(offlineData[0]);
                                buscaInput.value = '';
                                resultadosDiv.innerHTML = '';
                                buscaInput.focus();
                            } else if (offlineData.length > 1) {
                                processarResultadosBusca(offlineData, term, true);
                            } else {
                                showMessage('Produto não encontrado.', 'warning');
                            }
                         });
                    }
                })
                .catch(err => {
                    console.error("Erro busca Enter:", err);
                    // Fallback total offline (se fetch falhar)
                     OfflineManager.searchProducts(term).then(offlineData => {
                        if (offlineData.length === 1 || (offlineData.length > 0 && offlineData[0].codigo_barras === term)) {
                             adicionarAoCarrinho(offlineData[0]);
                             buscaInput.value = '';
                             resultadosDiv.innerHTML = '';
                        } else {
                             processarResultadosBusca(offlineData, term, true);
                        }
                     });
                });
        }
    });

    // --- INTEGRAÇÃO MERCADO PAGO POINT ---
    
    function processarPagamentoPoint(valor, type, nomeFormaOriginal) {
        
        // INTERCEPTAÇÃO PIX TELA
        if (type === 'pix') {
             // Lógica para PIX na Tela
             btnAdicionarPagamento.disabled = true;
             btnAdicionarPagamento.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Gerando PIX...';

             fetch('actions/create_pix_payment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ amount: valor })
             })
             .then(res => res.json())
             .then(data => {
                 if (data.qr_code_base64) {
                     // Abre Modal PIX
                     pixQrImage.src = 'data:image/png;base64,' + data.qr_code_base64;
                     pixCopiaCola.value = data.qr_code;
                     pixPaymentId = data.payment_id;
                     pixValorAtual = valor;
                     pixFormaAtual = nomeFormaOriginal;
                     
                     modalPix.show(); 
                     
                     // Se for Manual (Fallback)
                     if (data.manual_confirmation) {
                         statusPixDiv.innerHTML = '<span class="badge bg-warning text-dark">Modo Contingência</span><br>Confirme o recebimento no App do Banco.';
                         btnConfirmarPixManual.style.display = 'inline-block';
                         // Não inicia polling
                     } else {
                         // Automático
                         statusPixDiv.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Aguardando pagamento...';
                         btnConfirmarPixManual.style.display = 'none';
                         if (pixInterval) clearInterval(pixInterval);
                         pixInterval = setInterval(() => checkPixStatus(valor, nomeFormaOriginal), 3000);
                     }

                 } else {
                     showMessage("Erro ao gerar PIX: " + (data.error || 'Erro desconhecido'), 'danger');
                 }
             })
             .catch(err => {
                 showMessage("Erro na comunicação PIX", 'danger');
             })
             .finally(() => {
                 btnAdicionarPagamento.disabled = false;
                 btnAdicionarPagamento.innerHTML = '<i class="bi bi-plus-circle"></i> Adicionar Pagamento';
             });

             return; 
        }

        // LÓGICA PADRÃO POINT (Cartão)
        btnAdicionarPagamento.disabled = true;
        btnAdicionarPagamento.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Iniciando no Point...';

        fetch('actions/create_point_payment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ amount: valor, type: type })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                monitorarPagamentoPoint(data.payment_intent_id, valor, nomeFormaOriginal); // Passa o nome original para a lista
            } else {
                showMessage('Erro ao iniciar pagamento: ' + (data.message || 'Desconhecido'), 'danger');
                resetBtnPagamento();
            }
        })
        .catch(err => {
            console.error(err);
            showMessage('Erro de comunicação.', 'danger');
            resetBtnPagamento();
        });
    }

    function monitorarPagamentoPoint(paymentIntentId, valorOriginal, nomeFormaOriginal) {
       btnAdicionarPagamento.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Aguardando Cartão/PIX...';
       let attempts = 0;
       
       const interval = setInterval(() => {
           attempts++;
           if (attempts > 60) { // Timeout de 3 minutos aprox
               clearInterval(interval);
               showMessage('Tempo limite excedido na maquininha.', 'warning');
               resetBtnPagamento();
               return;
           }

           fetch(`actions/check_point_status.php?id=${paymentIntentId}`)
           .then(res => res.json())
           .then(data => {
               if (data.success) {
                   if (data.state === 'PROCESSED') {
                       clearInterval(interval);
                       adicionarPagamentoNaLista(nomeFormaOriginal, valorOriginal, data.payment_data ? data.payment_data.id : null);
                       resetBtnPagamento();
                       showMessage('Pagamento aprovado na maquininha!', 'success');
                   } else if (data.state === 'CANCELED' || data.state === 'ABANDONED') {
                       clearInterval(interval);
                       showMessage('Pagamento cancelado ou abandonado na maquininha.', 'warning');
                       resetBtnPagamento();
                   }
                   // Se OPEN, continua poll
               }
           })
           .catch(() => { /* ignora erros de rede no poll */ });
       }, 3000); 
    }

    function resetBtnPagamento() {
        btnAdicionarPagamento.disabled = false;
        btnAdicionarPagamento.innerHTML = '<i class="bi bi-plus-circle"></i> Adicionar Pagamento';
    }

    // 14. LOGICA EMISSAO NFC-e
    btnEmitirNFCe.addEventListener('click', function() {
        if (!lastVendaId) return;

        btnEmitirNFCe.disabled = true;
        btnEmitirNFCe.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Emitindo...';
        resultadoNFCeDiv.innerHTML = '';

        fetch('actions/emitir_nfce.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id_venda: lastVendaId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultadoNFCeDiv.innerHTML = '<div class="alert alert-success mt-2">NFC-e Emitida! XML Gerado.</div>';
                // Aqui poderia abrir o PDF ou algo assim
                console.log('XML:', data.xml);
            } else {
                resultadoNFCeDiv.innerHTML = `<div class="alert alert-danger mt-2">Erro: ${data.message}</div>`;
                btnEmitirNFCe.disabled = false;
                btnEmitirNFCe.innerHTML = '<i class="bi bi-receipt"></i> Tentar Novamente';
            }
        })
        .catch(err => {
            resultadoNFCeDiv.innerHTML = `<div class="alert alert-danger mt-2">Erro de conexão</div>`;
            btnEmitirNFCe.disabled = false;
            btnEmitirNFCe.innerHTML = '<i class="bi bi-receipt"></i> Tentar Novamente';
        });
    });

    // 15. CHECK PIX STATUS LOOP
    function checkPixStatus(valor, nomeFormaOriginal) {
        if (!pixPaymentId) return;

        fetch('actions/check_pix_status.php?id=' + pixPaymentId)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'approved') {
                clearInterval(pixInterval);
                modalPix.hide();
                statusPixDiv.innerHTML = 'Pago!';
                adicionarPagamentoNaLista(nomeFormaOriginal, valor, pixPaymentId);
                // alert('Pagamento PIX confirmado!');
                showMessage('Pagamento PIX Confirmado!', 'success');
            }
        });
    }
    
    // Botão Copiar PIX
    btnCopiarPix.addEventListener('click', () => {
        pixCopiaCola.select();
        document.execCommand('copy');
        alert('Código PIX copiado!');
    });

    // Botão Confirmar Manual (Fallback)
    btnConfirmarPixManual.addEventListener('click', () => {
        if (confirm('Você confirmou visualmente que o PIX foi pago?')) {
            modalPix.hide();
            adicionarPagamentoNaLista(pixFormaAtual, pixValorAtual, 'MANUAL_' + Date.now());
            showMessage('Pagamento PIX Confirmado Manualmente!', 'success');
        }
    });

    // Ao fechar modal PIX, para polling?
    // Se o user cancelar, ok.
    btnCancelarPix.addEventListener('click', () => {
        if (pixInterval) clearInterval(pixInterval);
    });

    atualizarTotal();
});
</script>
<?php 
include 'includes/footer.php'; // Inclui o header que já criamos

?>