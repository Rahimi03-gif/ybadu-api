<?php
header("Content-Type: application/json");
include 'db_connection.php'; // Assuming this file correctly sets up $conn

if (!isset($_GET['user_id'])) {
    echo json_encode(["success" => false, "message" => "User ID is required"]);
    exit;
}

$userId = $_GET['user_id'];

// Prepare the statement to prevent SQL injection
// Assuming 'users' table has 'id', 'email', 'username', 'phone', 'role', 'area' columns
$stmt = $conn->prepare("SELECT id, email, username, phone, role, area, image_url FROM users WHERE id = ?");
$stmt->bind_param("i", $userId); // 'i' for integer type
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $yb = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "message" => "Profile fetched successfully.",
        "data" => [ // Changed key from 'yb' to 'data'
            "id" => $yb['id'],
            "email" => $yb['email'],
            "name" => $yb['username'], // Mapped 'username' to 'name'
            "phone" => $yb['phone'] ?? '-', // Include phone, default to '-' if null
            "role" => $yb['role'] ?? '-', // Include role, default to '-' if null
            "area" => $yb['area'] ?? '-', // Include area, default to '-' if null
            "image_url" => $yb['image_url'] ?? null // Include image_url
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "YB profile not found for the given ID."]);
}

$stmt->close();
$conn->close();
?>
