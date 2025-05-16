<?php
session_start();
include 'config.php';
include 'generate-uuid.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$error = '';
$name = '';
$email = '';
$role = '';
$password = '';
$confirm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];
  $role = $_POST['role'];
  $active = 1; // Always active

  if ($name && $email && $password && $confirm && in_array($role, ['admin', 'staff'])) {
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $error = "Email already exists. Please use a different email.";
    } elseif ($password !== $confirm) {
      $error = "Passwords do not match.";
    } else {
      $id = generate_uuid();
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (id, name, email, password, user_type, created_at, active) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
      $stmt->bind_param("sssssi", $id, $name, $email, $hashedPassword, $role, $active);

      if ($stmt->execute()) {
        header("Location: users.php?msg=created");
        exit;
      } else {
        $error = "Failed to create user. Please try again.";
      }
      $stmt->close();
    }
    $check->close();
  } else {
    $error = "All fields are required.";
  }
}
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 600px;">
    <h1 style="text-align:center;">Add User</h1>

    <?php if ($error): ?>
      <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="form-styled" novalidate>
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required value="<?= htmlspecialchars($name) ?>" />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>" />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="6" />

      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required minlength="6" />

      <label for="role">Role</label>
      <select id="role" name="role" required>
        <option value="" disabled <?= $role == '' ? 'selected' : '' ?>>Select role</option>
        <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="staff" <?= $role == 'staff' ? 'selected' : '' ?>>Staff</option>
      </select>

      <section class="action-buttons" style="margin-top: 1.5rem;">
        <button type="submit" class="submit-btn">Save</button>
        <a href="users.php" class="submit-btn cancel" style="text-align: center;">Cancel</a>
      </section>
    </form>
  </section>
</main>

<?php include 'footer.php'; ?>
