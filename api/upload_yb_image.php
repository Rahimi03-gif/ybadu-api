<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Folder simpanan
$target_dir = "uploads/";

// Semak jika file ada
if (!isset($_FILES['image'])) {
    echo json_encode(["success" => false, "message" => "No file uploaded."]);
    exit;
}

$image = $_FILES["image"];
$filename = basename($image["name"]);
$imageFileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Benarkan hanya jenis gambar tertentu
$allowedTypes = ["jpg", "jpeg", "png", "webp"];
if (!in_array($imageFileType, $allowedTypes)) {
    echo json_encode(["success" => false, "message" => "Only JPG, JPEG, PNG & WEBP files are allowed."]);
    exit;
}

// Hasilkan nama unik
$newFileName = time() . "_" . preg_replace("/[^A-Za-z0-9_\-\.]/", "_", $filename);
$target_file = $target_dir . $newFileName;

// Upload
if (move_uploaded_file($image["tmp_name"], $target_file)) {
    echo json_encode([
        "success" => true,
        "image_url" => "http://10.0.2.2/ybadu_api/" . $target_file,
        "file_name" => $newFileName
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to upload image."]);
}
?>
