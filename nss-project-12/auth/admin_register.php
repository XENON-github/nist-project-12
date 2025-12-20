<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db.php';
include '../includes/header.php'; // Path corrected

$error = '';

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
    $role = 'admin'; // Changed from 'teacher' to 'admin'
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if($stmt->execute()){
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['role'] = $role;
        // Set cookie for "remember me"
        setcookie("user_id", $conn->insert_id, time() + (30*24*60*60), "/");
        header("Location: ../main.php");
        exit();
    } else {
        $error = "Registration failed (maybe email already exists)";
    }
}
?>

<div class="container">
    <header>
        <h2>Admin / Teacher Registration</h2>
    </header>
    <form method="POST">
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="register">Register</button>
    </form>
    <p>Already have an account? <a href="admin_login.php">Login here</a></p>
</div>

<?php include '../includes/footer.php'; // Path corrected ?>