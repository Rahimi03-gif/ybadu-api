<?php
header("Content-Type: application/json");

// Connect to DB
$conn = new mysqli("localhost", "root", "", "ybadu_db");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;

if ($userId > 0) {
    // Get YB's area
    $stmtArea = $conn->prepare("SELECT area FROM users WHERE id = ?");
    $stmtArea->bind_param("i", $userId);
    $stmtArea->execute();
    $areaResult = $stmtArea->get_result();
    $stmtArea->close();

    $ybArea = null;
    if ($areaResult && $areaResult->num_rows > 0) {
        $ybArea = $areaResult->fetch_assoc()['area'];
    }

    if ($ybArea) {
        // ✅ FIXED QUERY: now includes latest complaint status
        $query = "
            SELECT 
                aduan.*, 
                users.role,
                COALESCE((
                    SELECT cs.status_name 
                    FROM complaint_status cs 
                    WHERE cs.complaint_id = aduan.id 
                    ORDER BY cs.updated_at DESC 
                    LIMIT 1
                ), 'Pending') AS current_status
            FROM aduan
            JOIN users ON aduan.user_email = users.email
            WHERE aduan.area = ?
            ORDER BY aduan.created_at DESC
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $ybArea);
        $stmt->execute();
        $result = $stmt->get_result();

        $complaints = [];
        while ($row = $result->fetch_assoc()) {
            $complaints[] = $row;
        }

        echo json_encode($complaints);
        $stmt->close();
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}

$conn->close();
?>
