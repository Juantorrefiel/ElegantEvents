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

$id = $_GET['id'] ?? '';
if (!$id) {
  header("Location: events.php?msg=invalid");
  exit;
}

$stmt = $conn->prepare("
  SELECT 
    e.reference AS event_ref,
    e.event_date,
    e.time_from,
    e.time_to,
    e.internal_remarks,
    c.reference AS inquiry_ref,
    c.first_name,
    c.last_name,
    c.email,
    c.event_type,
    u.name AS staff_name,
    e.assigned_to
  FROM events e
  JOIN contact_messages c ON e.contact_message_id = c.id
  JOIN users u ON e.assigned_to = u.id
  WHERE e.id = ?
");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
  $stmt->close();
  header("Location: events.php?msg=notfound");
  exit;
}

$event = $result->fetch_assoc();
$stmt->close();

// Only allow staff to view their own assigned events
if ($role === 'staff' && $event['assigned_to'] !== $user_id) {
  header("Location: events.php?msg=unauthorized");
  exit;
}
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 700px;">
    <h1 style="text-align:center;">Event Details</h1>

    <article style="margin-top: 2rem;">
      <p><strong>Event Reference:</strong> <?= htmlspecialchars($event['event_ref']) ?></p>
      <p><strong>Inquiry Reference:</strong> <?= htmlspecialchars($event['inquiry_ref']) ?></p>
      <p><strong>Client:</strong> <?= htmlspecialchars($event['first_name'] . ' ' . $event['last_name']) ?> (<?= htmlspecialchars($event['email']) ?>)</p>
      <p><strong>Event Type:</strong> <?= htmlspecialchars(ucwords($event['event_type'])) ?></p>
      <p><strong>Assigned Staff:</strong> <?= htmlspecialchars($event['staff_name']) ?></p>
      <p><strong>Event Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
      <p><strong>Time:</strong> 
        <?= date("g:i A", strtotime($event['time_from'])) ?> – <?= date("g:i A", strtotime($event['time_to'])) ?>
      </p>
      <p><strong>Internal Remarks:</strong></p>
      <p style="border: 1px solid #ccc; padding: 12px; background-color: #fdf6f0; border-radius: 5px;">
        <?= nl2br(htmlspecialchars($event['internal_remarks'] ?: '—')) ?>
      </p>
    </article>

    <section class="action-buttons" style="margin-top: 2rem;">
      <a href="events.php" class="submit-btn cancel">← Back to Events</a>
    </section>
  </section>
</main>

<?php include 'footer.php'; ?>
