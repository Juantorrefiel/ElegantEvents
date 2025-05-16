<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current = basename($_SERVER['PHP_SELF']);
$user = $_SESSION['user'] ?? null;
$role = $user['user_type'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Elegant Events</title>
  <link rel="icon" type="image/png" href="images/logo.png" />
  <link rel="stylesheet" href="index.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
</head>
<body>

<header class="navbar">
  <div class="nav-container">
    <div class="logo-section">
      <img src="images/logo.png" alt="Elegant Events Logo" class="logo" />
    </div>
    <div class="nav-right">
      <button class="menu-toggle" onclick="toggleMenu()">☰</button>
      <nav class="nav-links">
        <?php if ($user): ?>
          <a href="dashboard.php" <?= $current === 'dashboard.php' ? 'aria-current="page"' : '' ?>>Dashboard</a>
          <?php if ($role === 'admin'): ?>
            <a href="users.php" <?= $current === 'users.php' ? 'aria-current="page"' : '' ?>>Users</a>
            <a href="messages.php" <?= $current === 'messages.php' ? 'aria-current="page"' : '' ?>>Messages</a>
          <?php endif; ?>
          <a href="events.php" <?= $current === 'events.php' ? 'aria-current="page"' : '' ?>>Events</a>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="index.html">Home</a>
          <a href="About/about.html">About</a>
          <a href="Services/services.html">Services</a>
          <a href="Portfolio/portfolio.html">Portfolio</a>
          <a href="Contact/contact.php">Contact Us</a>
          <a href="login.php" <?= $current === 'login.php' ? 'aria-current="page"' : '' ?>>Login</a>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</header>

<script>
  function toggleMenu() {
    const nav = document.querySelector(".nav-links");
    nav.classList.toggle("show");
  }
</script>
