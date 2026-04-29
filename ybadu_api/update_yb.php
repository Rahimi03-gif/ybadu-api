<?php
include 'db_connection.php';

// Pastikan semua parameter ada
if (
    isset($_POST['id']) && isset($_POST['email']) && isset($_POST['nationality']) &&
    isset($_POST['role']) && isset($_POST['area']) && isset($_POST['image_url'])
) {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $nationality = $_POST['nationality'];
    $role = $_POST['role'];
    $area = $_POST['area'];
    $image_url = $_POST['image_url'];

    // Guna prepared statement untuk lebih selamat
    $stmt = $conn->prepare("UPDATE users SET email = ?, nationality = ?, role = ?, area = ?, image_url = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $email, $nationality, $role, $area, $image_url, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $stmt->error
        ]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
}
?>
