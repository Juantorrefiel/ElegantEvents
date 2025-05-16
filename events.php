<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$user = $_SESSION['user'];
$role = $user['user_type'];
$user_id = $user['id'];
$search = trim($_GET['search'] ?? '');
$param = "%$search%";
$flash = $_GET['msg'] ?? '';

// Admin: see all, Staff: see only assigned
if ($role === 'admin') {
  $stmt = $conn->prepare("
    SELECT 
      e.id,
      e.reference AS event_ref,
      e.event_date,
      e.time_from,
      e.time_to,
      u.name AS staff_name,
      c.id AS inquiry_id,
      c.reference AS inquiry_ref,
      c.first_name,
      c.last_name,
      c.event_type,
      c.guest_count
    FROM events e
    JOIN users u ON e.assigned_to = u.id
    JOIN contact_messages c ON e.contact_message_id = c.id
    WHERE e.reference LIKE ? OR u.name LIKE ?
    ORDER BY e.event_date DESC, e.time_from ASC
  ");
  $stmt->bind_param("ss", $param, $param);
} else {
  $stmt = $conn->prepare("
    SELECT 
      e.id,
      e.reference AS event_ref,
      e.event_date,
      e.time_from,
      e.time_to,
      u.name AS staff_name,
      c.id AS inquiry_id,
      c.reference AS inquiry_ref,
      c.first_name,
      c.last_name,
      c.event_type,
      c.guest_count
    FROM events e
    JOIN users u ON e.assigned_to = u.id
    JOIN contact_messages c ON e.contact_message_id = c.id
    WHERE e.assigned_to = ?
    ORDER BY e.event_date DESC, e.time_from ASC
  ");
  $stmt->bind_param("s", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 1100px;">
    <h1 style="text-align: center;">
      <?= $role === 'admin' ? 'Scheduled Events' : 'My Scheduled Events' ?>
    </h1>

    <?php if ($flash === 'deleted'): ?>
      <p style="color: green; font-weight: bold;">Event deleted successfully.</p>
    <?php elseif ($flash === 'error'): ?>
      <p style="color: red; font-weight: bold;">Failed to delete event.</p>
    <?php elseif ($flash === 'created'): ?>
      <p style="color: green; font-weight: bold;">Event added successfully.</p>
    <?php elseif ($flash === 'updated'): ?>
      <p style="color: green; font-weight: bold;">Event updated successfully.</p>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
      <form method="get" action="events.php" class="user-search-form">
        <input type="text" name="search" placeholder="Search by event ref or staff..." value="<?= htmlspecialchars($search) ?>" />
        <a href="event-add.php" class="btn success">Add Event</a>
      </form>
    <?php endif; ?>

    <section aria-label="Event Table Section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Client</th>
            <th>Inquiry Ref</th>
            <th>Event Type</th>
            <th>Guests</th>
            <th>Assigned Staff</th>
            <th>Date</th>
            <th>Time</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td data-label="Client"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
              <td data-label="Inquiry Ref">
                <a href="message-view.php?id=<?= $row['inquiry_id'] ?>&from=events">
                  <?= htmlspecialchars($row['inquiry_ref']) ?>
                </a>
              </td>
              <td data-label="Event Type"><?= htmlspecialchars(ucwords($row['event_type'])) ?></td>
              <td data-label="Guests"><?= (int)$row['guest_count'] ?></td>
              <td data-label="Staff"><?= htmlspecialchars($row['staff_name']) ?></td>
              <td data-label="Date"><?= htmlspecialchars($row['event_date']) ?></td>
              <td data-label="Time">
                <?= date("g:i A", strtotime($row['time_from'])) ?> – <?= date("g:i A", strtotime($row['time_to'])) ?>
              </td>
              <td data-label="Actions" class="actions-cell">
                <section class="action-buttons">
                  <a href="event-view.php?id=<?= $row['id'] ?>" class="btn view">View</a>
                  <?php if ($role === 'admin'): ?>
                    <a href="event-edit.php?id=<?= $row['id'] ?>" class="btn edit">Edit</a>
                    <a href="event-delete.php?id=<?= $row['id'] ?>" class="btn delete" onclick="return confirm('Delete this event?')">Delete</a>
                  <?php endif; ?>
                </section>
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
