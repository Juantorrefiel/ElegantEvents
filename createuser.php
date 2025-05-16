<?php
include 'config.php';             // Your DB connection file
include 'generate-uuid.php';      // Your UUID generator function

// User details
$id = generate_uuid();
$name = "Admin";
$email = "admin@admin.com";
$password = password_hash("admin", PASSWORD_DEFAULT);  // Secure hashed password
$user_type = "admin";             // Must be 'admin' or 'instructor'
$active = true;

// Prepare and execute insert
$stmt = $conn->prepare("INSERT INTO users (id, name, email, password, user_type, active) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $id, $name, $email, $password, $user_type, $active);

if ($stmt->execute()) {
    echo "✅ Admin user created with UUID: $id";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
