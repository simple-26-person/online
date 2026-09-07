<?php

$host = "sql206.infinityfree.com";
$username = "if0_42853240";
$password = "YOUR_MYSQL_PASSWORD";
$database = "if0_42853240_onlineexam";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>