<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$search = trim($_GET['search'] ?? '');
$param = "%$search%";
$flash = $_GET['msg'] ?? '';

$stmt = $conn->prepare("
  SELECT id, name, email, user_type, created_at, active
  FROM users
  WHERE name LIKE ? OR email LIKE ?
  ORDER BY created_at DESC
");
$stmt->bind_param("ss", $param, $param);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box">
    <h1 style="text-align:center; margin-bottom: 1.5rem;">User Management</h1>

    <?php if ($flash === 'created'): ?>
      <p style="color: green; font-weight: bold;">User created successfully.</p>
    <?php elseif ($flash === 'deleted'): ?>
      <p style="color: green; font-weight: bold;">User deleted successfully.</p>
    <?php elseif ($flash === 'error'): ?>
      <p style="color: red; font-weight: bold;">Failed to delete user. Please try again.</p>
    <?php elseif ($flash === 'invalid'): ?>
      <p style="color: red; font-weight: bold;">Invalid delete request.</p>
    <?php endif; ?>

    <form method="get" action="users.php" class="user-search-form">
      <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>" />
      <a href="user-add.php" class="btn success">Add User</a>
    </form>

    <section aria-label="User Table Section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= ucfirst($row['user_type']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td data-label="Actions" class="actions-cell">
  <div class="action-buttons">
    <a href="user-edit.php?id=<?= $row['id'] ?>" class="btn edit">Edit</a>
    <a href="user-delete.php?id=<?= $row['id'] ?>" class="btn delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
  </div>
</td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </section>
</main>

<?php
$stmt->close();
$conn->close();
include 'footer.php';
?>
