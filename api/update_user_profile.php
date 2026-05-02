<?php
header("Content-Type: application/json");

// Connect to DB
$conn = new mysqli("localhost", "root", "", "ybadu_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

// Get POST data
$currentEmail = $_POST['current_email'];
$newEmail = $_POST['new_email'];
$name = $_POST['name'];
$phone = $_POST['phone'];

// Check if new email already exists (avoid duplicate)
$check = $conn->query("SELECT * FROM users WHERE email = '$newEmail' AND email != '$currentEmail'");
if ($check && $check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already in use by another account"]);
    exit;
}

// Update user
$query = "UPDATE users SET username = '$name', phone = '$phone', email = '$newEmail' WHERE email = '$currentEmail'";

if ($conn->query($query) === TRUE) {
    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}
?>
