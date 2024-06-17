<?php
$host = 'localhost';
$dbname = 'voltaaomundobd'; // Nome do seu banco de dados atualizado
$username = 'root'; // Nome de usuário do banco de dados
$password = ''; // Senha do banco de dados

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}
?>
