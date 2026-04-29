<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "ybadu_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

$user_email = isset($_GET['user_email']) ? $conn->real_escape_string($_GET['user_email']) : '';

if ($user_email !== '') {
    $query = "
        SELECT a.*, a.image_path AS image_url, u.role,  /* <-- PENTING: TAMBAH BARIS INI */
            (
                SELECT cs.status_name
                FROM complaint_status cs
                WHERE cs.complaint_id = a.id
                ORDER BY cs.updated_at DESC
                LIMIT 1
            ) AS current_status
        FROM aduan a
        JOIN users u ON a.user_email = u.email
        WHERE a.user_email = '$user_email'
        ORDER BY a.created_at DESC
    ";
} else {
    $query = "
        SELECT a.*, a.image_path AS image_url, u.role, /* <-- PENTING: TAMBAH BARIS INI */
            (
                SELECT cs.status_name
                FROM complaint_status cs
                WHERE cs.complaint_id = a.id
                ORDER BY cs.updated_at DESC
                LIMIT 1
            ) AS current_status
        FROM aduan a
        JOIN users u ON a.user_email = u.email
        ORDER BY a.created_at DESC
    ";
}

$result = $conn->query($query);

$complaints = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
    echo json_encode(["success" => true, "data" => $complaints]);
} else {
    echo json_encode(["success" => true, "data" => []]);
}

$conn->close();
?>