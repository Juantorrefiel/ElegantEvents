<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$user = $_SESSION['user'];
$role = $user['user_type'];
$search = trim($_GET['search'] ?? '');
$param = "%$search%";
$flash = $_GET['msg'] ?? '';

$stmt = $conn->prepare("
  SELECT id, reference, first_name, last_name, email, event_type, event_date, guest_count, message, submitted_at
  FROM contact_messages
  WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?
  ORDER BY submitted_at DESC
");
$stmt->bind_param("sss", $param, $param, $param);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 1100px;">
    <h1 style="text-align: center;">Contact Inquiries</h1>

    <?php if ($flash === 'deleted'): ?>
      <p style="color: green; font-weight: bold;">Inquiry deleted successfully.</p>
    <?php elseif ($flash === 'error'): ?>
      <p style="color: red; font-weight: bold;">Failed to delete inquiry.</p>
    <?php endif; ?>

    <form method="get" action="messages.php" class="user-search-form">
      <input type="text" name="search" placeholder="Search inquiries..." value="<?= htmlspecialchars($search) ?>" />
    </form>

    <section aria-label="Inquiry Table Section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Ref #</th>
            <th>Name</th>
            <th>Email</th>
            <th>Event</th>
            <th>Date</th>
            <th>Guests</th>
            <th>Message</th>
            <th>Submitted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td data-label="Ref #"><?= htmlspecialchars($row['reference']) ?></td>
              <td data-label="Name"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
              <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
              <td data-label="Event"><?= htmlspecialchars($row['event_type']) ?></td>
              <td data-label="Date"><?= htmlspecialchars($row['event_date']) ?></td>
              <td data-label="Guests"><?= (int)$row['guest_count'] ?></td>
              <td data-label="Message"><?= htmlspecialchars(mb_strimwidth($row['message'], 0, 50, '...')) ?></td>
              <td data-label="Submitted"><?= date('M d, Y H:i', strtotime($row['submitted_at'])) ?></td>
              <td data-label="Actions" class="actions-cell">
                <div class="action-buttons">
                  <a href="message-view.php?id=<?= $row['id'] ?>" class="btn edit">View</a>
                  <a href="message-delete.php?id=<?= $row['id'] ?>" class="btn delete" onclick="return confirm('Delete this inquiry?')">Delete</a>
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
