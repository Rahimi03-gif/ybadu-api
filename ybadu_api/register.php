<?php
header('Content-Type: application/json');
include 'db_connection.php'; // sambungan ke DB

// Dapatkan semua field daripada POST
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';
$ic = $_POST['ic'] ?? ''; // ✅ ambil IC dari Flutter
$nationality = $_POST['nationality'] ?? ''; // optional

// Validation: Check jika ada yang kosong
if (empty($username) || empty($email) || empty($password) || empty($phone) || empty($ic)) {
    echo json_encode(['success' => false, 'message' => 'Sila isi semua maklumat']);
    exit;
}

// Check jika email telah digunakan
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'E-mel telah digunakan']);
    exit;
}

// Check jika username telah digunakan
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username telah digunakan']);
    exit;
}

// Check jika IC telah digunakan
$stmt = $conn->prepare("SELECT * FROM users WHERE ic = ?");
$stmt->bind_param("s", $ic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'IC telah digunakan']);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Masukkan user baru ke dalam database
$stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, ic, nationality) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $username, $email, $hashed_password, $phone, $ic, $nationality);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Berjaya daftar']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ralat semasa daftar']);
}
?>
