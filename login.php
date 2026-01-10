<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistema PDV</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
</head>
<body>
    <div class="container login-container">
        <div class="row g-0 shadow-lg">
            <!-- Card do logotipo e frases -->
            <div class="col-lg-5 d-flex">
                <div class="card logo-card w-100">
                    <!-- Substitua o src abaixo pelo caminho do seu logotipo -->
                    <!-- Logo genérico ou texto -->
                    <div class="text-center mb-4">
                        <h1 class="text-primary fw-bold">SISTEMA PDV</h1>
                    </div>
                    <div class="frase-efeito">
                       Bem vindo ao Sistema PDV
                        Exemplo de portfólio, acesse sua conta!
                    </div>
                    <div class="dev-by">
                        Desenvolvido por <strong>Dev Victor</strong>
                    </div>
                </div>
            </div>
            <!-- Card do formulário de login -->
            <div class="col-lg-7 d-flex">
                <div class="card w-100">
                    <div class="card-body p-5">
                        <!-- <h2 class="card-title text-center mb-4">Acessar Sistema</h2> -->
                        <form action="actions/login_action.php" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label"><strong>Email</strong></label>
                                <input 
                                    type="email" 
                                    class="form-control form-control-lg" 
                                    id="email" 
                                    name="email" 
                                    required 
                                    style="background-color: #ECECEC; border-radius: 30px; border: none; box-shadow: none; transition: box-shadow 0.3s, background-color 0.3s;"
                                    onfocus="this.style.backgroundColor='#f5f5f5'; this.style.boxShadow='0 0 0 2px #bdbdbd';"
                                    onblur="this.style.backgroundColor='#ECECEC'; this.style.boxShadow='none';">
                            </div>
                            <div class="mb-4">
                                <label for="senha" class="form-label"><strong>Senha</strong></label>
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg" 
                                    id="senha" 
                                    name="senha" 
                                    required 
                                    style="background-color: #ECECEC; border-radius: 30px; border: none; box-shadow: none; transition: box-shadow 0.3s, background-color 0.3s;"
                                    onfocus="this.style.backgroundColor='#f5f5f5'; this.style.boxShadow='0 0 0 2px #bdbdbd';"
                                    onblur="this.style.backgroundColor='#ECECEC'; this.style.boxShadow='none';">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-custom-laranja btn-lg">Entrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>