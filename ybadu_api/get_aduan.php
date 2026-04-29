<?php
header('Content-Type: application/json');
include 'db_connection.php';

$user_email = $_GET['user_email'] ?? '';

if (empty($user_email)) {
    echo json_encode(['success' => false, 'message' => 'E-mel diperlukan']);
    exit;
}

$stmt = $conn->prepare("SELECT category, description, address, created_at FROM aduan WHERE user_email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$complaints = [];
while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}

echo json_encode(['success' => true, 'data' => $complaints]);
?>
