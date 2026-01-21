<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the script is not accessed directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    die('Access denied.');
}

// Get the search query if it exists
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base SQL query
$sql = "SELECT book_id, book_name, book_serial_number, author, quantity FROM books";

// If there is a search query, modify the SQL to include a WHERE clause
if ($search_query) {
    $sql .= " WHERE book_name LIKE ? OR author LIKE ? OR book_serial_number LIKE ?";
}

$sql .= " ORDER BY book_name ASC";

$stmt = $conn->prepare($sql);

// If there is a search query, bind the parameters
if ($search_query) {
    $search_param = "%{$search_query}%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$books_result = $stmt->get_result();
?>

<div class="card full">
    <h2><i class="fas fa-book-open"></i> Available Books</h2>

    <?php if ($role === 'student'): ?>
    <div style="margin-bottom: 20px;">
        <form method="GET" action="main.php">
            <div class="form-group" style="flex-direction: row; align-items: center;">
                <input type="text" name="search" placeholder="Search for books..." value="<?= htmlspecialchars($search_query); ?>" style="flex-grow: 1; margin-right: 10px;">
                <button type="submit" class="btn">Search</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($role === 'admin' || $role === 'teacher'): ?>
    <div style="margin-bottom: 20px;">
        <button type="button" class="btn" id="addBookBtn">
            <i class="fas fa-plus"></i> Add a New Book
        </button>
    </div>

    <div id="addBookForm" class="card hidden">
        <h3>Add a New Book</h3>
        <form method="POST" action="books/add_book.php">
            <div class="form-group">
                <label for="book_name">Book Name:</label>
                <input type="text" id="book_name" name="book_name" required>
            </div>
            <div class="form-group">
                <label for="book_serial_number">Serial Number:</label>
                <input type="text" id="book_serial_number" name="book_serial_number" required>
            </div>
            <div class="form-group">
                <label for="author">Author:</label>
                <input type="text" id="author" name="author">
            </div>
            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" required>
            </div>
            <button type="submit" class="btn">Add Book</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <?php if ($books_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Book Name</th>
                    <th>Serial Number</th>
                    <th>Author</th>
                    <th>Quantity</th>
                    <?php if ($role === 'admin' || $role === 'teacher'): ?>
                    <th>Action</th>
                    <?php elseif ($role === 'student'): ?>
                    <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($book = $books_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($book['book_name']); ?></td>
                    <td><?= htmlspecialchars($book['book_serial_number']); ?></td>
                    <td><?= htmlspecialchars($book['author']); ?></td>
                    <td><?= htmlspecialchars($book['quantity']); ?></td>
                    <?php if ($role === 'admin' || $role === 'teacher'): ?>
                    <td>
                        <a href="books/edit_book.php?book_id=<?= $book['book_id']; ?>" class="btn">Edit</a>
                        <form method="POST" action="books/delete_book.php" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this book?');">
                            <input type="hidden" name="book_id" value="<?= $book['book_id']; ?>">
                            <button type="submit" class="btn deny">Delete</button>
                        </form>
                    </td>
                    <?php elseif ($role === 'student'): ?>
                    <td>
                        <?php if ($book['quantity'] > 0): ?>
                        <form method="POST" action="books/request_book.php" style="display:inline">
                            <input type="hidden" name="book_name" value="<?= htmlspecialchars($book['book_name']); ?>">
                            <button type="submit" class="btn">Request</button>
                        </form>
                        <?php else: ?>
                        <button class="btn" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No books available matching your search query.</p>
        <?php endif; ?>
    </div>
</div>
