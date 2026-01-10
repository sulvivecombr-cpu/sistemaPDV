<?php
// Senha que você está digitando no formulário
// $senha_digitada = 'Madmvconecta';
$senha_digitada = 'tyrvoPdv123!@';
// Hash que veio do banco de dados (copie e cole EXATAMENTE da saída do var_dump)
$hash_do_banco = '$2y$10$5QkOU.SvGNTE5e8DLe3rnOs./s75o8K70HcI8IfqmTIvmV9cIp8hC';

echo "Senha digitada: " . $senha_digitada . "<br>";
echo "Hash do banco: " . $hash_do_banco . "<br><br>";

if (password_verify($senha_digitada, $hash_do_banco)) {
    echo "<h2>RESULTADO: A senha CORRESPONDE ao hash!</h2>";
} else {
    echo "<h2>RESULTADO: A senha NÃO corresponde ao hash.</h2>";
}

// Opcional: Gerar um novo hash para 'admin123' para fins de comparação
echo "<br>---<br>";
echo "Um novo hash para " . $senha_digitada . "  seria: " . password_hash($senha_digitada, PASSWORD_DEFAULT);
?>