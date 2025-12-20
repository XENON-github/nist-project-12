<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "sql112.infinityfree.com";
$username = "if0_40049058";       // MySQL user
$password = "EgyptianMummy";      // MySQL password
$dbname = "if0_40049058_library"; // Make sure it has your username as prefix!

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
