<?php
$host = "localhost";
$port = "5432"; // Porta padrão do PostgreSQL
$database = "checknum";
$userdb = "root"; // Certifique-se de que este usuário existe no PostgreSQL
$passdb = ""; // Senha do usuário do banco

$conn = pg_connect("host=$host port=$port dbname=$database user=$userdb password=$passdb");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
} else {
    echo "Connection to PostgreSQL database successful!";
}
?>
