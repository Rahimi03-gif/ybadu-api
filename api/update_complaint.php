<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "ybadu_db");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $reject_reason = isset($_POST['reject_reason']) ? $conn->real_escape_string($_POST['reject_reason']) : null;

    if ($id > 0 && $status !== '' && $userId > 0) {
        // Semak kawasan YB
        $stmtUser = $conn->prepare("SELECT area FROM users WHERE id = ?");
        $stmtUser->bind_param("i", $userId);
        $stmtUser->execute();
        $areaResult = $stmtUser->get_result();
        $ybArea = null;
        if ($areaResult && $areaResult->num_rows > 0) {
            $ybArea = $areaResult->fetch_assoc()['area'];
        }
        $stmtUser->close();

        if ($ybArea) {
            // Pastikan aduan dalam kawasan YB
            $stmtCheck = $conn->prepare("SELECT id FROM aduan WHERE id = ? AND area = ?");
            $stmtCheck->bind_param("is", $id, $ybArea);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0) {
                // Insert ke table complaint_status
                $stmtInsert = $conn->prepare("INSERT INTO complaint_status (complaint_id, status_name, updated_by) VALUES (?, ?, ?)");
                $updated_by = "YB_$userId";
                $stmtInsert->bind_param("iss", $id, $status, $updated_by);

                if ($stmtInsert->execute()) {
                    // Jika status = Rejected, simpan reason dalam table aduan
                    if (strtolower($status) === 'rejected' && !empty($reject_reason)) {
                        $stmtUpdateReason = $conn->prepare("UPDATE aduan SET reject_reason = ? WHERE id = ?");
                        $stmtUpdateReason->bind_param("si", $reject_reason, $id);
                        $stmtUpdateReason->execute();
                        $stmtUpdateReason->close();
                    }

                    echo json_encode(["success" => true, "message" => "Status updated successfully"]);
                } else {
                    echo json_encode(["success" => false, "message" => "INSERT failed: " . $stmtInsert->error]);
                }

                $stmtInsert->close();
            } else {
                echo json_encode(["success" => false, "message" => "Aduan tak dijumpai dalam kawasan anda."]);
            }
            $stmtCheck->close();
        } else {
            echo json_encode(["success" => false, "message" => "Kawasan YB tak dijumpai."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Parameter tak lengkap."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
