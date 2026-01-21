<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db.php';
session_start();

// --- require student login ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['book_name'])) {
    $book_name = trim($_POST['book_name']);
    $user_id = $_SESSION['user_id'];

    // Check if the user has already requested this book
    $stmt = $conn->prepare("SELECT request_id FROM requests WHERE student_id = ? AND book_name = ? AND status = 'pending'");
    $stmt->bind_param("is", $user_id, $book_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Redirect with a message that the user has already requested this book
        header("Location: ../main.php?message=already_requested");
        exit();
    }

    // Check if the book is available (quantity > 0)
    $stmt_check_quantity = $conn->prepare("SELECT quantity FROM books WHERE book_name = ?");
    $stmt_check_quantity->bind_param("s", $book_name);
    $stmt_check_quantity->execute();
    $quantity_result = $stmt_check_quantity->get_result();
    $book_quantity = 0;
    if ($quantity_row = $quantity_result->fetch_assoc()) {
        $book_quantity = $quantity_row['quantity'];
    }
    $stmt_check_quantity->close();

    if ($book_quantity <= 0) {
        // Redirect with a message that the book is out of stock
        header("Location: ../main.php?message=out_of_stock"); // Need to handle this message in main.php
        exit();
    }

    // Insert the new request
    $stmt = $conn->prepare("INSERT INTO requests (student_id, book_name, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("is", $user_id, $book_name);

    if ($stmt->execute()) {
        // No longer update is_available. Quantity is decremented when request is GRANTED.

        header("Location: ../main.php?message=request_successful");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Redirect if accessed directly or without book_name
    header("Location: ../main.php");
    exit();
}

$conn->close();
?>