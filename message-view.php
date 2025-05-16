<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$id = $_GET['id'] ?? '';
$from = $_GET['from'] ?? ''; // check where the view originated

if (!$id) {
  header("Location: messages.php?msg=invalid");
  exit;
}

$stmt = $conn->prepare("
  SELECT reference, first_name, last_name, email, event_type, event_date, guest_count, message, submitted_at
  FROM contact_messages
  WHERE id = ?
");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $stmt->close();
  header("Location: messages.php?msg=invalid");
  exit;
}

$inquiry = $result->fetch_assoc();
$stmt->close();
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 700px;">
    <h1 style="text-align:center;">Inquiry Details</h1>

    <article style="margin-top: 2rem;">
      <p><strong>Reference #:</strong> <?= htmlspecialchars($inquiry['reference']) ?></p>
      <p><strong>Name:</strong> <?= htmlspecialchars($inquiry['first_name'] . ' ' . $inquiry['last_name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($inquiry['email']) ?></p>
      <p><strong>Event Type:</strong> <?= htmlspecialchars($inquiry['event_type']) ?></p>
      <p><strong>Event Date:</strong> <?= htmlspecialchars($inquiry['event_date']) ?></p>
      <p><strong>Estimated Guests:</strong> <?= (int)$inquiry['guest_count'] ?></p>
      <p><strong>Submitted At:</strong> <?= date('F j, Y H:i A', strtotime($inquiry['submitted_at'])) ?></p>
      <p><strong>Message:</strong></p>
      <p style="border: 1px solid #ccc; padding: 12px; background-color: #fdf6f0; border-radius: 5px;">
        <?= nl2br(htmlspecialchars($inquiry['message'])) ?>
      </p>
    </article>

    <section class="action-buttons" style="margin-top: 2rem;">
      <a href="<?= $from === 'events' ? 'events.php' : 'messages.php' ?>" class="submit-btn cancel">
        ← Back to <?= $from === 'events' ? 'Events' : 'Inquiries' ?>
      </a>
    </section>
  </section>
</main>

<?php include 'footer.php'; ?>
