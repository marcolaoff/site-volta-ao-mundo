<?php
session_start();
require 'config/database.php';

$message = '';

if (!empty($_POST['comentario'])) {
    $nome = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_POST['nome'];
    $email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_POST['email'];

    // Inserir o comentário diretamente no banco de dados com status "pending"
    $stmt = $pdo->prepare('INSERT INTO comments (nome, email, comentario, status) VALUES (:nome, :email, :comentario, "pending")');
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':comentario', $_POST['comentario']);
    if ($stmt->execute()) {
        $message = 'Comentário enviado com sucesso';
    } else {
        $message = 'Desculpe, houve um problema ao enviar seu comentário';
    }
}

$comments = $pdo->query('SELECT nome, comentario, created_at FROM comments WHERE status = "approved" ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comentários</title>
    <link rel="icon" href="galeria de fotos/bandeira.jpg">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }
        .comment-form {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        .comment-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .comment {
            margin-bottom: 20px;
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .comment p {
            margin: 0;
        }
        .comment .comment-info {
            font-size: 0.9em;
            color: #6c757d;
        }
        .comment .comment-date {
            font-size: 0.8em;
            color: #999;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand" href="primeirapg.html"><img src="galeria de fotos/bandeira.jpg" alt="Itália" style="width: 40px"> Itália </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="primeirapg.html">Página Inicial</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pratos-tipicos.html">Pratos Típicos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cultura-local.html">Cultura Local</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pontos-turisticos.html">Pontos Turísticos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" style="color: #008000; href="comments.php">Comentários</a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['is_admin']): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">Painel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logoff</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1 class="mt-5">Comentários</h1>
        <div class="comment-form">
            <form action="comments.php" method="POST">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" name="nome" class="form-control" placeholder="Digite seu nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" class="form-control" placeholder="Digite seu email" required>
                    </div>
                <?php else: ?>
                    <p><strong>Comentando como: <?= $_SESSION['user_name']; ?> (<?= $_SESSION['user_email']; ?>)</strong></p>
                <?php endif; ?>
                <div class="form-group">
                    <label for="comentario">Comentário:</label>
                    <textarea name="comentario" class="form-control" placeholder="Escreva seu comentário aqui" required></textarea>
                </div>
                <input type="submit" class="btn btn-primary" value="Enviar">
            </form>
            <p class="mt-3"><?= $message ?></p>
        </div>

        <h2 class="mt-5">Todos os Comentários</h2>
        <div class="comment-section">
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <p><strong><?= htmlspecialchars($comment['nome']); ?>:</strong> <?= htmlspecialchars($comment['comentario']); ?></p>
                    <p class="comment-date"><?= date('d M Y, H:i', strtotime($comment['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
