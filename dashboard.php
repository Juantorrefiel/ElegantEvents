<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$user = $_SESSION['user'];
$name = $user['name'];
$role = $user['user_type'];
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 600px;">
    <h1 style="text-align:center;">Welcome, <?= htmlspecialchars($name) ?>!</h1>
    <p style="text-align:center;">Your role: <strong><?= ucfirst($role) ?></strong></p>

    <section style="margin-top: 2rem;">
      <?php if ($role === 'admin'): ?>
        <p style="margin: 2rem 0;"><a class="redirect-button" href="users.php">Manage Users</a></p>
        <p style="margin: 2rem 0;"><a class="redirect-button" href="messages.php">Manage Contact Inquiries</a></p>
        <p style="margin: 2rem 0;"><a class="redirect-button" href="events.php">Manage Events</a></p>
      <?php else: ?>
        <p style="margin: 2rem 0;"><a class="redirect-button" href="events.php">My Scheduled Events</a></p>
      <?php endif; ?>
    </section>
  </section>
</main>

<?php include 'footer.php'; ?>
