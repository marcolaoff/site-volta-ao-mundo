<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$message = '';
$importMessage = '';

if (!empty($_POST['content'])) {
    $sql = "INSERT INTO comments (user_id, content) VALUES (:user_id, :content)";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':content', $_POST['content']);

    if ($stmt->execute()) {
        $message = 'Successfully submitted your comment';
    } else {
        $message = 'Sorry there must have been an issue submitting your comment';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['json_file'])) {
    $file = $_FILES['json_file']['tmp_name'];
    $json_data = file_get_contents($file);
    $comments = json_decode($json_data, true);

    foreach ($comments as $comment) {
        $stmt = $pdo->prepare('INSERT INTO comments (user_id, content, status) VALUES (:user_id, :content, :status)');
        $stmt->bindParam(':user_id', $comment['user_id']);
        $stmt->bindParam(':content', $comment['content']);
        $stmt->bindParam(':status', $comment['status']);
        $stmt->execute();
    }
    $importMessage = 'Comments imported successfully';
}

$comments = $pdo->query('SELECT comments.content, comments.created_at, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.status = "approved" ORDER BY comments.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comments</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body>
    <h1>Comments</h1>
    <form action="comments.php" method="POST">
        <textarea name="content" placeholder="Write your comment here" required></textarea>
        <input type="submit" value="Submit">
    </form>
    <p><?= $message ?></p>

    <h2>Import Comments from JSON</h2>
    <form action="comments.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="json_file" required>
        <input type="submit" value="Import">
    </form>
    <p><?= $importMessage ?></p>

    <h2>All Comments</h2>
    <div>
        <?php foreach ($comments as $comment): ?>
            <p><strong><?= htmlspecialchars($comment['username']); ?>:</strong> <?= htmlspecialchars($comment['content']); ?> <em>(<?= $comment['created_at']; ?>)</em></p>
        <?php endforeach; ?>
    </div>
</body>
</html>
