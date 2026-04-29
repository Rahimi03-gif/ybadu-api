<?php
include('db_connection.php');
header('Content-Type: application/json');

$sql = "SELECT id, username, email, nationality, role, area, image_url FROM users WHERE role = 'yb'";
$result = mysqli_query($conn, $sql);

$ybList = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ybList[] = $row;
    }
}

echo json_encode($ybList);
?>
