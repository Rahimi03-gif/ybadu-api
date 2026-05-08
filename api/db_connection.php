<?php
error_reporting(0);
ini_set('display_errors', 0);

$servername = "bxmkd2ihw6des1k2f5sj-mysql.services.clever-cloud.com";
$username = "uy8alfs1bngroj2y";
$password = "diFFS6NYxIHOS7jChxoQ";
$dbname = "bxmkd2ihw6des1k2f5sj";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}
?>
