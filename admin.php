<?php
session_start();
require 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = :id');
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['is_admin'] != 1) {
    echo "Acesso negado. Você não tem permissão para acessar esta página.";
    exit();
}

$comments = $pdo->query('SELECT id, nome, email, comentario, status, created_at FROM comments ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $stmt = $pdo->prepare('UPDATE comments SET status = "approved" WHERE id = :id');
        $stmt->bindParam(':id', $_POST['comment_id']);
        $stmt->execute();
    } elseif (isset($_POST['reject'])) {
        $stmt = $pdo->prepare('UPDATE comments SET status = "rejected" WHERE id = :id');
        $stmt->bindParam(':id', $_POST['comment_id']);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $stmt = $pdo->prepare('DELETE FROM comments WHERE id = :id');
        $stmt->bindParam(':id', $_POST['comment_id']);
        $stmt->execute();
    } elseif (isset($_FILES['json_file'])) {
        $file = $_FILES['json_file']['tmp_name'];
        $json_data = file_get_contents($file);
        $comments = json_decode($json_data, true);

        foreach ($comments as $comment) {
            $stmt = $pdo->prepare('INSERT INTO comments (nome, email, comentario, status) VALUES (:nome, :email, :comentario, :status)');
            $stmt->bindParam(':nome', $comment['nome']);
            $stmt->bindParam(':email', $comment['email']);
            $stmt->bindParam(':comentario', $comment['comentario']);
            $stmt->bindParam(':status', $comment['status']);
            $stmt->execute();
        }
        $importMessage = 'Comments imported successfully';
    }

    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .comment-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .comment {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .btn-logout {
            float: right;
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
                        <a class="nav-link" href="comments.php">Comentários</a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger btn-logout" href="logout.php">Logoff</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>Admin Panel</h1>
        <h2>Manage Comments</h2>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="json_file" required>
            <input type="submit" class="btn btn-primary mt-3" value="Import Comments">
        </form>
        <p><?= !empty($importMessage) ? $importMessage : ''; ?></p>

        <div class="comment-section mt-5">
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <p><strong><?= htmlspecialchars($comment['nome']); ?>:</strong> <?= htmlspecialchars($comment['comentario']); ?> <em>(<?= $comment['created_at']; ?>)</em> - Status: <?= htmlspecialchars($comment['status']); ?></p>
                    <form action="admin.php" method="POST" style="display:inline;">
                        <input type="hidden" name="comment_id" value="<?= $comment['id']; ?>">
                        <input type="submit" name="approve" class="btn btn-success btn-sm" value="Approve">
                        <input type="submit" name="reject" class="btn btn-warning btn-sm" value="Reject">
                        <input type="submit" name="delete" class="btn btn-danger btn-sm" value="Delete">
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
