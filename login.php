<?php
session_start();
require 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT id, nome, password FROM users WHERE email = :email');
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nome'];
        header("Location: admin.php");
        exit();
    } else {
        $error = 'Sorry, those credentials do not match';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
        .login-form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }
        .login-form h2 {
            margin-bottom: 20px;
        }
        .login-form .form-group {
            margin-bottom: 20px;
        }
        .login-form .btn {
            width: 100%;
        }
        .login-form .link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
        }
        .login-form .link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-form .link a:hover {
            text-decoration: underline;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="link">Not registered? <a href="register.php">Create an account</a></p>
        <p class="link"><a href="primeirapg.html">Voltar para o Site</a></p>
    </div>
</body>
</html>
