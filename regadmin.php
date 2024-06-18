<?php
include 'config/database.php';

// Definindo os dados do administrador
$nome = '';
$email = '';
$password = password_hash('', PASSWORD_BCRYPT);
$is_admin = 1;

// Verifica se o usuário já existe
$sql_check = "SELECT * FROM users WHERE email = ?";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([$email]);
$result = $stmt_check->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo "O usuário com o email 'lui@example.com' já existe.";
} else {
    // Insere o novo usuário admin
    $sql_insert = "INSERT INTO users (nome, email, password, is_admin) VALUES (?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);

    if ($stmt_insert->execute([$nome, $email, $password, $is_admin])) {
        echo "Usuário admin criado com sucesso";
    } else {
        echo "Erro: " . $stmt_insert->errorInfo()[2];
    }
}

$stmt_check->closeCursor();
$stmt_insert->closeCursor();
$pdo = null;
?>
