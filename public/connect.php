<?php
$host = 'dpg-cq82v988fa8c738ccl1g-a';
$db = 'checkardb';
$user = 'checkardb_user';
$pass = 'wzqgbNgdjPi3S3EgOQi0REKEMomdZxjk';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
    exit();
}
