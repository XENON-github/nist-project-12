<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';
include 'includes/header.php';

// --- Safe cookie-based auto-login ---
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $cookie_id = intval($_COOKIE['user_id']);
    if ($cookie_id > 0) {
        $stmt = $conn->prepare("SELECT user_id, role, name FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $cookie_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $_SESSION['user_id'] = (int)$row['user_id'];
            $_SESSION['role'] = $row['role'];
            setcookie("user_id", $row['user_id'], time() + 30*24*60*60, "/");
        } else {
            setcookie("user_id", "", time() - 3600, "/");
        }
    } else {
        setcookie("user_id", "", time() - 3600, "/");
    }
}

// --- require login ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// --- get user info ---
$user_id = intval($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'student';

$stmt = $conn->prepare("SELECT user_id, name, email, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows !== 1) {
    setcookie("user_id", "", time() - 3600, "/");
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
$user = $res->fetch_assoc();
?>

<div class="container">
    <header class="dashboard-header">
      <h1>Welcome, <?= htmlspecialchars($user['name']); ?>!</h1>
      <p class="small">Role: <?= htmlspecialchars($user['role']); ?></p>
    </header>

    <div class="dashboard-container">

    <?php
    if (isset($_GET['message'])) {
        $message = $_GET['message'];
        $messageText = '';
        $messageClass = 'success'; // Default to success
        if ($message === 'request_successful') {
            $messageText = 'Your book request has been submitted successfully.';
        } elseif ($message === 'already_requested') {
            $messageText = 'You have already requested this book.';
            $messageClass = 'error';
        } elseif ($message === 'out_of_stock') {
            $messageText = 'This book is currently out of stock.';
            $messageClass = 'error';
        }

        if ($messageText) {
            echo "<div class='card message {$messageClass}'>{$messageText}</div>";
        }
    }
    ?>

    <?php if($role==='student'): ?>
      <!-- My Borrowed Books -->
      <div class="card">
        <h2><i class="fas fa-book"></i> My Borrowed Books</h2>
        <?php
          $stmt = $conn->prepare("SELECT book_name, status, deadline FROM requests WHERE student_id=? AND status='granted' ORDER BY created_at DESC");
          $stmt->bind_param("i",$user_id);
          $stmt->execute();
          $rows = $stmt->get_result();
        ?>
        <div class="table-responsive">
          <?php if($rows->num_rows>0): ?>
          <table>
            <thead><tr><th>Book</th><th>Status</th><th>Deadline</th></tr></thead>
            <tbody>
            <?php while($row=$rows->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['book_name']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
                <td><?= $row['deadline'] ?? 'N/A'; ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          <?php else: ?>
            <p>No borrowed books yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- My Book Requests -->
      <div class="card">
        <h2><i class="fas fa-tasks"></i> My Book Requests</h2>
        <?php
          $stmt = $conn->prepare("SELECT book_name, status, denial_reason FROM requests WHERE student_id=? AND (status = 'denied' OR status = 'pending') ORDER BY created_at DESC");
          $stmt->bind_param("i",$user_id);
          $stmt->execute();
          $rows = $stmt->get_result();
        ?>
        <div class="table-responsive">
          <?php if($rows->num_rows>0): ?>
          <table>
            <thead><tr><th>Book</th><th>Status</th><th>Reason for Denial</th></tr></thead>
            <tbody>
            <?php while($row=$rows->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['book_name']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
                <td><?= strtolower(trim($row['status'])) === 'denied' ? htmlspecialchars($row['denial_reason']) : ''; ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          <?php else: ?>
            <p>You haven't made any book requests yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Available Books -->
      <?php include __DIR__.'/books/list_books.php'; ?>

    <?php elseif($role==='teacher' || $role==='admin'): ?>

      <!-- Pending Requests -->
      <div class="card full">
        <h2>Pending Book Requests</h2>
        <?php
          $stmt = $conn->prepare("SELECT r.book_name, r.student_id, u.name, u.roll_no, u.class, u.section FROM requests r JOIN users u ON r.student_id=u.user_id WHERE r.status='pending' ORDER BY r.created_at ASC");
          $stmt->execute();
          $pending_rows = $stmt->get_result();
        ?>
        <div class="table-responsive">
          <?php if($pending_rows->num_rows>0): ?>
          <table>
            <thead><tr><th>Student</th><th>Roll No</th><th>Class</th><th>Section</th><th>Book</th><th>Actions</th></tr></thead>
            <tbody>
            <?php while($row=$pending_rows->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td><?= htmlspecialchars($row['roll_no']); ?></td>
                <td><?= htmlspecialchars($row['class']); ?></td>
                <td><?= htmlspecialchars($row['section']); ?></td>
                <td><?= htmlspecialchars($row['book_name']); ?></td>
                <td>
                  <form method="POST" action="books/approve_request.php" style="display:inline">
                    <input type="hidden" name="action" value="grant">
                    <input type="hidden" name="book_name" value="<?= htmlspecialchars($row['book_name']); ?>">
                    <input type="hidden" name="student_id" value="<?= $row['student_id']; ?>">
                    <input type="date" name="deadline" required>
                    <button class="btn approve">Grant</button>
                  </form>
                  <button class="btn deny" onclick="document.getElementById('denyForm-<?= $row['student_id'] ?>-<?= htmlspecialchars($row['book_name']) ?>').classList.toggle('hidden');">Deny</button>

                  <div id="denyForm-<?= $row['student_id'] ?>-<?= htmlspecialchars($row['book_name']) ?>" class="card hidden" style="margin-top: 10px;">
                    <h4>Reason for Denial</h4>
                    <form method="POST" action="books/approve_request.php">
                      <input type="hidden" name="action" value="deny">
                      <input type="hidden" name="book_name" value="<?= htmlspecialchars($row['book_name']); ?>">
                      <input type="hidden" name="student_id" value="<?= $row['student_id']; ?>">
                      <textarea name="denial_reason" rows="3" placeholder="Enter reason for denial" required></textarea>
                      <button type="submit" class="btn deny">Confirm Deny</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          <?php else: ?>
            <p>No pending requests.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Lent Books -->
      <div class="card full">
        <h2><i class="fas fa-book-reader"></i> Lent Books</h2>
        <?php
          $stmt = $conn->prepare("SELECT r.request_id, r.book_name, r.deadline, u.name, u.roll_no, u.class, u.section FROM requests r JOIN users u ON r.student_id=u.user_id WHERE r.status='granted' ORDER BY r.deadline ASC");
          $stmt->execute();
          $lent_rows = $stmt->get_result();
        ?>
        <div class="table-responsive">
          <?php if($lent_rows->num_rows>0): ?>
          <table>
            <thead>
              <tr>
                <th>Student</th>
                <th>Roll No</th>
                <th>Class</th>
                <th>Section</th>
                <th>Book</th>
                <th>Deadline</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php while($row=$lent_rows->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td><?= htmlspecialchars($row['roll_no']); ?></td>
                <td><?= htmlspecialchars($row['class']); ?></td>
                <td><?= htmlspecialchars($row['section']); ?></td>
                <td><?= htmlspecialchars($row['book_name']); ?></td>
                <td><?= htmlspecialchars($row['deadline']); ?></td>
                <td>
                  <form method="POST" action="books/approve_request.php" style="display:inline">
                    <input type="hidden" name="request_id" value="<?= $row['request_id']; ?>">
                    <input type="hidden" name="action" value="returned">
                    <button class="btn deny"><i class="fas fa-check-circle"></i> Returned</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          <?php else: ?>
            <p>No books currently lent out.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Available Books -->
      <?php include __DIR__.'/books/list_books.php'; ?>

    <?php else: ?>
      <div class="card">
        <p class="small">Unknown role — contact administrator.</p>
      </div>
    <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
