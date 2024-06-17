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

$importMessage = '';

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
    } elseif (isset($_POST['import'])) {
        if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['json_file']['tmp_name'];
            $json_data = file_get_contents($file);
            $comments = json_decode($json_data, true);

            if ($comments !== null) {
                foreach ($comments as $comment) {
                    $status = isset($comment['status']) ? $comment['status'] : 'pending';
                    $stmt = $pdo->prepare('INSERT INTO comments (nome, email, comentario, status) VALUES (:nome, :email, :comentario, :status)');
                    $stmt->bindParam(':nome', $comment['nome']);
                    $stmt->bindParam(':email', $comment['email']);
                    $stmt->bindParam(':comentario', $comment['comentario']);
                    $stmt->bindParam(':status', $status);
                    $stmt->execute();
                }
                $importMessage = 'Comentários importados com sucesso';
            } else {
                $importMessage = 'Erro ao decodificar o arquivo JSON';
            }
        } else {
            $importMessage = 'Erro ao importar comentários';
        }
    } elseif (isset($_POST['approve_all'])) {
        $stmt = $pdo->prepare('UPDATE comments SET status = "approved" WHERE status != "approved"');
        $stmt->execute();
        $importMessage = 'Todos os comentários foram aprovados.';
    } elseif (isset($_POST['reject_all'])) {
        $stmt = $pdo->prepare('UPDATE comments SET status = "rejected" WHERE status != "rejected"');
        $stmt->execute();
        $importMessage = 'Todos os comentários foram rejeitados.';
    } elseif (isset($_POST['delete_all'])) {
        $stmt = $pdo->prepare('DELETE FROM comments WHERE id IS NOT NULL');
        $stmt->execute();
        $importMessage = 'Todos os comentários foram excluídos.';
    }

    header("Location: admin.php?importMessage=" . urlencode($importMessage));
    exit();
}

if (isset($_GET['importMessage'])) {
    $importMessage = $_GET['importMessage'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Painel do Administrador</title>
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
        h1, h2 {
            text-align: left;
        }
        .import-json {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .import-json input[type="file"] {
            display: none;
        }
        .import-json label {
            margin-right: 10px;
            margin-bottom: 0;
        }
        .import-json .btn {
            margin-top: 0;
        }
        .alert {
            margin-top: 20px;
        }
        .action-buttons {
            margin-bottom: 20px;
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
        <h1 class="mt-5">Painel do Administrador</h1>
        <h2>Administrar Comentários</h2>
        <div class="action-buttons mb-4">
            <form action="admin.php" method="POST" class="d-inline">
                <button type="submit" name="approve_all" class="btn btn-success">Aprovar Todos</button>
            </form>
            <form action="admin.php" method="POST" class="d-inline">
                <button type="submit" name="reject_all" class="btn btn-warning">Rejeitar Todos</button>
            </form>
            <form action="admin.php" method="POST" class="d-inline">
                <button type="submit" name="delete_all" class="btn btn-danger">Apagar todos</button>
            </form>
        </div>
        <?php if ($importMessage): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($importMessage); ?>
            </div>
        <?php endif; ?>
        
        <h2>Importar Comentários JSON</h2>
        <div class="import-json">
            <form action="admin.php" method="POST" enctype="multipart/form-data" class="form-inline">
                <label for="json_file" class="btn btn-primary">Escolher arquivo JSON</label>
                <input type="file" id="json_file" name="json_file" required>
                <button type="submit" name="import" class="btn btn-success ml-2">Importar Comentários</button>
            </form>
        </div>

        <div class="comment-section mt-5">
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <p><strong><?= htmlspecialchars($comment['nome']); ?>:</strong> <?= htmlspecialchars($comment['comentario']); ?> <em>(<?= $comment['created_at']; ?>)</em> - Status: <?= htmlspecialchars($comment['status']); ?></p>
                    <form action="admin.php" method="POST" style="display:inline;">
                        <input type="hidden" name="comment_id" value="<?= $comment['id']; ?>">
                        <input type="submit" name="approve" class="btn btn-success btn-sm" value="Aprovar">
                        <input type="submit" name="reject" class="btn btn-warning btn-sm" value="Rejeitar">
                        <input type="submit" name="delete" class="btn btn-danger btn-sm" value="Apagar">
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
