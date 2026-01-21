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
    $book_id = intval($_POST['book_id']);

    if ($book_id <= 0) {
        die("Invalid book ID.");
    }

    // You might want to check if the book is currently borrowed before deleting
    // For simplicity, we are deleting it directly.

    $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);

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
