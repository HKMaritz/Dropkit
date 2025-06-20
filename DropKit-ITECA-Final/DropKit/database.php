<?php
$servername = "localhost";
$username   = "root";       // or your MySQL user
$password   = "";           // or your MySQL password
$dbname     = "dropkit_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
