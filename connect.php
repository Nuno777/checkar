<?php
$host = "dpg-cq82v988fa8c738ccl1g-a";
$port = 5432;
$database = "checkardb";
$userdb = "checkardb_user";
$passdb = "wzqgbNgdjPi3S3EgOQi0REKEMomdZxjk";
$conn = new mysqli($host, $userdb, $passdb, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
