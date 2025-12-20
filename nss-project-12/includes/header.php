<?php
header('Content-Type: text/html; charset=utf-8'); // Moved here to ensure it's sent first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Registry System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/nss-project-12/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="/nss-project-12/index.php" class="nav-logo">
            <i class="fas fa-book-open"></i><span style="margin-left: 10px;">Library System</span>
        </a>
        
        <div class="nav-right-group">
            <ul class="nav-menu">
                <li><a href="/nss-project-12/index.php">Home</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="/nss-project-12/main.php">Dashboard</a></li>
                    <li><a href="/nss-project-12/auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/nss-project-12/auth/student_login.php">Student Login</a></li>
                    <li><a href="/nss-project-12/auth/admin_login.php">Admin/Teacher Login</a></li>
                <?php endif; ?>
            </ul>

            <!-- Dark Mode Toggle -->
            <label class="dark-mode-toggle">
                <span class="toggle-label">Dark Mode</span>
                <div class="toggle-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider"></span>
                </div>
            </label>

            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</nav>
