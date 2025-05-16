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

$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("s", $id);

if ($stmt->execute()) {
  header("Location: events.php?msg=deleted");
} else {
  header("Location: events.php?msg=error");
}

$stmt->close();
$conn->close();
exit;
