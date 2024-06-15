<?php
require '../config/database.php';
$message = '';

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $stmt = $pdo->prepare($sql);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    $stmt->bindParam(':username', $_POST['username']);
    $stmt->bindParam(':password', $password);

    if ($stmt->execute()) {
        $message = 'Successfully created new user';
    } else {
        $message = 'Sorry there must have been an issue creating your account';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body>
    <h1>Register</h1>
    <form action="register.php" method="POST">
        <input type="text" placeholder="Enter your username" name="username" required>
        <input type="password" placeholder="and password" name="password" required>
        <input type="submit" value="Submit">
    </form>
    <p><?= $message ?></p>
</body>
</html>
