<?php
// approve_request.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../db.php'; // adjust path if needed

// --- Check if user is logged in ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// --- Get role ---
$role = $_SESSION['role'] ?? '';
$user_id = intval($_SESSION['user_id']);

if ($role !== 'admin' && $role !== 'teacher') {
    echo "<p class='error'>Only admins or teachers can access this section.</p>";
    exit();
}

// --- Handle form actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'grant') {
        $book_name = $_POST['book_name'] ?? '';
        $student_id = intval($_POST['student_id'] ?? 0);
        $deadline = $_POST['deadline'] ?? '';

        if ($book_name && $student_id && $deadline) {
            $stmt = $conn->prepare("UPDATE requests SET status='granted', deadline=? WHERE student_id=? AND book_name=? AND status='pending'");
            $stmt->bind_param("sis", $deadline, $student_id, $book_name);
            $stmt->execute();
        }

    } elseif ($action === 'deny') {
        $book_name = $_POST['book_name'] ?? '';
        $student_id = intval($_POST['student_id'] ?? 0);

        if ($book_name && $student_id) {
            $stmt = $conn->prepare("DELETE FROM requests WHERE student_id=? AND book_name=? AND status='pending'");
            $stmt->bind_param("is", $student_id, $book_name);
            $stmt->execute();
        }

    } elseif ($action === 'returned') {
        $request_id = intval($_POST['request_id'] ?? 0);

        if ($request_id) {
            $stmt = $conn->prepare("DELETE FROM requests WHERE request_id=? AND status='granted'");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
        }
    }

    // Redirect back to dashboard after action
    header("Location: ../main.php");
    exit();
}
?>
