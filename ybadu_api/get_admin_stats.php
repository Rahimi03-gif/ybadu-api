<?php
header('Content-Type: application/json');
include 'db_connection.php'; // fail sambungan ke database

$response = array();

// Kira total users
$userQuery = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users");
$userData = mysqli_fetch_assoc($userQuery);
$totalUsers = $userData['total_users'] ?? 0;

// Kira total YB dari users yang role dia 'yb'
$ybQuery = mysqli_query($conn, "SELECT COUNT(*) AS total_yb FROM users WHERE role = 'yb'");
$ybData = mysqli_fetch_assoc($ybQuery);
$totalYB = $ybData['total_yb'] ?? 0;

// Kira total aduan dari table 'aduan'
$complaintQuery = mysqli_query($conn, "SELECT COUNT(*) AS total_complaints FROM aduan");
$complaintData = mysqli_fetch_assoc($complaintQuery);
$totalComplaints = $complaintData['total_complaints'] ?? 0;

$response['success'] = true;
$response['total_users'] = (int)$totalUsers;
$response['total_yb'] = (int)$totalYB;
$response['total_complaints'] = (int)$totalComplaints;

echo json_encode($response);
?>
