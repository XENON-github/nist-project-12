<?php
// request_book.php
// INCLUDED inside main.php
// Assumes $conn, $user_id, $role are already set

if ($role === 'student') {

    // --- Handle student book request submission ---
    $requestMessage = '';
    if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['book_name'])) {
        $book_name = trim($_POST['book_name']);
        $stmt = $conn->prepare("INSERT INTO requests (student_id, book_name, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("is", $user_id, $book_name);
        $stmt->execute();
        $requestMessage = "✅ Your request has been submitted and is pending approval.";
    } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
        $requestMessage = "⚠️ Please enter a book name.";
    }

    ?>

    <div class="student-section">

        <!-- Collapsible Request Form -->
        <div class="form-container" style="margin-bottom:20px;">
            <?php if ($requestMessage) echo "<p style='margin-bottom:10px;'>$requestMessage</p>"; ?>
            <button type="button" onclick="document.getElementById('requestForm').classList.toggle('hidden')" 
                style="padding:6px 12px; border:none; background:#007bff; color:#fff; border-radius:6px; cursor:pointer; margin-bottom:10px;">
                Request a Book
            </button>

            <div id="requestForm" class="hidden" style="margin-top:12px;">
                <form method="POST">
                    <input type="text" name="book_name" placeholder="Book Name" required
                        style="padding:6px 10px; border-radius:6px; border:1px solid #ccc; width:70%; margin-right:6px;">
                    <button type="submit" style="padding:6px 12px; border-radius:6px; border:none; background:#28a745; color:#fff; cursor:pointer;">Submit</button>
                </form>
            </div>
        </div>

    </div>

    <style>
        .hidden { display:none; }
    </style>

<?php
} elseif ($role === 'admin' || $role === 'teacher') {
    // Admin/Teacher sees all granted books
    $stmt = $conn->prepare("
        SELECT r.book_name, r.deadline, u.name, u.roll_no, u.class, u.section
        FROM requests r
        JOIN users u ON r.student_id = u.user_id
        WHERE r.status = 'granted'
        ORDER BY r.deadline ASC
    ");
    $stmt->execute();
    $rows = $stmt->get_result();
    ?>

    <div class="admin-section">
      <h2>📖 Lent Books</h2>
      <?php if ($rows->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Student</th>
            <th>Roll No</th>
            <th>Class</th>
            <th>Section</th>
            <th>Book</th>
            <th>Deadline</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $rows->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['roll_no']) ?></td>
            <td><?= htmlspecialchars($row['class']) ?></td>
            <td><?= htmlspecialchars($row['section']) ?></td>
            <td><?= htmlspecialchars($row['book_name']) ?></td>
            <td><?= htmlspecialchars($row['deadline']) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p>No books currently lent out.</p>
      <?php endif; ?>
    </div>

<?php
} else {
    echo "<p class='error'>You are not authorized to view this page.</p>";
}
?>
