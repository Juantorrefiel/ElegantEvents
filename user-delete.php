<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$id = $_GET['id'] ?? '';

if (!$id) {
  header("Location: users.php?msg=invalid");
  exit;
}

// Prevent deleting yourself (optional safety)
if ($_SESSION['user']['id'] === $id) {
  header("Location: users.php?msg=error");
  exit;
}

// Check if user exists
$check = $conn->prepare("SELECT id FROM users WHERE id = ?");
$check->bind_param("s", $id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
  $check->close();
  header("Location: users.php?msg=invalid");
  exit;
}
$check->close();

// Proceed with delete
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("s", $id);

if ($stmt->execute()) {
  header("Location: users.php?msg=deleted");
} else {
  header("Location: users.php?msg=error");
}

$stmt->close();
$conn->close();
exit;
