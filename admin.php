<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar se o usuário é um administrador (implemente a lógica necessária)
// Este exemplo assume que há uma coluna 'is_admin' na tabela 'users' que indica se o usuário é administrador
$stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = :id');
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['is_admin'] != 1) {
    echo "Acesso negado. Você não tem permissão para acessar esta página.";
    exit();
}

$comments = $pdo->query('SELECT comments.id, comments.content, comments.status, comments.created_at, users.username FROM comments JOIN users ON comments.user_id = users.id ORDER BY comments.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $stmt = $pdo->prepare('UPDATE comments SET status = "approved" WHERE id = :id');
        $stmt->bindParam(':id', $_POST['comment_id']);
        $stmt->execute();
    } elseif (isset($_POST['reject'])) {
        $stmt = $pdo->prepare('UPDATE comments SET status = "rejected" WHERE id = :id');
        $stmt->bindParam(':id', $_POST['comment_id']);
        $stmt->execute();
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
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body>
    <h1>Admin Panel</h1>
    <h2>Manage Comments</h2>
    <div>
        <?php foreach ($comments as $comment): ?>
            <p><strong><?= htmlspecialchars($comment['username']); ?>:</strong> <?= htmlspecialchars($comment['content']); ?> <em>(<?= $comment['created_at']; ?>)</em> - Status: <?= htmlspecialchars($comment['status']); ?></p>
            <form action="admin.php" method="POST" style="display:inline;">
                <input type="hidden" name="comment_id" value="<?= $comment['id']; ?>">
                <input type="submit" name="approve" value="Approve">
                <input type="submit" name="reject" value="Reject">
            </form>
        <?php endforeach; ?>
    </div>
</body>
</html>
