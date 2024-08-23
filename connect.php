<?php
$host = "localhost";
$database = "checknum";
$userdb = "root";
$passdb = "";
$conn = new mysqli($host, $userdb, $passdb, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

