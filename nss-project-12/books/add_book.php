<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db.php';
session_start();

// --- require admin or teacher login ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_name = trim($_POST['book_name']);
    $book_serial_number = trim($_POST['book_serial_number']);
    $author = trim($_POST['author']);
    $quantity = intval($_POST['quantity'] ?? 1); // Get quantity from POST, default to 1 if not provided

    if (empty($book_name) || empty($book_serial_number)) {
        die("Book name and serial number are required.");
    }

    $stmt = $conn->prepare("INSERT INTO books (book_name, book_serial_number, author, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $book_name, $book_serial_number, $author, $quantity);

    if ($stmt->execute()) {
        header("Location: ../main.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
