<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db.php';
include '../includes/header.php'; // Path corrected

// If already sessioned, go to main
if (isset($_SESSION['user_id'])) {
    header('Location: ../main.php');
    exit();
}

// Auto-login via cookie (if present)
if (isset($_COOKIE['user_id']) && !isset($_SESSION['user_id'])) {
    $user_id = intval($_COOKIE['user_id']);
    $q = $conn->prepare("SELECT * FROM users WHERE user_id=? AND role IN ('teacher','admin')");
    $q->bind_param("i", $user_id);
    $q->execute();
    $res = $q->get_result();
    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        session_regenerate_id(true);
        header('Location: ../main.php');
        exit();
    } else {
        // invalid cookie: remove it
        setcookie('user_id', '', time() - 3600, '/');
    }
}

// Handle login form
$error = '';
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role IN ('teacher','admin')");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            session_regenerate_id(true);

            // set remember cookie for whole site (root). Use '/' so cookie is sent to all pages.
            if ($remember) {
                setcookie('user_id', $user['user_id'], time() + (30*24*60*60), '/');
            }

            header('Location: ../main.php');
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<div class="container">
  <header><h2>Admin / Teacher Login</h2></header>

  <?php if($error): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <form method="post" action="">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <label><input type="checkbox" name="remember"> Remember Me</label>
    <button type="submit" name="login">Login</button>
  </form>

  <p>Don't have an account? <a href="admin_register.php">Register here</a></p>
</div>

<?php include '../includes/footer.php'; // Path corrected ?>
