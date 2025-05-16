<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$id = $_GET['id'] ?? '';
$error = '';
$success = '';
$name = '';
$email = '';
$role = '';

if (!$id) {
  header("Location: users.php?msg=invalid");
  exit;
}

// Fetch user info
$stmt = $conn->prepare("SELECT name, email, user_type FROM users WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
  $stmt->close();
  header("Location: users.php?msg=invalid");
  exit;
}

$stmt->bind_result($name, $email, $role);
$stmt->fetch();
$stmt->close();

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $role = $_POST['role'];
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if ($name && $email && in_array($role, ['admin', 'staff'])) {
    if (!empty($password)) {
      if ($password !== $confirm) {
        $error = "Passwords do not match.";
      } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, user_type = ? WHERE id = ?");
        $update->bind_param("sssss", $name, $email, $hashed, $role, $id);
      }
    } else {
      $update = $conn->prepare("UPDATE users SET name = ?, email = ?, user_type = ? WHERE id = ?");
      $update->bind_param("ssss", $name, $email, $role, $id);
    }

    if (isset($update) && $update->execute()) {
      header("Location: users.php?msg=updated");
      exit;
    } elseif (isset($update)) {
      $error = "Update failed. Please try again.";
    }

    if (isset($update)) $update->close();
  } else {
    $error = "All fields are required.";
  }
}
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 600px;">
    <h1 style="text-align:center;">Edit User</h1>

    <?php if ($error): ?>
      <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="form-styled" novalidate>
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required value="<?= htmlspecialchars($name) ?>" />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>" />

      <label for="role">Role</label>
      <select id="role" name="role" required>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
      </select>

      <label for="password">New Password <small style="font-weight:normal">(leave blank to keep current)</small></label>
      <input type="password" id="password" name="password" minlength="6" />

      <label for="confirm_password">Confirm New Password</label>
      <input type="password" id="confirm_password" name="confirm_password" minlength="6" />

      <section class="action-buttons" style="margin-top: 1.5rem;">
        <button type="submit" class="submit-btn">Update</button>
        <a href="users.php" class="submit-btn cancel" style="text-align: center;">Cancel</a>
      </section>
    </form>
  </section>
</main>

<?php include 'footer.php'; ?>
