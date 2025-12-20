<?php
include 'includes/header.php';
?>

<!-- Main content -->
<div class="container">
    <header>
        <h1>Welcome to the Library Registry System</h1>
        <p>Your one-stop solution for managing and accessing library resources.</p>
    </header>

    <main class="options">
        <a href="./auth/student_login.php" class="card">
            <h2><i class="fas fa-user-graduate"></i> Student</h2>
            <p>Login or Register to borrow books and manage your account.</p>
        </a>

        <a href="./auth/admin_login.php" class="card">
            <h2><i class="fas fa-chalkboard-teacher"></i> Admin / Teacher</h2>
            <p>Manage the library's collection, users, and borrowing requests.</p>
        </a>

        <a href="./main.php" class="card">
            <h2><i class="fas fa-home"></i> Main Page</h2>
            <p>Go to your dashboard if you are already logged in.</p>
        </a>
    </main>
</div>

<?php
include 'includes/footer.php';
?>
