<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "ybadu_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

if (!isset($_GET['email'])) {
    echo json_encode(["success" => false, "message" => "Email not provided"]);
    exit;
}

$email = $_GET['email'];

$result = $conn->query("SELECT username AS name, phone, email FROM users WHERE email = '$email'");

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}
?>
