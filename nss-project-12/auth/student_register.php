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
    $roll_no = $_POST['roll_no'];
    $class = $_POST['class'];
    $section = $_POST['section'];

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO users (name,email,password,role,roll_no,class,section) VALUES (?,?,?,?,?,?,?)");
    $role = 'student';
    $stmt->bind_param("sssssss",$name,$email,$password,$role,$roll_no,$class,$section);

    if($stmt->execute()){
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['role'] = 'student';
        // Set cookie for "remember me" if needed
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
        <h2>Student Registration</h2>
    </header>
    <form method="POST">
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="text" name="roll_no" placeholder="Roll No" required><br>
        <input type="text" name="class" placeholder="Class" required><br>
        <input type="text" name="section" placeholder="Section" required><br>
        <button type="submit" name="register">Register</button>
    </form>
    <p>Already have an account? <a href="student_login.php">Login here</a></p>
</div>

<?php include '../includes/footer.php'; // Path corrected ?>