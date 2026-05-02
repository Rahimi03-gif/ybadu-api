<?php
header("Content-Type: application/json");
include 'db_connection.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $response['success'] = true;
               $response['user'] = [
    'id' => $user['id'],
    'email' => $user['email'],
    'nationality' => $user['nationality'],
    'role' => $user['role'],
    'username' => $user['username'], // ✅ tambahan username
    'image_url' => $user['image_url'] ?? '', // optional, jika ada
];

            } else {
                $response['success'] = false;
                $response['message'] = "Incorrect password.";
            }
        } else {
            $response['success'] = false;
            $response['message'] = "User not found.";
        }
    } else {
        $response['success'] = false;
        $response['message'] = "DB error.";
    }

    $stmt->close();
    $conn->close();
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request.";
}

echo json_encode($response);
?>
