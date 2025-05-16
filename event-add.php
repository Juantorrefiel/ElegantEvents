<?php
session_start();
include 'config.php';
include 'generate-uuid.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$error = '';
$contact_message_id = $_POST['contact_message_id'] ?? '';
$assigned_to = $_POST['assigned_to'] ?? '';
$event_date = $_POST['event_date'] ?? '';
$time_from = $_POST['time_from'] ?? '';
$time_to = $_POST['time_to'] ?? '';
$internal_remarks = trim($_POST['internal_remarks'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($contact_message_id && $assigned_to && $event_date && $time_from && $time_to) {
    $today = date('Y-m-d');
    if ($event_date < $today) {
      $error = "Event date cannot be in the past.";
    } elseif ($time_from >= $time_to) {
      $error = "Time From must be earlier than Time To.";
    } else {
      $id = generate_uuid();
      $ref = 'EVT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

      $stmt = $conn->prepare("INSERT INTO events (id, reference, contact_message_id, assigned_to, event_date, time_from, time_to, internal_remarks)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("ssssssss", $id, $ref, $contact_message_id, $assigned_to, $event_date, $time_from, $time_to, $internal_remarks);

      if ($stmt->execute()) {
        header("Location: events.php?msg=created");
        exit;
      } else {
        $error = "Failed to add event. Please try again.";
      }

      $stmt->close();
    }
  } else {
    $error = "All fields are required.";
  }
}

$inq_stmt = $conn->query("
  SELECT id, reference, first_name, last_name, event_date
  FROM contact_messages
  ORDER BY submitted_at DESC
");

$user_stmt = $conn->query("
  SELECT id, name FROM users WHERE user_type = 'staff' AND active = 1 ORDER BY name
");
?>

<?php include 'header.php'; ?>

<main class="user-list-main">
  <section class="user-list-box" style="max-width: 600px;">
    <h1 style="text-align:center;">Add Event</h1>

    <?php if ($error): ?>
      <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="form-styled" novalidate>
      <label for="contact_message_id">Select Inquiry</label>
      <select name="contact_message_id" id="contact_message_id" required>
        <option value="" disabled <?= $contact_message_id ? '' : 'selected' ?>>Choose inquiry...</option>
        <?php while ($inq = $inq_stmt->fetch_assoc()): ?>
          <option value="<?= $inq['id'] ?>" <?= $contact_message_id == $inq['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($inq['reference']) ?> - <?= htmlspecialchars($inq['first_name'] . ' ' . $inq['last_name']) ?> - <?= htmlspecialchars($inq['event_date']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="assigned_to">Assign to Staff</label>
      <select name="assigned_to" id="assigned_to" required>
        <option value="" disabled <?= $assigned_to ? '' : 'selected' ?>>Select staff...</option>
        <?php while ($user = $user_stmt->fetch_assoc()): ?>
          <option value="<?= $user['id'] ?>" <?= $assigned_to == $user['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($user['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="event_date">Event Date</label>
      <input type="date" name="event_date" id="event_date" required value="<?= htmlspecialchars($event_date) ?>">

      <label for="time_from">Time From</label>
      <input type="time" name="time_from" id="time_from" required value="<?= htmlspecialchars($time_from) ?>">

      <label for="time_to">Time To</label>
      <input type="time" name="time_to" id="time_to" required value="<?= htmlspecialchars($time_to) ?>">

      <label for="internal_remarks">Internal Remarks</label>
      <textarea name="internal_remarks" id="internal_remarks" rows="4" placeholder="Notes for staff or internal comments..."><?= htmlspecialchars($internal_remarks) ?></textarea>

      <section class="action-buttons" style="margin-top: 1.5rem;">
        <button type="submit" class="submit-btn">Save</button>
        <a href="events.php" class="submit-btn cancel">Cancel</a>
      </section>
    </form>
  </section>
</main>

<?php
$conn->close();
include 'footer.php';
?>
