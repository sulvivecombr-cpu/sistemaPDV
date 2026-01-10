/**
 * offline-manager.js
 * Gerencia o armazenamento local (IndexedDB) para produtos e vendas offline.
 */

const OfflineManager = {
    dbName: 'pdv_db',
    dbVersion: 1,
    db: null,

    // Inicializa o banco de dados
    init: function () {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = (event) => {
                console.error("Erro ao abrir IndexedDB:", event);
                reject(event);
            };

            request.onsuccess = (event) => {
                this.db = event.target.result;
                console.log("IndexedDB aberto com sucesso.");
                this.cacheProducts(); // Tenta atualizar o cache ao abrir
                this.syncSales();    // Tenta sincronizar vendas pendentes
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                // Store para Produtos (busca offline)
                if (!db.objectStoreNames.contains('products')) {
                    const productStore = db.createObjectStore('products', { keyPath: 'id' });
                    productStore.createIndex('nome', 'nome', { unique: false });
                    productStore.createIndex('codigo_barras', 'codigo_barras', { unique: false });
                }
                // Store para Fila de Vendas (offline sales)
                if (!db.objectStoreNames.contains('sales_queue')) {
                    db.createObjectStore('sales_queue', { autoIncrement: true });
                }
            };
        });
    },

    // Busca dados do servidor e salva no IndexedDB
    cacheProducts: function () {
        if (!navigator.onLine) return; // Só faz se tiver online

        fetch('actions/load_offline_data.php')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.products) {
                    const transaction = this.db.transaction(['products'], 'readwrite');
                    const store = transaction.objectStore('products');

                    // Limpa antigo (opcional, ou update)
                    store.clear().onsuccess = () => {
                        data.products.forEach(prod => {
                            // Converte preços para number se vier string
                            prod.preco_venda = parseFloat(prod.preco_venda);
                            store.add(prod);
                        });
                        console.log(`Cache atualizado: ${data.products.length} produtos.`);
                    };
                }
            })
            .catch(err => console.error("Erro ao cachear produtos:", err));
    },

    // Busca produtos no IndexedDB
    searchProducts: function (term) {
        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject("DB não inicializado");
                return;
            }

            const transaction = this.db.transaction(['products'], 'readonly');
            const store = transaction.objectStore('products');
            const results = [];
            const termLower = term.toLowerCase();

            // Usar cursor para varrer (pode ser lento se tiver MUITOS produtos, 
            // mas para milhares é ok. Ideal seria usar Full Text Search externo, 
            // mas aqui é "contains" simples).
            store.openCursor().onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    const prod = cursor.value;
                    const nome = prod.nome.toLowerCase();
                    const codigo = prod.codigo_barras ? prod.codigo_barras.toLowerCase() : '';

                    if (nome.includes(termLower) || codigo.includes(termLower)) {
                        results.push(prod);
                    }

                    if (results.length >= 20) {
                        resolve(results); // Limita retorno
                        return;
                    }

                    cursor.continue();
                } else {
                    resolve(results);
                }
            };

            transaction.onerror = (e) => reject(e);
        });
    },

    // Salva venda na fila offline
    saveOfflineSale: function (saleData) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['sales_queue'], 'readwrite');
            const store = transaction.objectStore('sales_queue');

            // Adiciona timestamp para controle
            saleData.created_at_offline = new Date().toISOString();

            const request = store.add(saleData);

            request.onsuccess = () => {
                console.log("Venda salva offline!");
                resolve(true); // Retorna sucesso
            };

            request.onerror = (e) => {
                console.error("Erro ao salvar venda offline:", e);
                reject(e);
            };
        });
    },

    // Sincroniza vendas da fila
    syncSales: function () {
        if (!navigator.onLine || !this.db) return;

        const transaction = this.db.transaction(['sales_queue'], 'readonly');
        const store = transaction.objectStore('sales_queue');

        // Pega todas as vendas pendentes
        store.getAll().onsuccess = (event) => {
            const vendas = event.target.result;
            const keys = [];
            // getAllKeys não é suportado em todos browsers antigos, mas modernos sim.
            // Vamos fazer um hack para pegar as chaves se precisar deletar.

            if (vendas.length === 0) return;

            console.log(`Tentando sincronizar ${vendas.length} vendas...`);

            // Envia uma por uma (mais seguro) ou em lote. Vamos uma por uma para simplificar erro.
            // Para deletar, precisamos da chave (id autoincrement).
            // Vamos fazer um cursor para ter a chave e o valor.
        };

        // Melhor abordagem com cursor para ter a key de delete
        this.processQueue();
    },

    processQueue: function () {
        const transaction = this.db.transaction(['sales_queue'], 'readwrite');
        const store = transaction.objectStore('sales_queue');

        store.openCursor().onsuccess = (event) => {
            const cursor = event.target.result;
            if (cursor) {
                const venda = cursor.value;
                const key = cursor.key;

                // Tenta enviar
                fetch('actions/venda_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(venda)
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            console.log(`Venda ${key} sincronizada com sucesso. ID: ${data.id_venda}`);
                            // Remove da fila
                            this.removeFromQueue(key);
                            // Notifica UI (se quiser)
                            const event = new CustomEvent('offline-synced', { detail: { id: data.id_venda } });
                            window.dispatchEvent(event);
                        } else {
                            console.error(`Erro ao sincronizar venda ${key}:`, data.message);
                            // Mantém na fila para tentar depois? Ou move para "erros"?
                            // Por enquanto mantém.
                        }
                    })
                    .catch(err => console.error("Erro rede sync:", err));

                cursor.continue(); // Vai para a próxima
            }
        };
    },

    removeFromQueue: function (key) {
        const transaction = this.db.transaction(['sales_queue'], 'readwrite');
        const store = transaction.objectStore('sales_queue');
        store.delete(key);
    }
};
