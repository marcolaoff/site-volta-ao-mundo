<?php
session_start();
require '../config/database.php';

$message = '';

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $records = $pdo->prepare('SELECT id, username, password, is_admin FROM users WHERE username = :username');
    $records->bindParam(':username', $_POST['username']);
    $records->execute();
    $results = $records->fetch(PDO::FETCH_ASSOC);

    if (count($results) > 0 && password_verify($_POST['password'], $results['password'])) {
        $_SESSION['user_id'] = $results['id'];
        $_SESSION['is_admin'] = $results['is_admin'];
        header("Location: comments.php");
        exit();
    } else {
        $message = 'Sorry, those credentials do not match';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body>
    <h1>Login</h1>
    <form action="login.php" method="POST">
        <input type="text" placeholder="Enter your username" name="username" required>
        <input type="password" placeholder="and password" name="password" required>
        <input type="submit" value="Submit">
    </form>
    <p><?= !empty($message) ? $message : ''; ?></p>
</body>
</html>
