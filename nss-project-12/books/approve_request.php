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
            // Update the request status
            $stmt = $conn->prepare("UPDATE requests SET status='granted', deadline=? WHERE student_id=? AND book_name=? AND status='pending'");
            $stmt->bind_param("sis", $deadline, $student_id, $book_name);
            $stmt->execute();

            // Check if request update was successful and then update book quantity
            if ($stmt->affected_rows > 0) {
                $stmt_book = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE book_name = ? AND quantity > 0");
                $stmt_book->bind_param("s", $book_name);
                $stmt_book->execute();
                $stmt_book->close();
            }
            $stmt->close(); // Close the first statement
        }

        } elseif ($action === 'deny') {

            $book_name = $_POST['book_name'] ?? '';

            $student_id = intval($_POST['student_id'] ?? 0);

            $denial_reason = trim($_POST['denial_reason'] ?? '');

    

            if ($book_name && $student_id && $denial_reason) {

                $stmt = $conn->prepare("UPDATE requests SET status='denied', denial_reason=? WHERE student_id=? AND book_name=? AND status='pending'");

                $stmt->bind_param("sis", $denial_reason, $student_id, $book_name);

                $stmt->execute();

            }

    

        } elseif ($action === 'returned') {
        $request_id = intval($_POST['request_id'] ?? 0);

        if ($request_id) {
            // Get book_name before deleting the request
            $stmt_get_book = $conn->prepare("SELECT book_name FROM requests WHERE request_id=?");
            if ($stmt_get_book) {
                $stmt_get_book->bind_param("i", $request_id);
                $stmt_get_book->execute();
                $result_get_book = $stmt_get_book->get_result();
                $book_name_to_return = null;
                if ($result_get_book && $row_get_book = $result_get_book->fetch_assoc()) {
                    $book_name_to_return = $row_get_book['book_name'];
                }
                $stmt_get_book->close();
            }

            // Delete the request
            $stmt = $conn->prepare("DELETE FROM requests WHERE request_id=? AND status='granted'");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();

            // Check if request deletion was successful and then update book quantity
            if ($stmt->affected_rows > 0 && $book_name_to_return) {
                $stmt_book = $conn->prepare("UPDATE books SET quantity = quantity + 1 WHERE book_name = ?");
                $stmt_book->bind_param("s", $book_name_to_return);
                $stmt_book->execute();
                $stmt_book->close();
            }
            $stmt->close();
        }
    }

    // Redirect back to dashboard after action
    header("Location: ../main.php");
    exit();
}
?>
