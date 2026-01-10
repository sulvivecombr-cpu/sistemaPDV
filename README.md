# Sistema PDV

Um sistema de Ponto de Venda (PDV) completo e responsivo, desenvolvido em PHP. Este projeto serve como um exemplo de arquitetura e implementação de um sistema comercial web.

## 🚀 Funcionalidades

*   **Frente de Caixa (PDV):** Interface ágil para vendas, com suporte a leitores de código de barras.
*   **Gestão de Estoque:** Controle de produtos e quantidades.
*   **Integração com Pagamentos:** Estrutura preparada para integração com terminais de pagamento (ex: Mercado Pago).
*   **Gestão Fiscal:** Configuração de parâmetros fiscais (NFC-e/CF-e).
*   **Painel Administrativo:** Dashboard para acompanhamento de métricas.
*   **Responsividade:** Funciona em desktops e dispositivos móveis.

## 🛠️ Tecnologias Utilizadas

*   **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.
*   **Backend:** PHP (Vanilla).
*   **Banco de Dados:** MySQL.
*   **Gerenciamento de Dependências:** Composer.

## ⚙️ Instalação e Configuração

### Pré-requisitos
*   PHP 7.4 ou superior.
*   MySQL.
*   Composer.
*   Servidor web (Apache/Nginx) ou XAMPP/WAMP.

### Passo a Passo

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/seu-usuario/sistema-pdv.git
    ```

2.  **Configuração do Banco de Dados:**
    *   Crie um banco de dados MySQL (ex: `pdv_system`).
    *   Importe o esquema do banco de dados (se disponível) ou configure a conexão.
    *   Edite o arquivo `config/db.php` ou `conection.php` com suas credenciais locais:
        ```php
        $username = "root";
        $password = ""; // Sua senha local
        ```

3.  **Instale as dependências:**
    ```bash
    composer install
    ```

4.  **Acesse o sistema:**
    *   Abra o navegador e acesse `http://localhost/sistema-pdv`.
    *   Login padrão (se houver): `admin@admin.com` / `admin` (exemplo).

## 🔒 Segurança

Este projeto foi sanitizado para remoção de credenciais reais. 
*   **NUNCA** suba arquivos contendo senhas de produção (como `conection_prd.php`) para o repositório público.
*   Utilize variáveis de ambiente para credenciais sensíveis em produção.

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.
