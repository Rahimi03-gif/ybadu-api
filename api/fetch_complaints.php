<?php
include 'db_connection.php';
header("Content-Type: application/json");

// Ambil semua aduan bersama status terkini
$sql = "
    SELECT a.*, 
        (
            SELECT cs.status_name 
            FROM complaint_status cs 
            WHERE cs.complaint_id = a.id 
            ORDER BY cs.updated_at DESC 
            LIMIT 1
        ) AS current_status
    FROM aduan a
    ORDER BY a.created_at DESC
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $complaints = [];
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
    echo json_encode(["success" => true, "complaints" => $complaints]);
} else {
    echo json_encode(["success" => false, "message" => "No complaints found"]);
}

$conn->close();
?>
