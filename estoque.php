<?php
$page_title = 'Gestão de Estoque';
include 'includes/header.php'; // Certifique-se de que este header inclui o CSS do Bootstrap

// Lógica para buscar produtos do banco
try {
    $stmt = $pdo->query("SELECT id, codigo_barras, nome, descricao, preco_venda, quantidade_estoque, ativo FROM produtos ORDER BY nome");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $produtos = [];
    echo '<div class="alert alert-danger" role="alert">Erro ao carregar produtos: ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid mt-3">
    <!-- Área para exibir mensagens de sucesso/erro (reutilizada do PDV) -->
    <div id="app-messages" class="alert alert-dismissible fade show d-none" role="alert">
        <span id="message-content"></span>
        <button type="button" class="btn-close" aria-label="Close"></button>
    </div>
</div>

<div class="card p-4 mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="bi bi-box-seam"></i> Gestão de Estoque</h3>
        <!-- Botão para adicionar NOVO produto -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#produtoModal" id="btn-add-novo-produto">
            <i class="bi bi-plus-circle"></i> Adicionar Novo Produto
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Cód. Barras</th>
                    <th>Nome</th>
                    <th>Preço (R$)</th>
                    <th>Estoque</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Nenhum produto cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($produtos as $produto): ?>
                    <tr data-id="<?php echo $produto['id']; ?>">
                        <td><?php echo htmlspecialchars($produto['codigo_barras']); ?></td>
                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                        <td><?php echo number_format($produto['preco_venda'], 2, ',', '.'); ?></td>
                        <td><?php echo $produto['quantidade_estoque']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $produto['ativo'] ? 'success' : 'danger'; ?>">
                                <?php echo $produto['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </td>
                        <td>
                            <!-- Botão para editar produto existente -->
                            <button class="btn btn-sm btn-outline-secondary btn-editar-produto"
                                data-bs-toggle="modal" data-bs-target="#produtoModal"
                                data-id="<?php echo $produto['id']; ?>"
                                data-codigo_barras="<?php echo htmlspecialchars($produto['codigo_barras']); ?>"
                                data-nome="<?php echo htmlspecialchars($produto['nome']); ?>"
                                data-descricao="<?php echo htmlspecialchars($produto['descricao'] ?? ''); ?>"
                                data-preco_venda="<?php echo htmlspecialchars($produto['preco_venda']); ?>"
                                data-quantidade_estoque="<?php echo htmlspecialchars($produto['quantidade_estoque']); ?>"
                                data-ativo="<?php echo $produto['ativo'] ? '1' : '0'; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <!-- Botão para adicionar estoque a um produto existente -->
                            <button class="btn btn-sm btn-outline-info btn-add-estoque"
                                data-bs-toggle="modal" data-bs-target="#addEstoqueModal"
                                data-id="<?php echo $produto['id']; ?>"
                                data-nome="<?php echo htmlspecialchars($produto['nome']); ?>">
                                <i class="bi bi-box-seam-fill"></i>
                            </button>
                            <!-- Botão para (des)ativar produto -->
                            <button class="btn btn-sm btn-outline-<?php echo $produto['ativo'] ? 'danger' : 'success'; ?> btn-toggle-ativo"
                                data-id="<?php echo $produto['id']; ?>"
                                data-ativo="<?php echo $produto['ativo'] ? '1' : '0'; ?>">
                                <i class="bi bi-power"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Adicionar/Editar Produto -->
<div class="modal fade" id="produtoModal" tabindex="-1" aria-labelledby="produtoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-produto">
                <div class="modal-header">
                    <h5 class="modal-title" id="produtoModalLabel">Adicionar/Editar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="produto-id" name="id">
                    <div class="mb-3">
                        <label for="codigo_barras" class="form-label">Código de Barras</label>
                        <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" required>
                    </div>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="preco_venda" class="form-label">Preço de Venda (R$)</label>
                            <input type="number" step="0.01" class="form-control" id="preco_venda" name="preco_venda" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quantidade_estoque" class="form-label">Estoque Inicial/Atual</label>
                            <input type="number" class="form-control" id="quantidade_estoque" name="quantidade_estoque" required min="0">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                        <label class="form-check-label" for="ativo">Produto Ativo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Adicionar Estoque (Entrada) -->
<div class="modal fade" id="addEstoqueModal" tabindex="-1" aria-labelledby="addEstoqueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-add-estoque">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEstoqueModalLabel">Adicionar Estoque para <span id="estoque-produto-nome"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="estoque-produto-id" name="id_produto">
                    <div class="mb-3">
                        <label for="quantidade-adicionar" class="form-label">Quantidade a Adicionar</label>
                        <input type="number" class="form-control" id="quantidade-adicionar" name="quantidade" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar Estoque</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inclua o JavaScript do Bootstrap (coloque antes do seu script personalizado) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/6SIngSFaRkX7E" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM completamente carregado.');

    // Verifique se o Bootstrap foi carregado corretamente
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap JS carregado com sucesso.');
    } else {
        console.error('Bootstrap JS não foi carregado. Os modais não funcionarão.');
        // Se o Bootstrap não carregar, os modais não funcionarão.
        // Você pode adicionar um alerta aqui ou desabilitar os botões.
    }

    const produtoModal = new bootstrap.Modal(document.getElementById('produtoModal'));
    const addEstoqueModal = new bootstrap.Modal(document.getElementById('addEstoqueModal'));

    const formProduto = document.getElementById('form-produto');
    const produtoIdInput = document.getElementById('produto-id');
    const codigoBarrasInput = document.getElementById('codigo_barras');
    const nomeProdutoInput = document.getElementById('nome');
    const descricaoInput = document.getElementById('descricao');
    const precoVendaInput = document.getElementById('preco_venda');
    const quantidadeEstoqueInput = document.getElementById('quantidade_estoque');
    const ativoCheckbox = document.getElementById('ativo');
    const produtoModalLabel = document.getElementById('produtoModalLabel');

    const formAddEstoque = document.getElementById('form-add-estoque');
    const estoqueProdutoIdInput = document.getElementById('estoque-produto-id');
    const estoqueProdutoNomeSpan = document.getElementById('estoque-produto-nome');
    const quantidadeAdicionarInput = document.getElementById('quantidade-adicionar');

    const appMessages = document.getElementById('app-messages');
    const messageContent = document.getElementById('message-content');
    const messageCloseBtn = appMessages ? appMessages.querySelector('.btn-close') : null;

    // Função para exibir mensagens na interface
    function showMessage(message, type = 'success') {
        if (appMessages && messageContent) {
            messageContent.textContent = message;
            appMessages.className = `alert alert-dismissible fade show mt-3 alert-${type}`;
            if (messageCloseBtn) {
                messageCloseBtn.onclick = () => appMessages.classList.add('d-none');
            }
            setTimeout(() => {
                appMessages.classList.add('d-none');
            }, 5000); // Esconde após 5 segundos
        } else {
            console.warn('Elemento de mensagem não encontrado. Exibindo via alert:', message);
            alert(message); // Fallback caso a div de mensagem não exista
        }
    }

    // Evento para abrir o modal de adicionar novo produto
    const btnAddNovoProduto = document.getElementById('btn-add-novo-produto');
    if (btnAddNovoProduto) {
        btnAddNovoProduto.addEventListener('click', function() {
            console.log('Botão "Adicionar Novo Produto" clicado.');
            formProduto.reset(); // Limpa o formulário
            produtoIdInput.value = ''; // Garante que é um novo produto
            produtoModalLabel.textContent = 'Adicionar Novo Produto';
            quantidadeEstoqueInput.readOnly = false; // Estoque inicial pode ser editado para novo produto
            ativoCheckbox.checked = true; // Novo produto sempre ativo por padrão
        });
    } else {
        console.error('Botão #btn-add-novo-produto não encontrado.');
    }


    // Evento para abrir o modal de editar produto
    document.querySelectorAll('.btn-editar-produto').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Botão "Editar Produto" clicado.');
            const data = this.dataset;
            produtoIdInput.value = data.id;
            codigoBarrasInput.value = data.codigo_barras;
            nomeProdutoInput.value = data.nome;
            descricaoInput.value = data.descricao;
            precoVendaInput.value = parseFloat(data.preco_venda).toFixed(2);
            quantidadeEstoqueInput.value = data.quantidade_estoque;
            quantidadeEstoqueInput.readOnly = true; // Estoque atual não pode ser editado diretamente aqui
            ativoCheckbox.checked = data.ativo === '1'; // Define o estado do checkbox
            produtoModalLabel.textContent = 'Editar Produto';
        });
    });

    // Evento para abrir o modal de adicionar estoque
    document.querySelectorAll('.btn-add-estoque').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Botão "Adicionar Estoque" clicado.');
            const data = this.dataset;
            estoqueProdutoIdInput.value = data.id;
            estoqueProdutoNomeSpan.textContent = data.nome;
            quantidadeAdicionarInput.value = ''; // Limpa o campo de quantidade
            quantidadeAdicionarInput.focus();
        });
    });

    // Submissão do formulário de Adicionar/Editar Produto
    formProduto.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulário de Produto submetido.');

        const formData = new FormData(formProduto);
        const data = Object.fromEntries(formData.entries());

        data.ativo = ativoCheckbox.checked ? 1 : 0; // Converte o checkbox 'ativo' para booleano

        fetch('actions/produto_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showMessage(result.message, 'success');
                produtoModal.hide(); // Fecha o modal
                location.reload(); // Recarrega a página para atualizar a tabela
            } else {
                showMessage('Erro: ' + result.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            showMessage('Ocorreu um erro de comunicação. Verifique o console.', 'danger');
        });
    });

    // Submissão do formulário de Adicionar Estoque
    formAddEstoque.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulário de Adicionar Estoque submetido.');

        const formData = new FormData(formAddEstoque);
        const data = Object.fromEntries(formData.entries());
        data.action = 'add_stock'; // Adiciona uma ação para o backend

        fetch('actions/produto_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showMessage(result.message, 'success');
                addEstoqueModal.hide(); // Fecha o modal
                location.reload(); // Recarrega a página para atualizar a tabela
            } else {
                showMessage('Erro: ' + result.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            showMessage('Ocorreu um erro de comunicação. Verifique o console.', 'danger');
        });
    });

    // Evento para (des)ativar produto
    document.querySelectorAll('.btn-toggle-ativo').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Botão "Ativar/Desativar" clicado.');
            const productId = this.dataset.id;
            const currentStatus = this.dataset.ativo === '1' ? true : false;
            const newStatus = !currentStatus;
            const actionText = newStatus ? 'ativar' : 'desativar';

            if (confirm(`Tem certeza que deseja ${actionText} este produto?`)) {
                fetch('actions/produto_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'toggle_active',
                        id: productId,
                        ativo: newStatus ? 1 : 0
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showMessage(result.message, 'success');
                        location.reload(); // Recarrega a página para atualizar o status
                    } else {
                        showMessage('Erro: ' + result.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    showMessage('Ocorreu um erro de comunicação. Verifique o console.', 'danger');
                });
            }
        });
    });
});

</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
