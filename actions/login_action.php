<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT id, nome, senha_hash, email FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // var_dump($user); // Adicione esta linha para ver o que o banco retornou
    // exit();          // Adicione esta linha para parar o script aqui
    

    if ($user && password_verify($senha, $user['senha_hash'])) {
        // Login bem-sucedido
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['email'];
        header("Location: ../dash.php"); // Redireciona para o dashboard
        exit();
    } else {
        // Falha no login
        // Idealmente, guarde uma mensagem de erro na sessão e mostre no login.php
        header("Location: ../login.php?error=1");
        exit();
    }
}
?>