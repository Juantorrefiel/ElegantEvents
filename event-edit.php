<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
  header("Location: events.php?msg=invalid");
  exit;
}

$error = '';
$event = null;

// Fetch event data
$stmt = $conn->prepare("
  SELECT id, contact_message_id, assigned_to, event_date, time_from, time_to, internal_remarks
  FROM events
  WHERE id = ?
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

// Fetch dropdown options
$inq_stmt = $conn->query("
  SELECT id, reference, first_name, last_name, event_date
  FROM contact_messages
  ORDER BY submitted_at DESC
");

$user_stmt = $conn->query("
  SELECT id, name FROM users WHERE user_type = 'staff' AND active = 1 ORDER BY name
");

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $contact_message_id = $_POST['contact_message_id'] ?? '';
  $assigned_to = $_POST['assigned_to'] ?? '';
  $event_date = $_POST['event_date'] ?? '';
  $time_from = $_POST['time_from'] ?? '';
  $time_to = $_POST['time_to'] ?? '';
  $internal_remarks = trim($_POST['internal_remarks'] ?? '');

  // Keep values in case of error
  $event['contact_message_id'] = $contact_message_id;
  $event['assigned_to'] = $assigned_to;
  $event['event_date'] = $event_date;
  $event['time_from'] = $time_from;
  $event['time_to'] = $time_to;
  $event['internal_remarks'] = $internal_remarks;

  if ($contact_message_id && $assigned_to && $event_date && $time_from && $time_to) {
    $today = date('Y-m-d');
    if ($event_date < $today) {
      $error = "Event date cannot be in the past.";
    } elseif ($time_from >= $time_to) {
      $error = "Time From must be earlier than Time To.";
    } else {
      $update = $conn->prepare("
        UPDATE events 
        SET contact_message_id = ?, assigned_to = ?, event_date = ?, time_from = ?, time_to = ?, internal_remarks = ?
        WHERE id = ?
      ");
      $update->bind_param("sssssss", $contact_message_id, $assigned_to, $event_date, $time_from, $time_to, $internal_remarks, $id);

      if ($update->execute()) {
        header("Location: events.php?msg=updated");
        exit;
      } else {
        $error = "Failed to update event. Please try again.";
      }
      $update->close();
    }
  } else {
    $error = "All fields are required.";
  }
}
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 600px;">
    <h1 style="text-align:center;">Edit Event</h1>

    <?php if ($error): ?>
      <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="form-styled" novalidate>
      <label for="contact_message_id">Select Inquiry</label>
      <select name="contact_message_id" id="contact_message_id" required>
        <option value="" disabled>Choose inquiry...</option>
        <?php while ($inq = $inq_stmt->fetch_assoc()): ?>
          <option value="<?= $inq['id'] ?>" <?= $event['contact_message_id'] == $inq['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($inq['reference']) ?> - <?= htmlspecialchars($inq['first_name'] . ' ' . $inq['last_name']) ?> - <?= htmlspecialchars($inq['event_date']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="assigned_to">Assign to Staff</label>
      <select name="assigned_to" id="assigned_to" required>
        <option value="" disabled>Select staff...</option>
        <?php while ($user = $user_stmt->fetch_assoc()): ?>
          <option value="<?= $user['id'] ?>" <?= $event['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($user['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="event_date">Event Date</label>
      <input type="date" name="event_date" id="event_date" required value="<?= htmlspecialchars($event['event_date']) ?>">

      <label for="time_from">Time From</label>
      <input type="time" name="time_from" id="time_from" required value="<?= htmlspecialchars($event['time_from']) ?>">

      <label for="time_to">Time To</label>
      <input type="time" name="time_to" id="time_to" required value="<?= htmlspecialchars($event['time_to']) ?>">

      <label for="internal_remarks">Internal Remarks</label>
      <textarea name="internal_remarks" id="internal_remarks" rows="4"><?= htmlspecialchars($event['internal_remarks']) ?></textarea>

      <section class="action-buttons" style="margin-top: 1.5rem;">
        <button type="submit" class="submit-btn">Update</button>
        <a href="events.php" class="submit-btn cancel">Cancel</a>
      </section>
    </form>
  </section>
</main>

<?php
$conn->close();
include 'footer.php';
?>
