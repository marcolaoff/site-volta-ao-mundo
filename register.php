<?php
require 'config/database.php';

$message = '';

if (!empty($_POST['nome']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    // Verificar se o e-mail já está em uso
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $message = 'Este e-mail já está em uso';
    } else {
        // Inserir o novo usuário
        $stmt = $pdo->prepare('INSERT INTO users (nome, email, password) VALUES (:nome, :email, :password)');
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt->bindParam(':nome', $_POST['nome']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            $message = 'Usuário criado com sucesso';
        } else {
            $message = 'Desculpe, ocorreu um problema ao criar sua conta';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }
        .register-form h2 {
            margin-bottom: 20px;
        }
        .register-form .form-group {
            margin-bottom: 20px;
        }
        .register-form .btn {
            width: 100%;
        }
        .register-form .link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
        }
        .register-form .link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-form .link a:hover {
            text-decoration: underline;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="register-form">
        <h2>Register</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?= $message; ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="link">Já tem uma conta? <a href="login.php">Faça o seu login aqui!</a></p>
        <p class="link"><a href="primeirapg.html">Volte para o site inicial</a></p>
    </div>
</body>
</html>
