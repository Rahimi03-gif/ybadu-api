<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan trim input
    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $nationality  = trim($_POST['nationality'] ?? '');
    $area         = trim($_POST['area'] ?? '');
    $role         = 'yb'; // ✅ hardcoded for security

    // Validasi: semua wajib diisi
    if (empty($username) || empty($email) || empty($password) || empty($nationality) || empty($area)) {
        echo json_encode([
            "success" => false,
            "message" => "Sila isi semua maklumat termasuk kawasan (area)."
        ]);
        exit;
    }

    // Semak email wujud tak
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Email telah didaftarkan."
        ]);
        $checkEmail->close();
        exit;
    }
    $checkEmail->close();

    // Semak username wujud tak
    $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    $checkUsername->store_result();

    if ($checkUsername->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Username telah didaftarkan."
        ]);
        $checkUsername->close();
        exit;
    }
    $checkUsername->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    if (!$hashedPassword) {
        echo json_encode([
            "success" => false,
            "message" => "Gagal hash password."
        ]);
        exit;
    }

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, nationality, role, area) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Gagal sediakan penyataan untuk insert."
        ]);
        exit;
    }

    $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $nationality, $role, $area);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Pendaftaran YB berjaya!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Pendaftaran gagal: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "Hanya permintaan POST dibenarkan."
    ]);
}
?>
