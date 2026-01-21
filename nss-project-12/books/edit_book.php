<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../db.php';
include '../includes/header.php';

// --- require admin or teacher login ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    die("Access denied.");
}

$book_id = null;
if (isset($_GET['book_id'])) {
    $book_id = intval($_GET['book_id']);
} elseif (isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);
}

if (!$book_id) {
    die("Book ID not provided.");
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_name = trim($_POST['book_name']);
    $book_serial_number = trim($_POST['book_serial_number']);
    $author = trim($_POST['author']);
    $quantity = intval($_POST['quantity']);

    if (empty($book_name) || empty($book_serial_number) || $quantity < 0) {
        die("Book name, serial number, and a non-negative quantity are required.");
    }

    $stmt = $conn->prepare("UPDATE books SET book_name=?, book_serial_number=?, author=?, quantity=? WHERE book_id=?");
    $stmt->bind_param("sssii", $book_name, $book_serial_number, $author, $quantity, $book_id);

    if ($stmt->execute()) {
        header("Location: ../main.php"); // Redirect to main page after successful update
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch book details for displaying the form (GET request)
$book = null;
$stmt = $conn->prepare("SELECT book_id, book_name, book_serial_number, author, quantity FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows === 1) {
    $book = $result->fetch_assoc();
}
$stmt->close();


if (!$book) {
    die("Book not found.");
}
?>

<div class="container">
    <h2>Edit Book: <?= htmlspecialchars($book['book_name']); ?></h2>

    <form method="POST" action="edit_book.php?book_id=<?= $book['book_id']; ?>">
        <input type="hidden" name="book_id" value="<?= $book['book_id']; ?>">
        
        <div class="form-group">
            <label for="book_name">Book Name:</label>
            <input type="text" id="book_name" name="book_name" value="<?= htmlspecialchars($book['book_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="book_serial_number">Serial Number:</label>
            <input type="text" id="book_serial_number" name="book_serial_number" value="<?= htmlspecialchars($book['book_serial_number']); ?>" required>
        </div>
        <div class="form-group">
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" value="<?= htmlspecialchars($book['author']); ?>">
        </div>
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="<?= htmlspecialchars($book['quantity']); ?>" min="0" required>
        </div>
        
        <button type="submit" class="btn">Update Book</button>
        <a href="../main.php" class="btn cancel">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
