<?php
header('Content-Type: application/json');
include 'db_connection.php'; 

// 1. TUTUP paparan ralat supaya tidak keluar <br />
error_reporting(0);
ini_set('display_errors', 0);

// Get POST data
$user_email  = trim($_POST['user_email'] ?? '');
$category    = trim($_POST['category'] ?? '');
$area        = trim($_POST['area'] ?? '');
$description = trim($_POST['description'] ?? '');
$address     = trim($_POST['address'] ?? '');
$image_path  = null; 

// Validate basic fields
if (empty($user_email) || empty($category) || empty($area) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required information.']);
    exit;
}

// Check user exist
$stmt_user = $conn->prepare("SELECT email FROM users WHERE email = ?");
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User account not found.']);
    exit;
}
$stmt_user->close();

// --- BAHAGIAN GAMBAR (DITUTUP SEMENTARA UNTUK VERCEL) ---
// Vercel tidak membenarkan 'move_uploaded_file' ke folder tempatan.
// Jika kau perlukan gambar, kau kena guna Cloudinary/Firebase.
// Buat masa ni kita set null supaya database tak error.
$image_path = null; 

// Step 1: Insert into aduan
$stmt_aduan = $conn->prepare("
    INSERT INTO aduan (user_email, category, area, description, address, image_path)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt_aduan->bind_param("ssssss", $user_email, $category, $area, $description, $address, $image_path);

if ($stmt_aduan->execute()) {
    $aduan_id = $conn->insert_id;

    // Step 2: Insert into complaint_status
    $status_name = 'Pending';
    $updated_by = 'system';

    $stmt_status = $conn->prepare("
        INSERT INTO complaint_status (complaint_id, status_name, updated_by)
        VALUES (?, ?, ?)
    ");
    
    if ($stmt_status) {
        $stmt_status->bind_param("iss", $aduan_id, $status_name, $updated_by);
        if ($stmt_status->execute()) {
            echo json_encode(['success' => true, 'message' => 'Complaint successfully submitted!']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Complaint submitted, but status failed.']);
        }
        $stmt_status->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt_aduan->close();
$conn->close();
?>
