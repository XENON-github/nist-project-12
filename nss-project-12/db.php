<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


//enter your own credentials here

$servername = "";
$username = "";       // MySQL user
$password = "";      // MySQL password
$dbname = ""; // Make sure it has your username as prefix!

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
