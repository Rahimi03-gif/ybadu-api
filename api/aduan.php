<?php
header('Content-Type: application/json');
include 'db_connection.php'; // Fail sambungan pangkalan data anda

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get POST data
$user_email  = trim($_POST['user_email'] ?? '');
$category    = trim($_POST['category'] ?? '');
$area        = trim($_POST['area'] ?? '');
$description = trim($_POST['description'] ?? '');
$address     = trim($_POST['address'] ?? '');
$image_path  = null; // Inisialisasi laluan gambar sebagai null

// Debug

// Validate basic fields
if (empty($user_email) || empty($category) || empty($area) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required information (except address and image).']);
    exit;
}

// Check user exist
$stmt_user = $conn->prepare("SELECT email FROM users WHERE email = ?");
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
file_put_contents("debug_log.txt", "User check rows: " . $result_user->num_rows . "\n", FILE_APPEND);

if ($result_user->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User account not found.']);
    exit;
}
$stmt_user->close(); // Tutup statement pengguna


// --- Bahagian Baru: Mengendalikan Muat Naik Gambar ---
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/'; // Direktori untuk menyimpan gambar
    
    // Pastikan direktori uploads wujud
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) { // Cipta direktori dengan kebenaran penuh
            file_put_contents("debug_log.txt", "Failed to create uploads directory.\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Gagal mencipta direktori muat naik.']);
            exit;
        }
    }

    $file_tmp_name = $_FILES['image']['tmp_name'];
    $file_name_original = basename($_FILES['image']['name']);
    $file_extension = pathinfo($file_name_original, PATHINFO_EXTENSION);
    $new_file_name = uniqid('img_', true) . '.' . $file_extension; // Jana nama unik
    $target_file = $upload_dir . $new_file_name;

    file_put_contents("debug_log.txt", "Attempting to move file from $file_tmp_name to $target_file\n", FILE_APPEND);

    if (move_uploaded_file($file_tmp_name, $target_file)) {
        $image_path = $target_file; // Simpan laluan relatif untuk pangkalan data
        file_put_contents("debug_log.txt", "Image uploaded successfully: $image_path\n", FILE_APPEND);
    } else {
        file_put_contents("debug_log.txt", "Failed to move uploaded file. Error: " . $_FILES['image']['error'] . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Failed to upload image. Please try again.']);
        exit;
    }
}
// --- Tamat Bahagian Baru ---


// Step 1: Insert into aduan (dengan image_path)
// Pastikan anda telah menambah lajur `image_path` dalam jadual `aduan` anda.
$stmt_aduan = $conn->prepare("
    INSERT INTO aduan (user_email, category, area, description, address, image_path)
    VALUES (?, ?, ?, ?, ?, ?)
");
// "ssssss" merujuk kepada 6 parameter string
$stmt_aduan->bind_param("ssssss", $user_email, $category, $area, $description, $address, $image_path);

if ($stmt_aduan->execute()) {
    $aduan_id = $conn->insert_id;
    file_put_contents("debug_log.txt", "aduan_id dapat: $aduan_id\n", FILE_APPEND);

    // Step 2: Insert into complaint_status
    $status_name = 'Pending';
    $updated_by = 'system';

    $stmt_status = $conn->prepare("
        INSERT INTO complaint_status (complaint_id, status_name, updated_by)
        VALUES (?, ?, ?)
    ");
    if ($stmt_status === false) {
        file_put_contents("debug_log.txt", "Prepare statement for status failed: " . $conn->error . "\n", FILE_APPEND);
        // Kita masih boleh anggap aduan utama berjaya dihantar, cuma status ada masalah.
        echo json_encode(['success' => true, 'message' => 'Complaint was successfully submitted, but failed to update the initial status.
']);
        exit;
    }

    $stmt_status->bind_param("iss", $aduan_id, $status_name, $updated_by);

    if ($stmt_status->execute()) {
        file_put_contents("debug_log.txt", "Status INSERT OK for Aduan ID $aduan_id\n", FILE_APPEND);
        echo json_encode(['success' => true, 'message' => 'Complaint and status successfully submitted!']);
    } else {
        file_put_contents("debug_log.txt", "Status insert FAILED: " . $stmt_status->error . "\n", FILE_APPEND);
        echo json_encode(['success' => true, 'message' => 'Complaint submitted successfully, but failed to update initial status. ' . $stmt_status->error]);
    }
    $stmt_status->close();

} else {
    file_put_contents("debug_log.txt", "Aduan insert FAILED: " . $stmt_aduan->error . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Error while submitting complaint. ' . $stmt_aduan->error]);
}

$stmt_aduan->close(); // Tutup statement aduan
$conn->close(); // Tutup sambungan pangkalan data
?>
