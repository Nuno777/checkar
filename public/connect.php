<?php
$host = "localhost";
$port = 3306;
$database = "checknum";
$userdb = "root";
$passdb = "";
$conn = new mysqli($host, $userdb, $passdb, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
