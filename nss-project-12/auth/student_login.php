<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db.php';
include '../includes/header.php'; // Path corrected

// If already logged in, go to main
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student') {
    header('Location: ../main.php');
    exit();
}

// Auto-login via cookie
if (isset($_COOKIE['user_id']) && !isset($_SESSION['user_id'])) {
    $user_id = intval($_COOKIE['user_id']);
    $query = $conn->prepare("SELECT * FROM users WHERE user_id=? AND role='student'");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = 'student';
        session_regenerate_id(true);
        header('Location: ../main.php');
        exit();
    } else {
        // Invalid cookie — remove it
        setcookie('user_id', '', time() - 3600, '/');
        unset($_COOKIE['user_id']);
    }
}

// Handle login form submission
$error = '';
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='student'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = 'student';
            session_regenerate_id(true);

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
    <header><h2>Student Login</h2></header>

    <?php if($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <label><input type="checkbox" name="remember"> Remember Me</label><br>
        <button type="submit" name="login">Login</button>
    </form>

    <p>Don't have an account? <a href="student_register.php">Register here</a></p>
</div>

<?php include '../includes/footer.php'; // Path corrected ?>
