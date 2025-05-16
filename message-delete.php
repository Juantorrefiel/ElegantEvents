<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
  header("Location: messages.php?msg=invalid");
  exit;
}

// Validate if inquiry exists
$check = $conn->prepare("SELECT id FROM contact_messages WHERE id = ?");
$check->bind_param("s", $id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
  $check->close();
  header("Location: messages.php?msg=invalid");
  exit;
}
$check->close();

// Proceed with delete
$stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
$stmt->bind_param("s", $id);

if ($stmt->execute()) {
  header("Location: messages.php?msg=deleted");
} else {
  header("Location: messages.php?msg=error");
}

$stmt->close();
$conn->close();
exit;
