<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT id, name, email, password, user_type, active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (!$user['active']) {
                $error = "Your account is inactive. Please contact the admin.";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'user_type' => $user['user_type']
                ];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }

        $stmt->close();
    } else {
        $error = "Both email and password are required.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login – Elegant Events</title>
  <link rel="icon" type="image/png" href="images/logo.png" />
  <link rel="stylesheet" href="index.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
</head>
<body>

<!-- NAVIGATION BAR -->
<header class="navbar">
  <div class="nav-container">
    <div class="logo-section">
      <img src="images/logo.png" alt="Elegant Events Logo" class="logo" />
    </div>
    <div class="nav-right">
      <button class="menu-toggle" onclick="toggleMenu()">☰</button>
      <nav class="nav-links">
        <a href="index.html">Home</a>
        <a href="About/about.html">About</a>
        <a href="Services/services.html">Services</a>
        <a href="Portfolio/portfolio.html">Portfolio</a>
        <a href="Contact/contact.php">Contact Us</a>
        <a href="login.php" class="active">Login</a>
      </nav>
    </div>
  </div>
</header>

<!-- LOGIN SECTION -->
<main class="login-main">
  <section class="login-box">
    <h1>Login to Elegant Events</h1>

    <?php if (!empty($error)): ?>
      <div class="error-message" style="color: red; margin-bottom: 1rem;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <label for="email">Email</label>
      <input
        type="email"
        id="email"
        name="email"
        placeholder="you@example.com"
        required
      />

      <label for="password">Password</label>
      <input
        type="password"
        id="password"
        name="password"
        placeholder="••••••••"
        required
        minlength="6"
      />

      <button type="submit" class="redirect-button">Login</button>
    </form>
  </section>
</main>

<footer class="footer">
         <div class="footer-content">
               <img src="images/logo.png" alt="Elegant Events Logo" class="logo" />
            <p>&copy; 2025 Elegant Events Services | All Rights Reserved</p>
            <p class="tagline">Crafting Moments with Grace and Grandeur...</p>
         </div>
         <div class="footer-links">
            <a href="#">Home</a>
            <a href="About/about.html">About</a>
            <a href="Services/services.html">Services</a>
            <a href="Portfolio/portfolio.html">Portfolio</a>
            <a href="Contact/contact.php">Contact Us</a>
         </div>
         <div class="newsletter">
            <p>Subscribe to our newsletter for exclusive promotions, event tips and updates</p>
            <form>
               <input type="email" placeholder="Your email address" required>
               <button type="submit">Subscribe</button>
            </form>
         </div>
         <div class="social-icons">
            <a href="https://www.facebook.com/JYPETWICE" target="_blank" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="https://twitter.com/JYPETWICE" target="_blank" class="social-icon"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com/twicetagram/" target="_blank" class="social-icon"  aria-label="Visit our Instagram"><i class="fab fa-instagram"></i></a>
         </div>
      </footer>

<!-- NAVBAR TOGGLE SCRIPT -->
<script>
  function toggleMenu() {
    const nav = document.querySelector('.nav-links');
    nav.classList.toggle('show');
  }
</script>

</body>
</html>
